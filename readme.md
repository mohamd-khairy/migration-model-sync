# 🧬 Laravel Migration Model Sync

**Laravel Migration Model Sync** is a development tool that automatically generates or synchronizes Eloquent model files based on your migration files. It extracts columns, data types, and foreign key relationships and applies them directly to your model's `$fillable`, `$casts`, and relationship methods.

---

## 🚀 Features

- 🔄 **Sync existing models**: Adds missing `$fillable`, `$casts`, and relationships
- 🧱 **Generate new models**: Creates clean models directly from migrations
- 🧠 **Smart relationship inference**:
  - `belongsTo` from foreign keys
  - `hasMany`, `hasOne`, and `belongsToMany` *(coming soon)*
- 🗃 Supports both Laravel default and custom migration styles
- 📁 Easily configurable via `config/modelsync.php`

---

## 📦 Installation

> Laravel versions supported: **8.x – 11.x**

### 1. If using from Packagist:
```bash
composer require khairy/migration-model-sync
```

### 2. If using locally as a path repo:
Add this to your Laravel app’s `composer.json`:
```json
"repositories": [
  {
    "type": "path",
    "url": "packages/migration-model-sync"
  }
]
```
Then run:
```bash
composer require khairy/migration-model-sync:@dev -W
```

### 3. Publish the Configuration
```bash
php artisan vendor:publish --tag=modelsync-config
```

This creates the config file: `config/modelsync.php`

---

## ⚙️ Configuration

Edit `config/modelsync.php` to customize behavior:

```php
return [
    'model_path' => app_path('Models'),

    'ignore_columns' => [
        'created_at',
        'updated_at',
    ],

    'relationship_overrides' => [
        // 'created_by' => 'belongsTo:App\\Models\\User'
    ],
];
```

- **model_path**: Where generated models go
- **ignore_columns**: Will not include in `$fillable` or `$casts`
- **relationship_overrides**: Force specific relationship definitions

---

## 🧪 Usage

### Sync a single model
```bash
php artisan model:sync User
```
This command will:
- Parse the migration that created `users` table
- Add `$fillable`, `$casts`, and any `belongsTo` relationships to `app/Models/User.php`

### Example Migration
```php
Schema::create('profiles', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->string('email');
    $table->timestamp('email_verified_at')->nullable();
    $table->string('password');
    $table->foreignId('user_id')->constrained();
    $table->timestamps();
});
```

### Resulting Model
```php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Profile extends Model
{
    protected $fillable = ['name', 'email', 'email_verified_at', 'password', 'user_id'];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
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
| belongsTo       | `foreignId('user_id')->constrained()` or foreign key chains                     |
| hasMany         | Inferred from other tables containing a foreign key to this model *(todo)*     |
| hasOne          | Inferred from `profiles`, `settings`, etc. *(todo)*                             |
| belongsToMany   | Detected from pivot tables like `role_user` *(todo)*                            |

---

## 🧩 Extending the Package

You can override default behavior:
- Use `relationship_overrides` in config to manually define model relations
- You can also stub and customize the model template (coming soon)

---

## 🛠 Roadmap

- [x] Parse single migration file
- [x] Generate model with fillable & casts
- [x] Infer belongsTo relationships
- [ ] Infer hasMany and hasOne
- [ ] Detect and handle pivot tables (belongsToMany)
- [ ] Add support for `model:sync-all`
- [ ] Auto PHPDoc annotations for IDE support
- [ ] Publish stub for model template customization

---

## 🤝 Contributing

Pull requests are welcome! Please fork the repo, improve, and submit a PR. For major changes, open an issue first.

---

## 📄 License

MIT License © [Khairy](https://github.com/yourname)

---

## 🌐 Links
- GitHub: [github.com/yourname/migration-model-sync](https://github.com/yourname/migration-model-sync)
- Packagist: [packagist.org/packages/khairy/migration-model-sync](https://packagist.org/packages/khairy/migration-model-sync)
# migration-model-sync
