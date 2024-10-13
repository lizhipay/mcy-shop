<?php
declare (strict_types=1);

namespace Kernel\Console;

use Kernel\Log\Const\Color;
use Kernel\Log\Log;

abstract class Command
{

    /**
     * @var array
     */
    protected array $param;

    /**
     * @var \Kernel\Context\Command
     */
    protected \Kernel\Context\Command $command;


    public function __construct(array $param, \Kernel\Context\Command $command)
    {
        $this->param = $param;
        $this->command = $command;
    }

    /**
     * @param string $message
     * @return void
     */
    protected function info(string $message): void
    {
        Log::inst()->stdout(sprintf("[%s]: %s", $this->command->getCommand(), $message), Color::BLUE, true);
    }

    /**
     * @param string $message
     * @return void
     */
    protected function success(string $message): void
    {
        Log::inst()->stdout(sprintf("[%s]: %s", $this->command->getCommand(), $message), Color::GREEN, true);
    }

    /**
     * @param string $message
     * @return void
     */
    protected function error(string $message): void
    {
        Log::inst()->stdout(sprintf("[%s]: %s", $this->command->getCommand(), $message), Color::RED, true);
    }
}