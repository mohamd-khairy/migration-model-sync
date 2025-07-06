<?php

namespace Khairy\MigrationModelSync\Services;

class MigrationBuilder
{
    public function build(string $table, array $columns): string
    {
        $fields = '';
        foreach ($columns as $name => $type) {
            if ($type === 'id') {
                $fields .= "            \$table->id();\n";
            } elseif ($type === 'softDeletes') {
                $fields .= "            \$table->softDeletes();\n";
            } elseif ($type === 'timestamps') {
                $fields .= "            \$table->timestamps();\n";
            } elseif (is_array($type) && $type['type'] === 'foreignId') {
                $fields .= "            \$table->foreignId('{$name}')->constrained()->references('{$type['ownerKey']}')->on('{$type['on']}');\n";
            } elseif ($name == 'email') {
                $fields .= "            \$table->{$type}('{$name}')->unique();\n";
            } elseif ($type == 'boolean') {
                $fields .= "            \$table->{$type}('{$name}')->default(false);\n";
            } else {
                $fields .= "            \$table->{$type}('{$name}')->nullable();\n";
            }
        }

        return <<<PHP
<?php

use Illuminate\\Database\\Migrations\\Migration;
use Illuminate\\Database\\Schema\\Blueprint;
use Illuminate\\Support\\Facades\\Schema;

return new class extends Migration{
    public function up(): void
    {
        Schema::create('{$table}', function (Blueprint \$table) {
{$fields}        });
    }

    public function down(): void
    {
        Schema::dropIfExists('{$table}');
    }
};
PHP;
    }
}
