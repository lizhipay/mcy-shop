<?php
declare (strict_types=1);

namespace App\Command;

use Kernel\Console\Command;
use Kernel\Log\Const\Color;
use Kernel\Log\Log;
use Kernel\Util\Shell;

class Composer extends Command
{

    private string $bin = BASE_PATH . "bin";

    /**
     * @param string $package
     * @return void
     * @throws \ReflectionException
     */
    public function require(string $package): void
    {
        Log::inst()->stdout("Composer is relying on {$package}, it may take a long time, please be patient..", Color::YELLOW, true);
        \Kernel\Plugin\Composer::inst()->updatePackagist();
        Shell::inst()->exec("{$this->bin} composer require {$package} --no-interaction");
        Log::inst()->stdout("Composer dependencies completed.", Color::GREEN, true);
    }


    /**
     * @param string $package
     * @return void
     * @throws \ReflectionException
     */
    public function remove(string $package): void
    {
        Log::inst()->stdout("Composer is removing dependency: {$package}, please wait patiently", Color::YELLOW, true);
        Shell::inst()->exec("{$this->bin} composer remove {$package} --no-interaction");
        Log::inst()->stdout("Composer dependency removal completed.", Color::GREEN, true);
    }
}