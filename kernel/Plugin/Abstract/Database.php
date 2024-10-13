<?php
declare (strict_types=1);

namespace Kernel\Plugin\Abstract;

use Kernel\Database\Schema;
use Kernel\Plugin\Entity\Plugin;

abstract class Database implements \Kernel\Plugin\Handle\Database
{
    /**
     * @var string
     */
    private string $usr;

    /**
     * @var Plugin
     */
    protected Plugin $plugin;

    /**
     * @param string $usr
     * @param Plugin $plugin
     */
    public function __construct(Plugin $plugin, string $usr = "*")
    {
        if ($usr == "*") {
            $usr = "";
        } else {
            $usr = strtolower($usr) . "_";
        }
        $this->plugin = $plugin;
        $this->usr = $usr;
    }

    /**
     * @param string $table
     * @return string
     */
    protected function getTable(string $table): string
    {
        return $this->usr . $table;
    }

    /**
     * @param string $table
     * @param string $column
     * @return bool
     */
    protected function hasColumn(string $table, string $column): bool
    {
        return Schema::hasColumn($this->getTable($table), $column);
    }

    /**
     * @param string $table
     * @param array $columns
     * @return bool
     */
    protected function hasColumns(string $table, array $columns): bool
    {
        return Schema::hasColumns($this->getTable($table), $columns);
    }

    /**
     * @param string $table
     * @return bool
     */
    protected function hasTable(string $table): bool
    {
        return Schema::hasTable($this->getTable($table));
    }

    /**
     * @param string $table
     * @param \Closure $callback
     * @return void
     */
    protected function table(string $table, \Closure $callback): void
    {

        Schema::table($this->getTable($table), $callback);
    }

    /**
     * @param string $table
     * @param \Closure $callback
     * @return void
     */
    protected function create(string $table, \Closure $callback): void
    {
        Schema::create($this->getTable($table), $callback);
    }

    /**
     * @param string $table
     * @return void
     */
    protected function drop(string $table): void
    {
        Schema::drop($this->getTable($table));
    }

    /**
     * @param string $table
     * @return void
     */
    protected function dropIfExists(string $table): void
    {
        Schema::dropIfExists($this->getTable($table));
    }
}