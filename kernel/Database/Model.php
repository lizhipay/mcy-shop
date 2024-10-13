<?php
declare (strict_types=1);

namespace Kernel\Database;

use Hyperf\Database\ConnectionInterface;
use Hyperf\Database\Model\Model as Base;
use Kernel\Component\Singleton;

/**
 * @method deleted(int $id)
 * @method deleting(int $id)
 * @method saved()
 */
abstract class Model extends Base
{
    /**
     * @param string $event
     * @param mixed ...$args
     * @return void
     */
    public function dispatcher(string $event, ...$args): void
    {
        if (method_exists($this, $event)) {
            call_user_func_array([$this, $event], $args);
        }
    }

    /**
     * @return ConnectionInterface
     * @throws \ReflectionException
     */
    public function getConnection(): ConnectionInterface
    {
        return Connection::instance()->get();
    }

    /**
     * @param array $options
     * @return bool
     */
    public function save(array $options = []): bool
    {
        $this->dispatcher("saving");
        $bool = parent::save($options);
        $this->dispatcher("saved");
        if ($this->wasRecentlyCreated) {
            $this->dispatcher("created");
        }
        return $bool;
    }

    /**
     * @return bool|null
     * @throws \Exception
     */
    public function delete(): ?bool
    {
        $this->dispatcher("deleting", (int)$this->id);
        $result = parent::delete();
        $this->dispatcher("deleted", (int)$this->id);
        return $result;
    }
}