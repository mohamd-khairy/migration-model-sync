# 🧬 Laravel Migration Model Sync

**Laravel Migration Model Sync** is a development tool that automatically generates or synchronizes Eloquent model files based on your migration files. It extracts columns, data types, and foreign key relationships and applies them directly to your model\"s `$fillable`, `$casts`, and relationship methods.

---

## 🚀 Features

- 🔄 **Sync existing models**: Adds missing `$fillable`, `$casts`, and relationships
- 🧱 **Generate new models**: Creates clean models directly from migrations
- 🧠 **Smart relationship inference**:
  - `belongsTo` from foreign keys
  - `hasMany`, `hasOne`, and `belongsToMany`
- 🗃 Supports both Laravel default and custom migration styles
- 📁 Easily configurable via `config/modelsync.php`
- ✨ **Sync all models**: A new `model:sync-all` command to synchronize all models from available migration files.
- 🛠  **Generate migrations from models**: `model:generate-migration User` — creates a migration file for a specific model
- 🛠  **Generate migrations from models**: `model:generate-migrations` — auto-generates all migration files from models (with dependency-aware sorting)
---

## 📦 Installation

> Laravel versions supported: **8.x – 12.x**

### 1. If using from Packagist:
```bash
composer require khairy/migration-model-sync
```

### 2. Publish the Configuration
```bash
php artisan vendor:publish --tag=modelsync-config
```

This creates the config file: `config/modelsync.php`

---

## ⚙️ Configuration

Edit `config/modelsync.php` to customize behavior:

```php
return [
    \"model_path\" => app_path(\"Models\"),

    \"ignore_columns\" => [
        \"created_at\",
        \"updated_at\",
    ],

    \"relationship_overrides\" => [
        // \"created_by\" => \"belongsTo:App\\Models\\User\"
    ],
];
```

- **model_path**: Specifies the directory where generated models will be stored. By default, this is `app/Models`.
- **ignore_columns**: An array of column names that should be excluded from the `$fillable` and `$casts` properties of the generated models. Common examples include `created_at` and `updated_at`.
- **relationship_overrides**: Allows you to manually define or override specific relationship definitions for models. This is useful for complex or non-standard relationships that cannot be automatically inferred.

---

## 🧪 Usage

### Sync a single model
```bash
php artisan model:sync User
```
This command will:
- Parse the migration that created `users` table
- Add `$fillable`, `$casts`, and any `belongsTo` relationships to `app/Models/User.php`

### Sync all models
```bash
php artisan model:sync-all
```
This command will:
- Iterate through all migration files that create tables
- Parse each migration to extract schema information
- Generate or update the corresponding Eloquent model with `$fillable`, `$casts`, and inferred `belongsTo` relationships.

### Generate migration file from model class
```bash
php artisan model:generate-migration User
```
This command will:
- Read `app/Models/User.php`
- Extract `$fillable`, `$casts`, and any `belongsTo()` relationships
- Infer column types `(string, timestamp, json, etc.)`
- Generate a `create_users_table.php` migration in `database/migrations`
- Include `foreignId()->constrained()->on(...)` if `belongsTo()` is present
- Automatically add `timestamps` and `soft deletes` if applicable


### Generate migration files from model classs
```bash
php artisan model:generate-migrations
```
This command will:
- Loop through all model classes in app/Models/
- Read their `$fillable`, `$casts`, and `belongsTo()` methods
- Generate new `create_*_table.php` migration files
- Sort migrations by relationship dependency (by default)
- Automatically set timestamps to maintain migration order
- Use options like `--only=User,Post` , `--except=Log` , `--sort=none` , and `--force` for advanced control.


### Example Migration
```php
Schema::create(\"profiles\", function (Blueprint $table) {
    $table->id();
    $table->string(\"name\");
    $table->string(\"email\");
    $table->timestamp(\"email_verified_at\")->nullable();
    $table->string(\"password\");
    $table->foreignId(\"user_id\")->constrained();
    $table->timestamps();
});
```

### Resulting Model
```php
namespace App\\Models;

use Illuminate\\Database\\Eloquent\\Model;

class Profile extends Model
{
    protected $fillable = [\"name\", \"email\", \"email_verified_at\", \"password\", \"user_id\"];

    protected $casts = [
        \"email_verified_at\" => \"datetime\",
        \"created_at\" => \"datetime\",
        \"updated_at\" => \"datetime\",
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
```

---

## 💡 How Relationships Are Inferred

| Relation Type   | Detection Logic                                                                 |
|----------------|----------------------------------------------------------------------------------|
| belongsTo       | `foreignId(\"user_id\")->constrained()` or foreign key chains                     |
| hasMany         | Inferred from other tables containing a foreign key to this model *(todo)*     |
| hasOne          | Inferred from `profiles`, `settings`, etc. *(todo)*                             |
| belongsToMany   | Detected from pivot tables like `role_user` *(todo)*                            |

---

## 🧩 Extending the Package

You can override default behavior:
- Use `relationship_overrides` in config to manually define model relations
- You can also stub and customize the model template (coming soon)

---

## 🤝 Contributing

Pull requests are welcome! Please fork the repo, improve, and submit a PR. For major changes, open an issue first.

---

## 📄 License

MIT License © [Khairy](https://github.com/mohamd-khairy)

---

## 🌐 Links
- GitHub: [github.com/mohamd-khairy/migration-model-sync](https://github.com/mohamd-khairy/migration-model-sync)
- Packagist: [packagist.org/packages/khairy/migration-model-sync](https://packagist.org/packages/khairy/migration-model-sync)


