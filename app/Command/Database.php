<?php
declare(strict_types=1);

namespace App\Command;

use Kernel\Console\Command;
use Kernel\Context\App;
use Kernel\Database\Db;
use Kernel\Database\Schema;


class Database extends Command
{
    /**
     * @return void
     */
    public function createModel(): void
    {
        if (empty($this->param)) {
            $this->error("生成模型最少提供1个表");
            return;
        }

        $prefix = App::$database['prefix'];

        foreach ($this->param as $table) {
            $columns = Schema::getColumnListing($table);
            $fullTableName = $prefix . $table;
            $columnDetails = DB::select("SHOW COLUMNS FROM `{$fullTableName}`");

            $properties = [];
            $casts = [];

            foreach ($columnDetails as $detail) {
                $type = $this->mapColumnTypeToPropertyType($detail->Type);
                $properties[] = " * @property {$type} \${$detail->Field}";
                if ($type !== 'string') { // Only add to casts if not string
                    $casts[$detail->Field] = $type;
                }
            }

            $annotation = "/**\n" . implode("\n", $properties) . "\n */";
            $array = "[" . implode(", ", array_map(fn($type, $field) => "'{$field}' => '{$type}'", $casts, array_keys($casts))) . "]";
            $fileName = $this->generateModelFileName($table);

            $php = $this->generateModelClass($fileName, $table, $annotation, $array);

            $this->writeModelFile($fileName, $php);
        }
    }

    /**
     * @param string $columnType
     * @return string
     */
    private function mapColumnTypeToPropertyType(string $columnType): string
    {
        if (preg_match("/decimal|float|double/", $columnType)) {
            return 'float';
        } elseif (str_contains($columnType, "int")) {
            return 'integer';
        } else {
            return 'string';
        }
    }

    /**
     * @param string $table
     * @return string
     */
    private function generateModelFileName(string $table): string
    {
        return implode('', array_map('ucfirst', explode('_', $table)));
    }

    /**
     * @param string $fileName
     * @param string $table
     * @param string $annotation
     * @param string $castsArray
     * @return string
     */
    private function generateModelClass(string $fileName, string $table, string $annotation, string $castsArray): string
    {
        return <<<PHP
<?php
declare(strict_types=1);

namespace App\Model;

use Kernel\Database\Model;

$annotation
class $fileName extends Model
{
    protected ?string \$table = '$table';
    public bool \$timestamps = false;
    protected array \$casts = $castsArray;
}
PHP;
    }

    /**
     * @param string $fileName
     * @param string $phpContent
     * @return void
     */
    private function writeModelFile(string $fileName, string $phpContent): void
    {
        $filePath = BASE_PATH . "/app/Model/" . $fileName . ".php";
        if (!file_exists($filePath)) {
            file_put_contents($filePath, $phpContent);
            $this->success("[{$fileName}]模型生成成功，完整路径：{$filePath}");
        } else {
            $this->error("[{$fileName}]模型已存在，如需重新生成，请删除原来的模型");
        }
    }

}
