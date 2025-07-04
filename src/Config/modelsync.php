<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Model Output Path
    |--------------------------------------------------------------------------
    |
    | The default path where generated model files will be placed.
    | You can change this to a custom location if needed.
    |
    */
    'model_path' => app_path('Models'),

    /*
    |--------------------------------------------------------------------------
    | Ignored Columns
    |--------------------------------------------------------------------------
    |
    | These columns will be excluded from the $fillable and $casts arrays.
    | Useful for timestamps or framework-managed fields.
    |
    */
    'ignore_columns' => [
        'created_at',
        'updated_at',
    ],

    /*
    |--------------------------------------------------------------------------
    | Hidden Attributes
    |--------------------------------------------------------------------------
    |
    | Fields listed here will be automatically added to the $hidden array
    | in generated models, but only if the column exists in the migration.
    |
    */
    'hidden' => [
        'password',
        'remember_token',
    ],

    /*
    |--------------------------------------------------------------------------
    | Hashed Fields
    |--------------------------------------------------------------------------
    |
    | Fields listed here (like 'password') are expected to be hashed,
    | which you can use in your own customization logic or extensions.
    | This is not applied automatically unless implemented in your generator.
    |
    */
    'hashed' => [
        'password',
    ],

    /*
    |--------------------------------------------------------------------------
    | Relationship Overrides
    |--------------------------------------------------------------------------
    |
    | Use this to override auto-inferred relationships.
    | Format: 'column_name' => 'type:Fully\\Qualified\\Model@methodName'
    |
    | Example:
    | 'created_by' => 'belongsTo:App\\Models\\Admin@author'
    |
    */
    'relationship_overrides' => [
        // 'created_by' => 'belongsTo:App\\Models\\User@author',
    ],

    /*
    |--------------------------------------------------------------------------
    | Excluded Tables
    |--------------------------------------------------------------------------
    |
    | Tables listed here will be skipped entirely during sync/model generation.
    | This is helpful for system tables, pivot tables, or logs.
    |
    */
    'exclude_tables' => [
        'cache',
        'migrations',
        'failed_jobs',
        'jobs',
        'personal_access_tokens',
    ],
];
