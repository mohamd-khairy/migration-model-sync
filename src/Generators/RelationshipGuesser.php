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
        foreach ($this->schema['foreign_keys'] as $column => $target) {
            $model = Str::studly(Str::singular($target));
            $relations[] = [
                'type' => 'belongsTo',
                'method' => lcfirst($model),
                'model' => "\\App\\Models\\{$model}"
            ];
        }
        return $relations;
    }
}