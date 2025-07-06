<?php

namespace Khairy\MigrationModelSync\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Khairy\MigrationModelSync\Services\ModelInspector;
use Khairy\MigrationModelSync\Services\MigrationBuilder;

class ModelSyncAllToMigrationCommand extends Command
{
    protected $signature = 'model:generate-migrations
        {--force : Overwrite existing migrations}
        {--path= : Custom path to save migrations}
        {--only= : Comma-separated list of models to include (e.g. User,Post)}
        {--except= : Comma-separated list of models to exclude (e.g. Log,Token)}
        {--sort=auto : Sort order for generation (default: "auto" = dependency-aware, or "none")}';

    protected $description = 'Generate migration files for all models in app/Models';

    public function handle(): int
    {
        $modelPath = app_path('Models');
        $migrationPath = $this->option('path') ?: database_path('migrations');

        $only = $this->option('only')
            ? collect(explode(',', $this->option('only')))->map(fn($m) => trim($m))->filter()->all()
            : null;

        $except = $this->option('except')
            ? collect(explode(',', $this->option('except')))->map(fn($m) => trim($m))->filter()->all()
            : [];

        $sortOption = $this->option('sort') ?? 'auto';
        $sortAuto = $sortOption === 'auto';

        if (!File::exists($modelPath)) {
            $this->error("Directory not found: {$modelPath}");
            return 1;
        }

        $modelFiles = collect(File::allFiles($modelPath))
            ->filter(fn($f) => Str::endsWith($f->getFilename(), '.php'))
            ->map(function ($file) {
                return [
                    'class' => 'App\\Models\\' . $file->getBasename('.php'),
                    'file' => $file
                ];
            })
            ->filter(function ($entry) use ($only, $except) {
                $name = class_basename($entry['class']);
                if ($only && !in_array($name, $only))
                    return false;
                if (in_array($name, $except))
                    return false;
                return true;
            });

        $entries = $modelFiles->map(function ($entry) {
            $class = $entry['class'];
            $model = new $class;
            $inspector = new ModelInspector($model);
            $entry['table'] = $model->getTable();
            $entry['depends_on'] = $inspector->getBelongsToTables();
            $entry['instance'] = $model;
            return $entry;
        })->values();

        $sorted = $sortAuto ? $this->topologicalSort($entries) : $entries->all();

        static $timestampOffset = 0;

        foreach ($sorted as $entry) {
            $class = $entry['class'];
            $model = $entry['instance'];
            $table = $entry['table'];

            $existing = collect(File::files($migrationPath))
                ->first(fn($f) => str_contains($f->getFilename(), "create_{$table}_table.php"));

            if ($existing && !$this->option('force')) {
                $this->line("✅ Skipped existing: {$table} (use --force to overwrite)");
                continue;
            }

            $filename = $existing
                ? $existing->getFilename()
                : now()->addSeconds($timestampOffset++)->format('Y_m_d_His') . "_create_{$table}_table.php";

            $fullPath = "{$migrationPath}/{$filename}";

            $inspector = new ModelInspector($model);
            $columns = $inspector->extractColumns();

            $builder = new MigrationBuilder();
            $stub = $builder->build($table, $columns);

            File::put($fullPath, $stub);
            $this->info("📝 Migration created: {$filename}");
        }

        return 0;
    }

    protected function topologicalSort($entries)
    {
        $sorted = [];
        $visited = [];

        $map = $entries->keyBy('table');

        $visit = function ($entry) use (&$visit, &$visited, &$sorted, $map) {
            $table = $entry['table'];
            if (isset($visited[$table])) return;
            $visited[$table] = true;

            foreach ($entry['depends_on'] as $dep) {
                if (isset($map[$dep])) {
                    $visit($map[$dep]);
                }
            }

            $sorted[] = $entry;
        };

        foreach ($entries as $entry) {
            $visit($entry);
        }

        return $sorted;
    }
}
