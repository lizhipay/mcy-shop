<?php
declare (strict_types=1);

use \Kernel\Console\Console;


//将命令注册到系统
shell_exec("sudo cp " . BASE_PATH . "console.sh /usr/local/bin/mcy");


//Console::instance()->add(command: "tst", callable: [\App\Command\Test::class, "test"], name: "测试专用", desc: "测试用的");


//数据库
Console::instance()->add(command: "database.model.create", callable: [\App\Command\Database::class, "createModel"], name: "生成数据库模型", desc: "提供一个表名或多个，使用空格隔开，表名无需带数据库前缀");

//语言包
Console::instance()->add(command: "language.create", callable: [\App\Command\Language::class, "createPack"], name: "创建语言包", desc: "参数1：原文，参数2：译文，参数3：国家语言代码");
Console::instance()->add(command: "language.del", callable: [\App\Command\Language::class, "delPack"], name: "删除语言包", desc: "参数1：原文，参数2：国家语言代码");
Console::instance()->add(command: "language.all.del", callable: [\App\Command\Language::class, "delAllPack"], name: "批量删除语言包", desc: "提供多个原文，使用空格隔开，复杂的可使用双引号包裹");
Console::instance()->add(command: "language.code", callable: [\App\Command\Language::class, "getCode"], name: "获取国家语言代码列表");


//压缩
Console::instance()->add(command: "compress.js.merge", callable: [\App\Command\Compress::class, "mergeJs"], name: "压缩当前项目JS");
Console::instance()->add(command: "compress.css.merge", callable: [\App\Command\Compress::class, "mergeCss"], name: "压缩当前项目CSS");
Console::instance()->add(command: "compress.all", callable: [\App\Command\Compress::class, "all"], name: "压缩当前项目JS和CSS");

//服务管理
Console::instance()->add(command: "service.install", callable: [\App\Command\Service::class, "install"], name: "安装服务");
Console::instance()->add(command: "service.start", callable: [\App\Command\Service::class, "start"], name: "启动服务");
Console::instance()->add(command: "service.stop", callable: [\App\Command\Service::class, "stop"], name: "停止服务");
Console::instance()->add(command: "service.restart", callable: [\App\Command\Service::class, "restart"], name: "重启服务");
Console::instance()->add(command: "service.uninstall", callable: [\App\Command\Service::class, "uninstall"], name: "卸载服务");

//插件管理
Console::instance()->add(command: "plugin.stop", callable: [\App\Command\Plugin::class, "stop"], name: "停止插件", desc: "参数1：插件标识，参数2：用户ID（不传代表主站插件）");
Console::instance()->add(command: "plugin.startups", callable: [\App\Command\Plugin::class, "list"], name: "获取正在运行的插件列表", desc: "参数1：用户ID（不传代表主站插件）");


Console::instance()->add(command: "kit.update", callable: [\App\Command\Kit::class, "update"], name: "更新系统版本", desc: "检查新版本并将程序更新至最新版");
Console::instance()->add(command: "kit.reset", callable: [\App\Command\Kit::class, "reset"], name: "重置超级管理员密码", desc: "参数1：新密码");


Console::instance()->add(command: "composer.require", callable: [\App\Command\Composer::class, "require"], name: "添加Composer依赖包", desc: "参数1：包名");
Console::instance()->add(command: "composer.remove", callable: [\App\Command\Composer::class, "remove"], name: "删除Composer依赖包", desc: "参数1：包名");


//迁移管理
Console::instance()->add(command: "migration.v3.user", callable: [\App\Command\Migration::class, "v3_user"], name: "导入异次元V3.0用户数据", desc: "参数1：根目录下.sql的文件名，如：v3.sql");



