<?php

namespace Khairy\MigrationModelSync\Generators;

use Illuminate\Support\Str;
use Symfony\Component\Finder\Finder;

class MigrationParser
{
    protected string $model;

    public function __construct(string $model)
    {
        $this->model = $model;
    }

    public function parse(): array
    {
        $excludedTables = config('modelsync.exclude_tables', []);
        $table = Str::snake(Str::pluralStudly(class_basename($this->model)));

        // Skip parsing if table is in excluded list
        if (in_array($table, $excludedTables)) {
            return [
                'columns' => [],
                'foreign_keys' => [],
            ];
        }

        $migrationPath = database_path('migrations');

        $finder = new Finder();
        $finder->files()->in($migrationPath)->name("*create_{$table}_table.php");

        foreach ($finder as $file) {
            $content = file_get_contents($file->getRealPath());

            // Match column definitions
            preg_match_all('/\$table->(\w+)\([\'"]([^\'"]+)[\'"]\)/', $content, $matches, PREG_SET_ORDER);
            $columns = [];
            foreach ($matches as $match) {
                $columns[$match[2]] = $match[1];
            }

            // Match foreign keys
            $foreignKeys = [];

            // ->foreignId('user_id')->constrained()
            preg_match_all("/->foreignId\('([^']+)'\)->constrained\(\)/", $content, $inferredMatches, PREG_SET_ORDER);
            foreach ($inferredMatches as $match) {
                $column = $match[1];
                $inferredTable = Str::plural(Str::beforeLast($column, '_id'));
                $foreignKeys[$column] = $inferredTable;
            }

            // ->foreign('user_id')->references('id')->on('users')
            preg_match_all("/->foreign\('([^']+)'\)->references\('([^']+)'\)->on\('([^']+)'\)/", $content, $classicMatches, PREG_SET_ORDER);
            foreach ($classicMatches as $match) {
                $foreignKeys[$match[1]] = $match[3];
            }

            return [
                'columns' => $columns,
                'foreign_keys' => $foreignKeys,
            ];
        }

        return [
            'columns' => [],
            'foreign_keys' => [],
        ];
    }
}
