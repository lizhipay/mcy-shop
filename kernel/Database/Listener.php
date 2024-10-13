<?php
declare (strict_types=1);

namespace Kernel\Database;

use Hyperf\Database\Events\QueryExecuted;
use Hyperf\Event\EventDispatcher;
use Hyperf\Event\ListenerProvider;
use Kernel\Component\Singleton;
use Kernel\Context\App;
use Kernel\Plugin\Const\Point;
use Kernel\Plugin\Plugin;

class Listener
{

    use Singleton;


    /**
     * @var EventDispatcher|null
     */
    private ?EventDispatcher $queryEvent = null;


    /**
     * @return EventDispatcher
     */
    public function query(): EventDispatcher
    {
        if ($this->queryEvent) {
            return $this->queryEvent;
        }
        $provider = new ListenerProvider();
        $provider->on(QueryExecuted::class, function (QueryExecuted $event) {
            Plugin::instance()->hook(App::$mEnv, Point::DB_QUERY_EXECUTED, \Kernel\Plugin\Const\Plugin::HOOK_TYPE_PAGE, $event);
        });
        $this->queryEvent = new EventDispatcher($provider);
        return $this->queryEvent;
    }
}