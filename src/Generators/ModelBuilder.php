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
        $fillable = array_keys(array_diff_key($this->schema['columns'], array_flip(config('modelsync.ignore_columns'))));
        $casts = $this->generateCasts($this->schema['columns']);

        $stub = "<?php\n\nnamespace App\\Models;\n\nuse Illuminate\\Database\\Eloquent\\Model;\n\nclass {$this->model} extends Model\n{\n    protected \$fillable = [" . implode(', ', array_map(fn($f) => "'{$f}'", $fillable)) . "];\n\n    protected \$casts = [\n" . implode(",\n", array_map(fn($k, $v) => "        '{$k}' => '{$v}'", array_keys($casts), $casts)) . "\n    ];\n\n";

        foreach ($this->relations as $relation) {
            $stub .= "    public function {$relation['method']}()\n    {\n        return \$this->{$relation['type']}({$relation['model']}::class);\n    }\n\n";
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
        }
        return $casts;
    }
}

