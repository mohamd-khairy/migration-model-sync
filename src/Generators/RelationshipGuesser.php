<?php

namespace Khairy\MigrationModelSync\Generators;

use Illuminate\Support\Str;

class RelationshipGuesser
{
    protected array $schema;

    public function __construct(array $schema)
    {
        $this->schema = $schema;
    }

    public function infer(): array
    {
        $relations = [];

        $overrides = config('modelsync.relationship_overrides', []);

        foreach ($this->schema['foreign_keys'] as $column => $target) {
            if (isset($overrides[$column])) {
                // Parse the override string: 'belongsTo:App\\Models\\Admin@author'
                [$type, $modelAndMethod] = explode(':', $overrides[$column]);
                [$model, $method] = explode('@', $modelAndMethod);

                $relations[] = [
                    'type'        => $type,
                    'method'      => $method,
                    'model'       => "\\{$model}",
                    'foreign_key' => $column,
                    'owner_key'   => 'id',
                ];
                continue;
            }

            // Default relationship inference
            $relationName = Str::studly(str_replace('_id', '', $column));
            $modelName = Str::studly(Str::singular($target));

            $relations[] = [
                'type'        => 'belongsTo',
                'method'      => lcfirst($relationName),
                'model'       => "\\App\\Models\\{$modelName}",
                'foreign_key' => $column,
                'owner_key'   => 'id',
            ];
        }

        return $relations;
    }
}
