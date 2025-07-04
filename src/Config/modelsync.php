<?php

return [
    'model_path' => app_path('Models'),
    'ignore_columns' => ['created_at', 'updated_at'],
    'relationship_overrides' => [
        // 'created_by' => 'belongsTo:App\Models\User'
    ],
];
