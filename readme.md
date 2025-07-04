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


