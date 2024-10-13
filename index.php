<?php
declare (strict_types=1);
if (!version_compare(PHP_VERSION, '8.1.0', '>=')) {
    echo sprintf('<body style="background: url(%s) fixed no-repeat;background-size: cover;padding: 0;margin: 0; display: flex; justify-content: center; align-items: center; height: 100vh; background-color: #f8d7da;   font-size: 24px; font-weight: bold; text-align: center;"><div style="background-color: #ffffff69; padding: 64px; max-width: 768px;box-shadow: rgba(149, 157, 165, 0.2) 0px 8px 24px;color: #ff3636;">当前PHP版本 8.0.26 已过时，请更换 <b style="color: green;font-size: 28px;">8.1</b> 或更高版本。</div></body>', "'/assets/user/images/bg.jpg'", PHP_VERSION);
    return;
}
require("kernel/Kernel.php");