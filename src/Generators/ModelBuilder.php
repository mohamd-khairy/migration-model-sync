<?php

namespace Khairy\MigrationModelSync\Generators;

use Illuminate\Support\Str;

class ModelBuilder
{
    protected string $model;
    protected array $schema;
    protected array $relations;

    public function __construct(string $model, array $schema, array $relations)
    {
        $this->model = $model;
        $this->schema = $schema;
        $this->relations = $relations;
    }

    public function build()
    {
        $modelPath = config('modelsync.model_path') . '/' . $this->model . '.php';

        $topLevelUses = '';
        $classTraitUses = '';
        $classSignature = "class {$this->model} extends Model"; // fallback
        $foundClassLine = false;

        if (file_exists($modelPath)) {
            $lines = file($modelPath);
            $insideClass = false;

            foreach ($lines as $line) {
                $trimmed = trim($line);

                // Top-level use imports
                if (!$insideClass && Str::startsWith($trimmed, 'use ') && Str::endsWith($trimmed, ';')) {
                    $topLevelUses .= $line;
                }

                // Detect class line and preserve it
                if (Str::startsWith($trimmed, 'class ') && !$foundClassLine) {
                    $classSignature = rtrim($trimmed, '{ ');
                    $foundClassLine = true;
                    $insideClass = true;
                    continue;
                }

                // Trait usage inside class
                if ($insideClass && Str::startsWith($trimmed, 'use ') && Str::endsWith($trimmed, ';')) {
                    $classTraitUses .= "    {$trimmed}\n";
                }
            }
        }

        $fillable = array_keys(array_diff_key($this->schema['columns'], array_flip(config('modelsync.ignore_columns'))));
        $casts = $this->generateCasts($this->schema['columns']);

        $stub  = "<?php\n\n";
        $stub .= "namespace App\\Models;\n\n";
        if (!file_exists($modelPath)) {
            $stub .= "use Illuminate\\Database\\Eloquent\\Model;\n\n";
        }
        $stub .= $topLevelUses ? trim($topLevelUses) . "\n\n" : '';

        $stub .= "{$classSignature}\n";
        $stub .= "{\n";
        $stub .= $classTraitUses ? $classTraitUses . "\n" : '';

        $stub .= "    protected \$fillable = [\n";
        $stub .= "        " . implode(",\n        ", array_map(fn($f) => "'{$f}'", $fillable)) . "\n";
        $stub .= "    ];\n\n";

        if (count($casts) > 0) {
            $stub .= "    protected \$casts = [\n";
            $stub .= "        " . implode(",\n        ", array_map(fn($k, $v) => "'{$k}' => '{$v}'", array_keys($casts), $casts)) . "\n";
            $stub .= "    ];\n\n";
        }

        $stub .= $this->generateHiddens($this->schema['columns']);

        foreach ($this->relations as $relation) {
            $stub .= "    public function {$relation['method']}()\n";
            $stub .= "    {\n";
            $stub .= "        return \$this->{$relation['type']}({$relation['model']}::class, '{$relation['foreign_key']}', '{$relation['owner_key']}');\n";
            $stub .= "    }\n\n";
        }

        $stub .= "}";

        file_put_contents($modelPath, $stub);
    }

    protected function generateCasts(array $columns): array
    {
        $casts = [];
        foreach ($columns as $col => $type) {
            if (in_array($col, config('modelsync.ignore_columns'))) continue;
            if (in_array($type, ['timestamp', 'datetime'])) {
                $casts[$col] = 'datetime';
            } elseif ($type === 'json') {
                $casts[$col] = 'array';
            } elseif ($type === 'boolean') {
                $casts[$col] = 'boolean';
            }
            if (in_array($col, config('modelsync.hashed'))) {
                $casts[$col] = 'hashed';
            }
        }
        return $casts;
    }

    protected function generateHiddens(array $columns): string
    {
        $hidden = config('modelsync.hidden', []);

        // Filter only columns that exist in the migration
        $filtered = array_filter($hidden, fn($field) => array_key_exists($field, $columns));

        if (empty($filtered)) {
            return '';
        }

        return "    protected \$hidden = [\n" .
            "        " . implode(",\n        ", array_map(fn($f) => "'{$f}'", $filtered)) . "\n" .
            "    ];\n\n";
    }
}
