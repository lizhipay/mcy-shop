<?php
declare(strict_types=1);

namespace Kernel\Cache;

use Kernel\Component\Singleton;
use Kernel\Util\Process;
use Swoole\Table;

class Cache
{
    use Singleton;

    /**
     * @var Table
     */
    private Table $table;

    /**
     * @return float
     */
    public function initialize(): float
    {
        $cpuNum = Process::cpuNum();
        $this->table = new Table(4096 * $cpuNum);
        $this->table->column('data', Table::TYPE_STRING, 256);
        $this->table->create();
        return (float)number_format($this->table->memorySize / 1024 / 1024, 2);
    }

    /**
     * @param string $key
     * @param mixed $data
     * @return void
     */
    public function set(string $key, mixed $data): void
    {
        $this->table->set($key, ["data" => serialize($data)]);
    }

    /**
     * @param string $key
     * @return bool
     */
    public function has(string $key): bool
    {
        return $this->table->exists($key);
    }

    /**
     * @param string $key
     * @return mixed
     */
    public function get(string $key): mixed
    {
        if (!$this->has($key)) {
            return null;
        }

        return unserialize($this->table->get($key, "data"));
    }


    /**
     * @param string $key
     * @return bool
     */
    public function del(string $key): bool
    {
        return $this->table->del($key);
    }

    /**
     * @return int
     */
    public function getSize(): int
    {
        return $this->table->getSize();
    }

    /**
     * @return int
     */
    public function getCount(): int
    {
        return $this->table->count();
    }

    /**
     * @return int
     */
    public function getMemorySize(): int
    {
        return $this->table->getMemorySize();
    }

    /**
     * @param string $keywords
     * @return array
     */
    public function search(string $keywords): array
    {
        $data = [];
        foreach ($this->table as $key => $value) {
            if (str_contains($key, $keywords)) {
                $data[$key] = unserialize($value['data']);
            }
        }
        return $data;
    }
}