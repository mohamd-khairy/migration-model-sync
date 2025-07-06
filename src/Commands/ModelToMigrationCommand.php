<?php

namespace Khairy\MigrationModelSync\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;
use Khairy\MigrationModelSync\Services\ModelInspector;
use Khairy\MigrationModelSync\Services\MigrationBuilder;

class ModelToMigrationCommand extends Command
{
    protected $signature = 'model:generate-migration {model} {--force} {--path= : Custom path to save migration}';
    protected $description = 'Generate a migration file based on an existing Eloquent model';

    public function handle(): int
    {
        $modelName = $this->argument('model');
        $modelClass = "App\\Models\\{$modelName}";

        if (!class_exists($modelClass)) {
            $this->error("Model class {$modelClass} does not exist.");
            return 1;
        }

        $table = Str::snake(Str::pluralStudly(class_basename($modelName)));
        $path = $this->option('path') ?: database_path('migrations');

        $existing = collect(File::files($path))
            ->first(fn($file) => str_contains($file->getFilename(), "create_{$table}_table.php"));

        if ($existing && !$this->option('force')) {
            $this->warn("Migration already exists: " . $existing->getFilename());
            $this->warn("Use --force to overwrite.");
            return 1;
        }

        $filename = $existing
            ? $existing->getFilename()
            : now()->format('Y_m_d_His') . "_create_{$table}_table.php";

        $fullPath = "{$path}/{$filename}";



        $inspector = new ModelInspector(new $modelClass());
        $columns = $inspector->extractColumns();

        $builder = new MigrationBuilder();
        $migration = $builder->build($table, $columns);

        File::put($fullPath, $migration);
        $this->info("✅ Migration created: {$fullPath}");

        return 0;
    }
}
