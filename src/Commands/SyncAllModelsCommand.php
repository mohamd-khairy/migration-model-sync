<?php

namespace Khairy\MigrationModelSync\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Symfony\Component\Finder\Finder;
use Khairy\MigrationModelSync\Generators\MigrationParser;
use Khairy\MigrationModelSync\Generators\ModelBuilder;
use Khairy\MigrationModelSync\Generators\RelationshipGuesser;

class SyncAllModelsCommand extends Command
{
    protected $signature = 'model:sync-all';
    protected $description = 'Sync all models from available migration files';

    public function handle()
    {
        $migrationPath = database_path('migrations');
        $finder = new Finder();
        $finder->files()->in($migrationPath)->name('/create_.*_table\.php/');

        $synced = 0;

        foreach ($finder as $file) {
            // Extract table name from file name
            preg_match('/create_(.*?)_table/', $file->getFilename(), $match);
            if (!isset($match[1])) {
                continue;
            }

            $table = $match[1];
            $modelName = Str::studly(Str::singular($table));

            $this->line("🔄 Syncing: $modelName");

            $parser = new MigrationParser($modelName);
            $schema = $parser->parse();

            if (empty($schema['columns'])) {
                $this->warn("⚠️  Skipped: $modelName (no columns found)");
                continue;
            }

            $relations = (new RelationshipGuesser($schema))->infer();
            (new ModelBuilder($modelName, $schema, $relations))->build();

            $this->info("✅ Synced: $modelName");
            $synced++;
        }

        $this->info("✨ Done! Total models synced: $synced");
    }
}
