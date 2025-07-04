<?php

namespace Khairy\MigrationModelSync\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;
use Khairy\MigrationModelSync\Generators\MigrationParser;
use Khairy\MigrationModelSync\Generators\ModelBuilder;
use Khairy\MigrationModelSync\Generators\RelationshipGuesser;

class SyncModelCommand extends Command
{
    protected $signature = 'model:sync {name?}';
    protected $description = 'Generate or update a model from its migration';

    public function handle()
    {
        $name = $this->argument('name');

        if (!$name) {
            $this->error("Model name is required.");
            return 1;
        }

        $this->info("Syncing model: {$name}");

        $parser = new MigrationParser($name);
        $schema = $parser->parse();

        $guesser = new RelationshipGuesser($schema);
        $relations = $guesser->infer();

        $builder = new ModelBuilder($name, $schema, $relations);
        $builder->build();

        $this->info("Model synced successfully.");
        return 0;
    }
}
