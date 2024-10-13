<?php
declare (strict_types=1);

namespace Kernel\Plugin;

use Kernel\Plugin\Entity\Plugin;
use Symfony\Component\Finder\Finder;

class Query
{

    private string $env;
    private int $page = 1;
    private int $limit = 10;
    private int $state = -1;

    /**
     * @var int|null
     */
    private ?int $type = null;

    /**
     * @var string|null
     */
    private ?string $keyword = null;


    /**
     * @param string $env
     */
    public function __construct(string $env = "/app/Plugin")
    {
        $this->env = $env;
    }


    /**
     * @param int $page
     * @param int $limit
     * @return void
     */
    public function setPaginate(int $page, int $limit): void
    {
        $this->page = $page;
        $this->limit = $limit;
    }

    /**
     * @param int $state
     * @return void
     */
    public function setState(int $state): void
    {
        $this->state = $state;
    }


    /**
     * @param string $keyword
     * @return void
     */
    public function setKeyword(string $keyword): void
    {
        $this->keyword = trim(strtoupper(urldecode($keyword)));
    }

    /**
     * @param int $type
     * @return void
     */
    public function setType(int $type): void
    {
        $this->type = $type;
    }


    /**
     * @param Plugin $plugin
     * @return bool
     */
    private function isKeywordMatch(Plugin $plugin): bool
    {
        if (!$this->keyword) {
            return true;
        }

        $searchFields = [strtoupper($plugin->info['name']), strtoupper($plugin->info['desc']), strtoupper($plugin->info['author']), strtoupper($plugin->name)];
        foreach ($searchFields as $field) {
            if (str_contains($field, $this->keyword)) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param Plugin $plugin
     * @return bool
     */
    private function isStateMatch(Plugin $plugin): bool
    {
        if ($this->state == -1) {
            return true;
        }

        //状态查询
        if ($this->state == $plugin->state['run']) {
            return true;
        }
        return false;
    }


    /**
     * @param Plugin $plugin
     * @return bool
     */
    private function isTypeMatch(Plugin $plugin): bool
    {
        // 如果没有指定类型，那么任何插件都匹配
        if (!$this->type || $this->type == 16) {
            return true;
        }

        return $this->type == $plugin->info[\Kernel\Plugin\Const\Plugin::TYPE];
    }

    /**
     * @param Plugin $plugin
     * @param array $counter
     * @return void
     */
    private function updateStateNums(Plugin $plugin, array &$counter): void
    {
        switch ($plugin->state['run']) {
            case 0:
                $counter[0]++;
                break;
            case 1:
                $counter[1]++;
                break;
            case 2:
                $counter[2]++;
                break;
            case 3:
                $counter[3]++;
                break;
        }
    }

    /**
     * @return array
     * @throws \ReflectionException
     */
    public function list(): array
    {
        $finder = is_dir(BASE_PATH . $this->env) ? Finder::create()->in(BASE_PATH . $this->env)->depth("== 0")->ignoreUnreadableDirs(true)->directories() : [];
        $data = [];
        $counter = [0, 0, 0, 0];
        foreach ($finder as $item) {
            $plugin = \Kernel\Plugin\Plugin::inst()->getPlugin($item->getFilename(), $this->env);

            if (!$plugin) {
                continue;
            }

            $this->updateStateNums($plugin, $counter);

            //搜索
            if (!$this->isKeywordMatch($plugin)) {
                continue;
            }

            //状态查询
            if (!$this->isStateMatch($plugin)) {
                continue;
            }

            //能力查询
            if (!$this->isTypeMatch($plugin)) {
                continue;
            }

            $data[] = $plugin;
        }

        //插件置顶功能
        usort($data, function ($a, $b) {
            $updateA = $a->getSystemConfig("update") ?? 0;
            $updateB = $b->getSystemConfig("update") ?? 0;
            $topA = $a->getSystemConfig("top") ?? 0;
            $topB = $b->getSystemConfig("top") ?? 0;

            if ($updateA != $updateB) {
                return $updateB <=> $updateA;
            }

            return $topB <=> $topA;
        });

        $totalItems = count($data);
        $offset = ($this->page - 1) * $this->limit;
        $data = array_slice($data, $offset, $this->limit);

        return [
            'list' => $data,
            'total' => $totalItems,
            'stop' => $counter[0],
            'running' => $counter[1],
            'starting' => $counter[2],
            'stopping' => $counter[3]
        ];
    }

}