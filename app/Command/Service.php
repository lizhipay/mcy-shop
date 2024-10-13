<?php
declare (strict_types=1);

namespace App\Command;

use Kernel\Context\App;
use Kernel\Log\Const\Color;
use Kernel\Log\Log;
use Kernel\Util\Config;
use Kernel\Util\Shell;

class Service
{

    private string $name;
    private string $file;

    private string $index = BASE_PATH . "index.php";

    private string $bin = BASE_PATH . "bin";


    public function __construct()
    {
        $config = Config::get("cli-server");
        $this->name = $config['name'] . "-" . $config['port'];
        $this->file = "/etc/systemd/system/{$this->name}.service";
    }


    /**
     * 安装服务
     * @return void
     * @throws \ReflectionException
     */
    public function install(): void
    {
        $commands = <<<EOL
  if [ -f "{$this->file}" ]; then
    echo "exists"
  else
    cat <<EOF >"{$this->file}"
[Unit]
Description={$this->name}
After=network.target

[Service]
ExecStart={$this->bin} {$this->index}
Restart=always
RestartSec=3

[Install]
WantedBy=multi-user.target
EOF
    systemctl enable "{$this->name}"
    echo "success"
  fi
EOL;

        if (trim(Shell::inst()->exec($commands)) == "exists") {
            Log::inst()->stdout("Service installation failed. The service may already exist.", Color::RED, true);
        } else {
            Log::inst()->stdout("Service installation successfully.", Color::GREEN, true);
            $this->start();
        }
    }


    /**
     * @return void
     * @throws \ReflectionException
     */
    public function start(): void
    {
        Log::inst()->stdout("Service starting...", Color::BLUE, true);
        Shell::inst()->exec("systemctl start {$this->name}");
        Log::inst()->stdout("Service started successfully.", Color::GREEN, true);
    }


    /**
     * @return void
     * @throws \ReflectionException
     */
    public function stop(): void
    {
        Log::inst()->stdout("Service stopping...", Color::BLUE, true);
        Shell::inst()->exec("systemctl stop {$this->name}");
        Log::inst()->stdout("Service stop successfully.", Color::GREEN, true);
    }


    /**
     * @return void
     * @throws \ReflectionException
     */
    public function restart(): void
    {
        if (App::$mode === "dev") {
            Shell::inst()->exec(BASE_PATH . "dev.sh restart"); 
            return;
        }
        Log::inst()->stdout("Service restart...", Color::BLUE, true);
        Shell::inst()->exec("systemctl restart {$this->name}");
        Log::inst()->stdout("Service restart successfully.", Color::GREEN, true);
    }

    /**
     * @return void
     * @throws \ReflectionException
     */
    public function uninstall(): void
    {
        Log::inst()->stdout("Service uninstallation...", Color::BLUE, true);
        $this->stop();
        Shell::inst()->exec("systemctl disable {$this->name}");
        Shell::inst()->exec("rm -f {$this->file}");
        Log::inst()->stdout("Service uninstall successfully.", Color::GREEN, true);
    }
}