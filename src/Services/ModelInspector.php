<?php

namespace Khairy\MigrationModelSync\Services;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use ReflectionClass;
use ReflectionMethod;

class ModelInspector
{
    public function __construct(protected Model $model)
    {
    }

    public function extractColumns(): array
    {
        $columns = [];
        $relationColumns = [];

        $fillable = $this->model->getFillable();
        $casts = method_exists($this->model, 'getCasts') ? $this->model->getCasts() : [];

        // Extract belongsTo relationships
        $reflection = new ReflectionClass($this->model);
        foreach ($reflection->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
            if ($method->class !== get_class($this->model) || $method->getNumberOfParameters() > 0) {
                continue;
            }

            try {
                $result = $method->invoke($this->model);
                if ($result instanceof BelongsTo) {
                    $relationColumns[$result->getForeignKeyName()] = [
                        'type' => 'foreignId',
                        'on' => $result->getRelated()->getTable(),
                        'foreignKey' => $result->getForeignKeyName(),
                        'ownerKey' => $result->getOwnerKeyName(),
                    ];
                }
            } catch (\Throwable) {
                continue;
            }
        }

        // Infer types from fillable + casts
        foreach ($fillable as $field) {
            if (isset($relationColumns[$field])) {
                $columns[$field] = $relationColumns[$field];
            } elseif (Str::endsWith($field, '_id')) {
                $columns[$field] = 'unsignedBigInteger';
            } else {
                $type = $casts[$field] ?? 'string';
                $columns[$field] = match ($type) {
                    'datetime', 'date', 'timestamp' => 'timestamp',
                    'boolean' => 'boolean',
                    'json', 'array' => 'json',
                    'int', 'integer' => 'integer',
                    default => 'string'
                };
            }
        }

        // Default ID and timestamps
        if (!array_key_exists('id', $columns)) {
            $columns = ['id' => 'id'] + $columns;
        }

        if ($this->usesSoftDeletes()) {
            $columns['deleted_at'] = 'softDeletes';
        }

        if (!isset($columns['created_at']) && !isset($columns['updated_at'])) {
            $columns['timestamps'] = 'timestamps';
        }

        return $columns;
    }

    public function getBelongsToTables(): array
    {
        $tables = [];

        $reflection = new \ReflectionClass($this->model);
        $methods = $reflection->getMethods(\ReflectionMethod::IS_PUBLIC);

        foreach ($methods as $method) {
            if ($method->class !== get_class($this->model))
                continue;
            if ($method->getNumberOfParameters() > 0)
                continue;

            try {
                $result = $method->invoke($this->model);
                if ($result instanceof \Illuminate\Database\Eloquent\Relations\BelongsTo) {
                    $tables[] = $result->getRelated()->getTable();
                }
            } catch (\Throwable) {
                continue;
            }
        }

        return array_unique($tables);
    }


    protected function usesSoftDeletes(): bool
    {
        $traits = class_uses_recursive(get_class($this->model));
        return in_array(\Illuminate\Database\Eloquent\SoftDeletes::class, $traits);
    }
}
