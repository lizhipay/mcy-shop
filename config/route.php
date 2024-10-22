<?php
/** @noinspection PhpUnhandledExceptionInspection */
declare (strict_types=1);

use Kernel\Util\Route;


Route::add("/hello", [\App\Controller\Index::class, "hello"], "GET");
Route::add("/wait/state", [\App\Controller\Index::class, "wait"], "POST");
Route::add("/owner", [\App\Controller\Index::class, "owner"], "POST");

Route::add("/admin", [\App\Controller\Admin\Auth::class, "login"], "GET");
Route::add("/admin", [\App\Controller\Admin\API\Auth::class, "login"], "POST");
Route::add("/admin/auth/secure/tunnel", [\App\Controller\Admin\API\Auth::class, "getSecureTunnel"], "POST");

Route::add("/admin/dashboard", [\App\Controller\Admin\Main\Dashboard::class, "index"], "GET");
Route::add("/admin/dashboard/statistics", [\App\Controller\Admin\API\Main\Dashboard::class, "statistics"], "POST");

#修改密码
Route::add("/admin/personal/logout", [\App\Controller\Admin\Manage\Personal::class, "logout"], "GET");
Route::add("/admin/personal/edit", [\App\Controller\Admin\API\Manage\Personal::class, "edit"], "POST");
Route::add("/admin/personal/login/log", [\App\Controller\Admin\Manage\Personal::class, "loginLog"], "GET");
Route::add("/admin/personal/login/log", [\App\Controller\Admin\API\Manage\Personal::class, "loginLog"], "POST");

//管理员
Route::add("/admin/manage", [\App\Controller\Admin\Manage\Manage::class, "index"], "GET");
Route::add("/admin/manage/get", [\App\Controller\Admin\API\Manage\Manage::class, "get"], "POST");
Route::add("/admin/manage/save", [\App\Controller\Admin\API\Manage\Manage::class, "save"], "POST");
Route::add("/admin/manage/del", [\App\Controller\Admin\API\Manage\Manage::class, "del"], "POST");
//角色管理
Route::add("/admin/role", [\App\Controller\Admin\Manage\Role::class, "index"], "GET");
Route::add("/admin/role/get", [\App\Controller\Admin\API\Manage\Role::class, "get"], "POST");
Route::add("/admin/role/save", [\App\Controller\Admin\API\Manage\Role::class, "save"], "POST");
Route::add("/admin/role/del", [\App\Controller\Admin\API\Manage\Role::class, "del"], "POST");
//权限管理
Route::add("/admin/permission", [\App\Controller\Admin\Manage\Permission::class, "index"], "GET");
Route::add("/admin/permission/get", [\App\Controller\Admin\API\Manage\Permission::class, "get"], "POST");
Route::add("/admin/permission/save", [\App\Controller\Admin\API\Manage\Permission::class, "save"], "POST");


//数据字典
Route::add("/admin/dict/role", [\App\Controller\Admin\API\Dict::class, "role"], "POST");
Route::add("/admin/dict/permission", [\App\Controller\Admin\API\Dict::class, "permission"], "POST");
Route::add("/admin/dict/repertoryCategory", [\App\Controller\Admin\API\Dict::class, "repertoryCategory"], "POST");
Route::add("/admin/dict/repertoryItem", [\App\Controller\Admin\API\Dict::class, "repertoryItem"], "POST");
Route::add("/admin/dict/repertoryPluginItem", [\App\Controller\Admin\API\Dict::class, "repertoryPluginItem"], "POST");
Route::add("/admin/dict/repertoryItemSku", [\App\Controller\Admin\API\Dict::class, "repertoryItemSku"], "POST");
Route::add("/admin/dict/theme", [\App\Controller\Admin\API\Dict::class, "theme"], "POST");
Route::add("/admin/dict/shopCategory", [\App\Controller\Admin\API\Dict::class, "shopCategory"], "POST");
Route::add("/admin/dict/ship", [\App\Controller\Admin\API\Dict::class, "ship"], "POST");
Route::add("/admin/dict/pay", [\App\Controller\Admin\API\Dict::class, "pay"], "POST");
Route::add("/admin/dict/user", [\App\Controller\Admin\API\Dict::class, "user"], "POST");
Route::add("/admin/dict/itemMarkupTemplate", [\App\Controller\Admin\API\Dict::class, "itemMarkupTemplate"], "POST");
Route::add("/admin/dict/repertoryItemMarkupTemplate", [\App\Controller\Admin\API\Dict::class, "repertoryItemMarkupTemplate"], "POST");
Route::add("/admin/dict/payApi", [\App\Controller\Admin\API\Dict::class, "payApi"], "POST");
Route::add("/admin/dict/group", [\App\Controller\Admin\API\Dict::class, "group"], "POST");
Route::add("/admin/dict/level", [\App\Controller\Admin\API\Dict::class, "level"], "POST");
Route::add("/admin/dict/item", [\App\Controller\Admin\API\Dict::class, "item"], "POST");
Route::add("/admin/dict/itemSku", [\App\Controller\Admin\API\Dict::class, "itemSku"], "POST");
Route::add("/admin/dict/storeGroup", [\App\Controller\Admin\API\Dict::class, "getStoreGroup"], "POST");
Route::add("/admin/dict/pluginConfig", [\App\Controller\Admin\API\Dict::class, "pluginConfig"], "POST");
Route::add("/admin/dict/currency", [\App\Controller\Admin\API\Dict::class, "currency"], "POST");


//后台文件上传相关API
Route::add("/admin/upload", [\App\Controller\Admin\API\Upload::class, "main"], "POST");
Route::add("/admin/upload", [\App\Controller\Admin\Upload\Upload::class, "index"], "GET");
Route::add("/admin/upload/get", [\App\Controller\Admin\API\Upload\Upload::class, "get"], "POST");
Route::add("/admin/upload/del", [\App\Controller\Admin\API\Upload\Upload::class, "del"], "POST");

//任务Task相关API
Route::add("/admin/task/autoReceipt", [\App\Controller\Admin\API\Task::class, "autoReceipt"], "POST");


# 网站设置
Route::add("/admin/config", [\App\Controller\Admin\Config\Config::class, "index"], "GET");
Route::add("/admin/config/get", [\App\Controller\Admin\API\Config\Config::class, "get"], "POST");
Route::add("/admin/config/save", [\App\Controller\Admin\API\Config\Config::class, "save"], "POST");
Route::add("/admin/config/sms/test", [\App\Controller\Admin\API\Config\Config::class, "smsTest"], "POST");
Route::add("/admin/config/smtp/test", [\App\Controller\Admin\API\Config\Config::class, "smtpTest"], "POST");

# 国际化管理
Route::add("/admin/config/language", [\App\Controller\Admin\Config\Config::class, "language"], "GET");
Route::add("/admin/config/language/get", [\App\Controller\Admin\API\Config\Language::class, "get"], "POST");
Route::add("/admin/config/language/save", [\App\Controller\Admin\API\Config\Language::class, "save"], "POST");
Route::add("/admin/config/language/del", [\App\Controller\Admin\API\Config\Language::class, "del"], "POST");


//仓库-----------------START---------------------
# 分类
Route::add("/admin/repertory/category", [\App\Controller\Admin\Repertory\Category::class, "index"], "GET");
Route::add("/admin/repertory/category/get", [\App\Controller\Admin\API\Repertory\Category::class, "get"], "POST");
Route::add("/admin/repertory/category/save", [\App\Controller\Admin\API\Repertory\Category::class, "save"], "POST");
Route::add("/admin/repertory/category/del", [\App\Controller\Admin\API\Repertory\Category::class, "del"], "POST");
# 商品管理
Route::add("/admin/repertory/item", [\App\Controller\Admin\Repertory\Item::class, "index"], "GET");
Route::add("/admin/repertory/item/get", [\App\Controller\Admin\API\Repertory\Item::class, "get"], "POST");
Route::add("/admin/repertory/item/save", [\App\Controller\Admin\API\Repertory\Item::class, "save"], "POST");
Route::add("/admin/repertory/item/del", [\App\Controller\Admin\API\Repertory\Item::class, "del"], "POST");
Route::add("/admin/repertory/item/transferShop", [\App\Controller\Admin\API\Repertory\Item::class, "transferShop"], "POST");
Route::add("/admin/repertory/item/updateStatus", [\App\Controller\Admin\API\Repertory\Item::class, "updateStatus"], "POST");
# SKU管理
Route::add("/admin/repertory/item/sku/get", [\App\Controller\Admin\API\Repertory\ItemSku::class, "get"], "POST");
Route::add("/admin/repertory/item/sku/save", [\App\Controller\Admin\API\Repertory\ItemSku::class, "save"], "POST");
Route::add("/admin/repertory/item/sku/del", [\App\Controller\Admin\API\Repertory\ItemSku::class, "del"], "POST");
# 用户组定价
Route::add("/admin/repertory/item/sku/group/get", [\App\Controller\Admin\API\Repertory\ItemSkuGroup::class, "get"], "POST");
Route::add("/admin/repertory/item/sku/group/save", [\App\Controller\Admin\API\Repertory\ItemSkuGroup::class, "save"], "POST");
# 会员单独定价
Route::add("/admin/repertory/item/sku/user/get", [\App\Controller\Admin\API\Repertory\ItemSkuUser::class, "get"], "POST");
Route::add("/admin/repertory/item/sku/user/save", [\App\Controller\Admin\API\Repertory\ItemSkuUser::class, "save"], "POST");
# 批发设置
Route::add("/admin/repertory/item/sku/wholesale/get", [\App\Controller\Admin\API\Repertory\ItemSkuWholesale::class, "get"], "POST");
Route::add("/admin/repertory/item/sku/wholesale/save", [\App\Controller\Admin\API\Repertory\ItemSkuWholesale::class, "save"], "POST");
Route::add("/admin/repertory/item/sku/wholesale/del", [\App\Controller\Admin\API\Repertory\ItemSkuWholesale::class, "del"], "POST");
# 批发设置-用户组
Route::add("/admin/repertory/item/sku/wholesale/group/get", [\App\Controller\Admin\API\Repertory\ItemSkuWholesaleGroup::class, "get"], "POST");
Route::add("/admin/repertory/item/sku/wholesale/group/save", [\App\Controller\Admin\API\Repertory\ItemSkuWholesaleGroup::class, "save"], "POST");
# 批发设置-会员
Route::add("/admin/repertory/item/sku/wholesale/user/get", [\App\Controller\Admin\API\Repertory\ItemSkuWholesaleUser::class, "get"], "POST");
Route::add("/admin/repertory/item/sku/wholesale/user/save", [\App\Controller\Admin\API\Repertory\ItemSkuWholesaleUser::class, "save"], "POST");
# 同步模版
Route::add("/admin/repertory/item/markup", [\App\Controller\Admin\Repertory\ItemMarkupTemplate::class, "index"], "GET");
Route::add("/admin/repertory/item/markup/get", [\App\Controller\Admin\API\Repertory\ItemMarkupTemplate::class, "get"], "POST");
Route::add("/admin/repertory/item/markup/save", [\App\Controller\Admin\API\Repertory\ItemMarkupTemplate::class, "save"], "POST");
Route::add("/admin/repertory/item/markup/del", [\App\Controller\Admin\API\Repertory\ItemMarkupTemplate::class, "del"], "POST");
//仓库-----------------END---------------------

//进货订单-----------------START---------------------
Route::add("/admin/repertory/order", [\App\Controller\Admin\Repertory\Order::class, "index"], "GET");
Route::add("/admin/repertory/order/get", [\App\Controller\Admin\API\Repertory\Order::class, "get"], "POST");
Route::add("/admin/repertory/order/detail", [\App\Controller\Admin\API\Repertory\Order::class, "detail"], "POST");
//进货订单-----------------END---------------------


//插件系统-----------------START---------------------
Route::add("/admin/plugin/submit/js", [\App\Controller\Admin\API\Plugin\Submit::class, "js"], "POST");


//商城-----------------START---------------------
# 分类
Route::add("/admin/shop/category", [\App\Controller\Admin\Shop\Category::class, "index"], "GET");
Route::add("/admin/shop/category/get", [\App\Controller\Admin\API\Shop\Category::class, "get"], "POST");
Route::add("/admin/shop/category/save", [\App\Controller\Admin\API\Shop\Category::class, "save"], "POST");
Route::add("/admin/shop/category/del", [\App\Controller\Admin\API\Shop\Category::class, "del"], "POST");
# 商品管理
Route::add("/admin/shop/item", [\App\Controller\Admin\Shop\Item::class, "index"], "GET");
Route::add("/admin/shop/item/get", [\App\Controller\Admin\API\Shop\Item::class, "get"], "POST");
Route::add("/admin/shop/item/save", [\App\Controller\Admin\API\Shop\Item::class, "save"], "POST");
Route::add("/admin/shop/item/del", [\App\Controller\Admin\API\Shop\Item::class, "del"], "POST");
# SKU
Route::add("/admin/shop/item/sku/get", [\App\Controller\Admin\API\Shop\ItemSku::class, "get"], "POST");
Route::add("/admin/shop/item/sku/save", [\App\Controller\Admin\API\Shop\ItemSku::class, "save"], "POST");
Route::add("/admin/shop/item/sku/level/get", [\App\Controller\Admin\API\Shop\ItemSkuLevel::class, "get"], "POST");
Route::add("/admin/shop/item/sku/level/save", [\App\Controller\Admin\API\Shop\ItemSkuLevel::class, "save"], "POST");
Route::add("/admin/shop/item/sku/user/get", [\App\Controller\Admin\API\Shop\ItemSkuUser::class, "get"], "POST");
Route::add("/admin/shop/item/sku/user/save", [\App\Controller\Admin\API\Shop\ItemSkuUser::class, "save"], "POST");
# 批发设置
Route::add("/admin/shop/item/sku/wholesale/get", [\App\Controller\Admin\API\Shop\ItemSkuWholesale::class, "get"], "POST");
Route::add("/admin/shop/item/sku/wholesale/save", [\App\Controller\Admin\API\Shop\ItemSkuWholesale::class, "save"], "POST");
Route::add("/admin/shop/item/sku/wholesale/level/get", [\App\Controller\Admin\API\Shop\ItemSkuWholesaleLevel::class, "get"], "POST");
Route::add("/admin/shop/item/sku/wholesale/level/save", [\App\Controller\Admin\API\Shop\ItemSkuWholesaleLevel::class, "save"], "POST");
Route::add("/admin/shop/item/sku/wholesale/user/get", [\App\Controller\Admin\API\Shop\ItemSkuWholesaleUser::class, "get"], "POST");
Route::add("/admin/shop/item/sku/wholesale/user/save", [\App\Controller\Admin\API\Shop\ItemSkuWholesaleUser::class, "save"], "POST");

# 定价模板
Route::add("/admin/shop/item/markup", [\App\Controller\Admin\Shop\ItemMarkupTemplate::class, "index"], "GET");
Route::add("/admin/shop/item/markup/get", [\App\Controller\Admin\API\Shop\ItemMarkupTemplate::class, "get"], "POST");
Route::add("/admin/shop/item/markup/save", [\App\Controller\Admin\API\Shop\ItemMarkupTemplate::class, "save"], "POST");
Route::add("/admin/shop/item/markup/del", [\App\Controller\Admin\API\Shop\ItemMarkupTemplate::class, "del"], "POST");
#订单管理
Route::add("/admin/shop/order", [\App\Controller\Admin\Shop\Order::class, "index"], "GET");
Route::add("/admin/shop/order/get", [\App\Controller\Admin\API\Shop\Order::class, "get"], "POST");
Route::add("/admin/shop/order/items", [\App\Controller\Admin\API\Shop\Order::class, "items"], "POST");
Route::add("/admin/shop/order/download", [\App\Controller\Admin\API\Shop\Order::class, "download"], "GET");
Route::add("/admin/shop/order/item", [\App\Controller\Admin\Shop\Order::class, "item"], "GET");
Route::add("/admin/shop/order/item", [\App\Controller\Admin\API\Shop\Order::class, "item"], "POST");
Route::add("/admin/shop/order/item/get", [\App\Controller\Admin\API\Shop\OrderItem::class, "get"], "POST");

#订单汇总
Route::add("/admin/shop/order/summary", [\App\Controller\Admin\Shop\Order::class, "summary"], "GET");
Route::add("/admin/shop/order/summary/get", [\App\Controller\Admin\API\Shop\OrderSummary::class, "get"], "POST");

#维权订单
Route::add("/admin/shop/report/order", [\App\Controller\Admin\Shop\OrderReport::class, "index"], "GET");
Route::add("/admin/shop/report/order/get", [\App\Controller\Admin\API\Shop\OrderReport::class, "get"], "POST");
Route::add("/admin/shop/report/order/message", [\App\Controller\Admin\API\Shop\OrderReport::class, "message"], "POST");
Route::add("/admin/shop/report/order/handle", [\App\Controller\Admin\API\Shop\OrderReport::class, "handle"], "POST");
Route::add("/admin/shop/report/order/heartbeat", [\App\Controller\Admin\API\Shop\OrderReport::class, "heartbeat"], "POST");
Route::add("/admin/shop/report/order/finish", [\App\Controller\Admin\API\Shop\OrderReport::class, "finish"], "POST");


//SHOP-----------------END---------------------

//系统相关-----------------START---------------------
Route::add("/admin/system/restart", [\App\Controller\Admin\API\System\App::class, "restart"], "POST");
Route::add("/admin/system/state", [\App\Controller\Admin\API\System\App::class, "state"], "POST");
//系统相关-----------------END---------------------


//应用商店-----------------START---------------------
Route::add("/admin/store/node/ping", [\App\Controller\Admin\API\Store\Node::class, "ping"], "POST");
Route::add("/admin/store/node/save", [\App\Controller\Admin\API\Store\Node::class, "save"], "POST");

Route::add("/admin/store/notice", [\App\Controller\Admin\API\Store\Notice::class, "list"], "POST");
Route::add("/admin/version/latest", [\App\Controller\Admin\API\Store\Version::class, "latest"], "POST");
Route::add("/admin/version/list", [\App\Controller\Admin\API\Store\Version::class, "list"], "POST");
Route::add("/admin/version/update", [\App\Controller\Admin\API\Store\Version::class, "update"], "POST");
Route::add("/admin/version/updateLog", [\App\Controller\Admin\API\Store\Version::class, "getUpdateLog"], "POST");
#应用商店主页
Route::add("/admin/store", [\App\Controller\Admin\Store\Store::class, "index"], "GET");
Route::add("/admin/store/list", [\App\Controller\Admin\API\Store\Store::class, "list"], "POST");
Route::add("/admin/store/purchase", [\App\Controller\Admin\API\Store\Store::class, "purchase"], "POST");
Route::add("/admin/store/recharge", [\App\Controller\Admin\API\Store\Store::class, "recharge"], "POST");
Route::add("/admin/store/powers", [\App\Controller\Admin\API\Store\Store::class, "powers"], "POST");
Route::add("/admin/store/power/detail", [\App\Controller\Admin\API\Store\Store::class, "powerDetail"], "POST");
Route::add("/admin/store/power/renewal", [\App\Controller\Admin\API\Store\Store::class, "powerRenewal"], "POST");
Route::add("/admin/store/power/renewal/bind", [\App\Controller\Admin\API\Store\Store::class, "powerBind"], "POST");
Route::add("/admin/store/power/renewal/auto", [\App\Controller\Admin\API\Store\Store::class, "openPowerAutoRenewal"], "POST");
Route::add("/admin/store/power/sub/free", [\App\Controller\Admin\API\Store\Store::class, "openSubFree"], "POST");
Route::add("/admin/store/power/sub/list", [\App\Controller\Admin\API\Store\Store::class, "subPowers"], "POST");
Route::add("/admin/store/power/sub/auth", [\App\Controller\Admin\API\Store\Store::class, "setSubPower"], "POST");
Route::add("/admin/store/group", [\App\Controller\Admin\API\Store\Store::class, "group"], "POST");
Route::add("/admin/store/install", [\App\Controller\Admin\API\Store\Store::class, "install"], "POST");
Route::add("/admin/store/uninstall", [\App\Controller\Admin\API\Store\Store::class, "uninstall"], "POST");
Route::add("/admin/store/plugin/versions", [\App\Controller\Admin\API\Store\Store::class, "getPluginVersions"], "POST");
Route::add("/admin/store/plugin/version/list", [\App\Controller\Admin\API\Store\Store::class, "getPluginVersionList"], "POST");
Route::add("/admin/store/plugin/version/update", [\App\Controller\Admin\API\Store\Store::class, "pluginUpdate"], "POST");
#应用商店支付
Route::add("/admin/store/pay/list", [\App\Controller\Admin\API\Store\Pay::class, "getList"], "POST");
Route::add("/admin/store/pay/order", [\App\Controller\Admin\API\Store\Pay::class, "getPayOrder"], "POST");

#开发者中心
Route::add("/admin/store/developer", [\App\Controller\Admin\Store\Store::class, "developer"], "GET");
Route::add("/admin/store/developer/plugin/save", [\App\Controller\Admin\API\Store\Developer::class, "createOrUpdatePlugin"], "POST"); //原：/admin/store/plugin/save@POST
Route::add("/admin/store/developer/plugin/list", [\App\Controller\Admin\API\Store\Developer::class, "pluginList"], "POST");
Route::add("/admin/store/developer/plugin/publish", [\App\Controller\Admin\API\Store\Developer::class, "publishPlugin"], "POST");
Route::add("/admin/store/developer/plugin/tracked", [\App\Controller\Admin\API\Store\Developer::class, "getPluginTrackedFiles"], "POST");
Route::add("/admin/store/developer/plugin/update", [\App\Controller\Admin\API\Store\Developer::class, "updatePlugin"], "POST");
Route::add("/admin/store/developer/plugin/version/list", [\App\Controller\Admin\API\Store\Developer::class, "getPluginVersionList"], "POST");
Route::add("/admin/store/developer/plugin/authorization/list", [\App\Controller\Admin\API\Store\Developer::class, "getPluginAuthorizationList"], "POST");
Route::add("/admin/store/developer/plugin/authorization/add", [\App\Controller\Admin\API\Store\Developer::class, "addPluginAuthorization"], "POST");
Route::add("/admin/store/developer/plugin/authorization/remove", [\App\Controller\Admin\API\Store\Developer::class, "removePluginAuthorization"], "POST");

#盈利中心
Route::add("/admin/store/trade", [\App\Controller\Admin\Store\Store::class, "trade"], "GET");
Route::add("/admin/store/withdrawal/get", [\App\Controller\Admin\API\Store\Withdrawal::class, "get"], "POST");
Route::add("/admin/store/withdrawal/apply", [\App\Controller\Admin\API\Store\Withdrawal::class, "apply"], "POST");
Route::add("/admin/store/bill/get", [\App\Controller\Admin\API\Store\Bill::class, "get"], "POST");

#个人资料
Route::add("/admin/store/personal/info", [\App\Controller\Admin\API\Store\Personal::class, "info"], "POST");

#实名认证
Route::add("/admin/store/identity/status", [\App\Controller\Admin\API\Store\Identity::class, "status"], "POST");
Route::add("/admin/store/identity/certification", [\App\Controller\Admin\API\Store\Identity::class, "certification"], "POST");


#登录/注册/找回密码
Route::add("/admin/store/auth/captcha", [\App\Controller\Admin\API\Store\Auth::class, "captcha"], "GET");
Route::add("/admin/store/auth/login", [\App\Controller\Admin\API\Store\Auth::class, "login"], "POST");
Route::add("/admin/store/auth/register", [\App\Controller\Admin\API\Store\Auth::class, "register"], "POST");
Route::add("/admin/store/auth/reset", [\App\Controller\Admin\API\Store\Auth::class, "reset"], "POST");
Route::add("/admin/store/auth/sms/send", [\App\Controller\Admin\API\Store\Auth::class, "sendSms"], "POST");


//应用商店-----------------END---------------------

//插件系统-----------------START---------------------
Route::add("/admin/plugin", [\App\Controller\Admin\Plugin\Plugin::class, "index"], "GET");
Route::add("/admin/plugin/get", [\App\Controller\Admin\API\Plugin\Plugin::class, "get"], "POST");
Route::add("/admin/plugin/start", [\App\Controller\Admin\API\Plugin\Plugin::class, "start"], "POST");
Route::add("/admin/plugin/restart", [\App\Controller\Admin\API\Plugin\Plugin::class, "restart"], "POST");
Route::add("/admin/plugin/stop", [\App\Controller\Admin\API\Plugin\Plugin::class, "stop"], "POST");
Route::add("/admin/plugin/getLogs", [\App\Controller\Admin\API\Plugin\Plugin::class, "getLogs"], "POST");
Route::add("/admin/plugin/clearLog", [\App\Controller\Admin\API\Plugin\Plugin::class, "clearLog"], "POST");
Route::add("/admin/plugin/setCfg", [\App\Controller\Admin\API\Plugin\Plugin::class, "setCfg"], "POST");
Route::add("/admin/plugin/setSysCfg", [\App\Controller\Admin\API\Plugin\Plugin::class, "setSysCfg"], "POST");
Route::add("/admin/plugin/config/get", [\App\Controller\Admin\API\Plugin\Config::class, "get"], "POST");
Route::add("/admin/plugin/config/save", [\App\Controller\Admin\API\Plugin\Config::class, "save"], "POST");
Route::add("/admin/plugin/config/del", [\App\Controller\Admin\API\Plugin\Config::class, "del"], "POST");
Route::add("/admin/plugin/ship/items", [\App\Controller\Admin\API\Plugin\Ship::class, "items"], "POST");
Route::add("/admin/plugin/ship/import", [\App\Controller\Admin\API\Plugin\Ship::class, "import"], "POST");
Route::add("/admin/plugin/ship/remote/items", [\App\Controller\Admin\API\Plugin\Ship::class, "getSyncRemoteItems"], "POST");
Route::add("/admin/plugin/ship/remote/sync", [\App\Controller\Admin\API\Plugin\Ship::class, "syncRemoteItem"], "POST");

Route::add("/admin/plugin/wiki", [\App\Controller\Admin\Plugin\Plugin::class, "wiki"], "GET");
Route::add("/admin/plugin/icon", [\App\Controller\Admin\API\Plugin\Plugin::class, "icon"], "GET");
//插件系统-----------------END---------------------


//支付接口-----------------START---------------------
Route::add("/admin/pay", [\App\Controller\Admin\Pay\Pay::class, "index"], "GET");
Route::add("/admin/pay/get", [\App\Controller\Admin\API\Pay\Pay::class, "get"], "POST");
Route::add("/admin/pay/save", [\App\Controller\Admin\API\Pay\Pay::class, "save"], "POST");
Route::add("/admin/pay/del", [\App\Controller\Admin\API\Pay\Pay::class, "del"], "POST");
//Route::add("/admin/pay/config", [\App\Controller\Admin\API\Pay\Pay::class, "config"], "POST");  --- 废弃
Route::add("/admin/pay/code", [\App\Controller\Admin\API\Pay\Pay::class, "code"], "POST");
Route::add("/admin/pay/order", [\App\Controller\Admin\Pay\Pay::class, "order"], "GET");
Route::add("/admin/pay/order/get", [\App\Controller\Admin\API\Pay\Order::class, "get"], "POST");
Route::add("/admin/pay/order/close", [\App\Controller\Admin\API\Pay\Order::class, "close"], "POST");
Route::add("/admin/pay/order/status", [\App\Controller\Admin\API\Pay\Order::class, "status"], "POST");
Route::add("/admin/pay/order/successful", [\App\Controller\Admin\API\Pay\Order::class, "successful"], "POST");
Route::add("/admin/pay/order/getLatestOrderId", [\App\Controller\Admin\API\Pay\Order::class, "getLatestOrderId"], "POST");
Route::add("/admin/pay/group/get", [\App\Controller\Admin\API\Pay\PayGroup::class, "get"], "POST");
Route::add("/admin/pay/group/save", [\App\Controller\Admin\API\Pay\PayGroup::class, "save"], "POST");
Route::add("/admin/pay/user/get", [\App\Controller\Admin\API\Pay\PayUser::class, "get"], "POST");
Route::add("/admin/pay/user/save", [\App\Controller\Admin\API\Pay\PayUser::class, "save"], "POST");
//支付接口-----------------END---------------------

//会员相关-----------------START---------------------

Route::add("/admin/user", [\App\Controller\Admin\User\User::class, "index"], "GET");
Route::add("/admin/user/get", [\App\Controller\Admin\API\User\User::class, "get"], "POST");
Route::add("/admin/user/save", [\App\Controller\Admin\API\User\User::class, "save"], "POST");
Route::add("/admin/user/del", [\App\Controller\Admin\API\User\User::class, "del"], "POST");
Route::add("/admin/user/balanceChange", [\App\Controller\Admin\API\User\User::class, "balanceChange"], "POST");

#站点管理
Route::add("/admin/site", [\App\Controller\Admin\User\User::class, "site"], "GET");
Route::add("/admin/site/get", [\App\Controller\Admin\API\User\Site::class, "get"], "POST");
Route::add("/admin/site/dnsRecord", [\App\Controller\Admin\API\User\Site::class, "getDnsRecord"], "POST");
Route::add("/admin/site/save", [\App\Controller\Admin\API\User\Site::class, "save"], "POST");
Route::add("/admin/site/del", [\App\Controller\Admin\API\User\Site::class, "del"], "POST");
Route::add("/admin/site/certificate/get", [\App\Controller\Admin\API\User\Site::class, "getCertificate"], "POST");
Route::add("/admin/site/certificate/modify", [\App\Controller\Admin\API\User\Site::class, "modifyCertificate"], "POST");

#会员等级
Route::add("/admin/user/level", [\App\Controller\Admin\User\User::class, "level"], "GET");
Route::add("/admin/user/level/get", [\App\Controller\Admin\API\User\Level::class, "get"], "POST");
Route::add("/admin/user/level/save", [\App\Controller\Admin\API\User\Level::class, "save"], "POST");
Route::add("/admin/user/level/del", [\App\Controller\Admin\API\User\Level::class, "del"], "POST");

#权限组
Route::add("/admin/user/group", [\App\Controller\Admin\User\User::class, "group"], "GET");
Route::add("/admin/user/group/get", [\App\Controller\Admin\API\User\Group::class, "get"], "POST");
Route::add("/admin/user/group/save", [\App\Controller\Admin\API\User\Group::class, "save"], "POST");
Route::add("/admin/user/group/del", [\App\Controller\Admin\API\User\Group::class, "del"], "POST");


#账单列表
Route::add("/admin/user/bill", [\App\Controller\Admin\User\User::class, "bill"], "GET");
Route::add("/admin/user/bill/get", [\App\Controller\Admin\API\User\Bill::class, "get"], "POST");

#实名管理
Route::add("/admin/user/identity", [\App\Controller\Admin\User\User::class, "identity"], "GET");
Route::add("/admin/user/identity/get", [\App\Controller\Admin\API\User\Identity::class, "get"], "POST");
Route::add("/admin/user/identity/save", [\App\Controller\Admin\API\User\Identity::class, "save"], "POST");
Route::add("/admin/user/identity/del", [\App\Controller\Admin\API\User\Identity::class, "del"], "POST");

#银行管理
Route::add("/admin/user/bank", [\App\Controller\Admin\User\Bank::class, "index"], "GET");
Route::add("/admin/user/bank/get", [\App\Controller\Admin\API\User\Bank::class, "get"], "POST");
Route::add("/admin/user/bank/save", [\App\Controller\Admin\API\User\Bank::class, "save"], "POST");
Route::add("/admin/user/bank/del", [\App\Controller\Admin\API\User\Bank::class, "del"], "POST");

#银行卡管理
Route::add("/admin/user/bank/card", [\App\Controller\Admin\User\BankCard::class, "index"], "GET");
Route::add("/admin/user/bank/card/get", [\App\Controller\Admin\API\User\BankCard::class, "get"], "POST");
Route::add("/admin/user/bank/card/abnormality", [\App\Controller\Admin\API\User\BankCard::class, "abnormality"], "POST");
Route::add("/admin/user/bank/card/del", [\App\Controller\Admin\API\User\BankCard::class, "del"], "POST");

#提现管理
Route::add("/admin/user/withdraw", [\App\Controller\Admin\User\User::class, "withdraw"], "GET");
Route::add("/admin/user/withdraw/get", [\App\Controller\Admin\API\User\Withdraw::class, "get"], "POST");
Route::add("/admin/user/withdraw/processed", [\App\Controller\Admin\API\User\Withdraw::class, "processed"], "POST");


//会员相关-----------------END---------------------


# 前台
Route::add("/", [\App\Controller\User\Index::class, "index"], "GET");
Route::add("/item", [\App\Controller\User\Index::class, "item"], "GET");
Route::add("/checkout", [\App\Controller\User\Index::class, "checkout"], "GET");  //结账


# 注册账号
Route::add("/register/terms", [\App\Controller\User\Auth::class, "terms"], "GET");
Route::add("/register", [\App\Controller\User\Auth::class, "register"], "GET");
Route::add("/register", [\App\Controller\User\API\Auth::class, "register"], "POST");
Route::add("/sendEmail", [\App\Controller\User\API\Auth::class, "sendEmail"], "POST");

Route::add("/login", [\App\Controller\User\Auth::class, "login"], "GET");
Route::add("/login", [\App\Controller\User\API\Auth::class, "login"], "POST");

Route::add("/reset", [\App\Controller\User\Auth::class, "reset"], "GET");
Route::add("/reset", [\App\Controller\User\API\Auth::class, "reset"], "POST");

//TODO 这个路由后面要重做
Route::add("/user/dashboard", [\App\Controller\User\Dashboard::class, "index"], "GET");


//获取分类
Route::add("/shop/category", [\App\Controller\User\API\Index\Category::class, "available"], "POST");//商品分类
Route::add("/shop/item", [\App\Controller\User\API\Index\Item::class, "list"], "POST");//商品列表
Route::add("/shop/item/detail", [\App\Controller\User\API\Index\Item::class, "detail"], "POST");//商品详细
Route::add("/shop/item/price", [\App\Controller\User\API\Index\Item::class, "getPrice"], "POST");//订单金额

//下单
Route::add("/shop/order/trade", [\App\Controller\User\API\Index\Order::class, "trade"], "POST"); //批量下单
Route::add("/shop/order/getOrder", [\App\Controller\User\API\Index\Order::class, "getOrder"], "POST"); //获取订单讯息
Route::add("/shop/order/cancel", [\App\Controller\User\API\Index\Order::class, "cancel"], "POST"); //取消订单
Route::add("/shop/order/download", [\App\Controller\User\API\Index\Order::class, "downloadOrder"], "GET"); //导出订单


//购物车
Route::add("/shop/cart", [\App\Controller\User\Index::class, "cart"], "GET");
Route::add("/shop/cart/items", [\App\Controller\User\API\Index\Cart::class, "items"], "POST");
Route::add("/shop/cart/getAmount", [\App\Controller\User\API\Index\Cart::class, "getAmount"], "POST");
Route::add("/shop/cart/add", [\App\Controller\User\API\Index\Cart::class, "add"], "POST");
Route::add("/shop/cart/changeQuantity", [\App\Controller\User\API\Index\Cart::class, "changeQuantity"], "POST");
Route::add("/shop/cart/updateOption", [\App\Controller\User\API\Index\Cart::class, "updateOption"], "POST");
Route::add("/shop/cart/getItem", [\App\Controller\User\API\Index\Cart::class, "getItem"], "POST");
Route::add("/shop/cart/delItem", [\App\Controller\User\API\Index\Cart::class, "delItem"], "POST");
Route::add("/shop/cart/clear", [\App\Controller\User\API\Index\Cart::class, "clear"], "POST");

//支付
Route::add("/pay", [\App\Controller\User\Pay\PayOrder::class, "pay"], "GET");  //支付页面
Route::add("/pay", [\App\Controller\User\API\Pay\PayOrder::class, "pay"], "POST");  //发起支付请求
Route::add("/pay/async", [\App\Controller\User\API\Pay\PayOrder::class, "async"], "ALL"); //异步地址
Route::add("/pay/sync", [\App\Controller\User\Pay\PayOrder::class, "sync"], "GET");  //同步地址
//Route::add("/pay/list", [\App\Controller\User\API\User\Pay::class, "list"], "POST"); // 通用支付
Route::add("/pay/list", [\App\Controller\User\API\Pay\Pay::class, "getList"], "POST");//通用支付
Route::add("/pay/getOrder", [\App\Controller\User\API\Pay\PayOrder::class, "getPayOrder"], "POST");//获取支付订单信息

//会员中心-我的店铺
Route::add("/user/shop/category", [\App\Controller\User\Shop\Category::class, "index"], "GET");
Route::add("/user/shop/category/get", [\App\Controller\User\API\Shop\Category::class, "get"], "POST");
Route::add("/user/shop/category/save", [\App\Controller\User\API\Shop\Category::class, "save"], "POST");
Route::add("/user/shop/category/del", [\App\Controller\User\API\Shop\Category::class, "del"], "POST");
Route::add("/user/shop/supply", [\App\Controller\User\Shop\Supply::class, "index"], "GET");
Route::add("/user/shop/supply/get", [\App\Controller\User\API\Shop\Supply::class, "get"], "POST");
Route::add("/user/shop/supply/item", [\App\Controller\User\API\Shop\Supply::class, "item"], "POST");
Route::add("/user/shop/supply/trade", [\App\Controller\User\API\Shop\Supply::class, "trade"], "POST"); //进货
Route::add("/user/shop/supply/dock", [\App\Controller\User\API\Shop\Supply::class, "dock"], "POST");
# 商品管理
Route::add("/user/shop/item", [\App\Controller\User\Shop\Item::class, "index"], "GET");
Route::add("/user/shop/item/get", [\App\Controller\User\API\Shop\Item::class, "get"], "POST");
Route::add("/user/shop/item/save", [\App\Controller\User\API\Shop\Item::class, "save"], "POST");
Route::add("/user/shop/item/del", [\App\Controller\User\API\Shop\Item::class, "del"], "POST");
# SKU
Route::add("/user/shop/item/sku/get", [\App\Controller\User\API\Shop\ItemSku::class, "get"], "POST");
Route::add("/user/shop/item/sku/save", [\App\Controller\User\API\Shop\ItemSku::class, "save"], "POST");
Route::add("/user/shop/item/sku/level/get", [\App\Controller\User\API\Shop\ItemSkuLevel::class, "get"], "POST");
Route::add("/user/shop/item/sku/level/save", [\App\Controller\User\API\Shop\ItemSkuLevel::class, "save"], "POST");
Route::add("/user/shop/item/sku/user/get", [\App\Controller\User\API\Shop\ItemSkuUser::class, "get"], "POST");
Route::add("/user/shop/item/sku/user/save", [\App\Controller\User\API\Shop\ItemSkuUser::class, "save"], "POST");
# 批发设置
Route::add("/user/shop/item/sku/wholesale/get", [\App\Controller\User\API\Shop\ItemSkuWholesale::class, "get"], "POST");
Route::add("/user/shop/item/sku/wholesale/save", [\App\Controller\User\API\Shop\ItemSkuWholesale::class, "save"], "POST");
Route::add("/user/shop/item/sku/wholesale/level/get", [\App\Controller\User\API\Shop\ItemSkuWholesaleLevel::class, "get"], "POST");
Route::add("/user/shop/item/sku/wholesale/level/save", [\App\Controller\User\API\Shop\ItemSkuWholesaleLevel::class, "save"], "POST");
Route::add("/user/shop/item/sku/wholesale/user/get", [\App\Controller\User\API\Shop\ItemSkuWholesaleUser::class, "get"], "POST");
Route::add("/user/shop/item/sku/wholesale/user/save", [\App\Controller\User\API\Shop\ItemSkuWholesaleUser::class, "save"], "POST");
# 同步模板
Route::add("/user/shop/item/markup", [\App\Controller\User\Shop\ItemMarkupTemplate::class, "index"], "GET");
Route::add("/user/shop/item/markup/get", [\App\Controller\User\API\Shop\ItemMarkupTemplate::class, "get"], "POST");
Route::add("/user/shop/item/markup/save", [\App\Controller\User\API\Shop\ItemMarkupTemplate::class, "save"], "POST");
Route::add("/user/shop/item/markup/del", [\App\Controller\User\API\Shop\ItemMarkupTemplate::class, "del"], "POST");
#订单管理
Route::add("/user/shop/order", [\App\Controller\User\Shop\Order::class, "index"], "GET");
Route::add("/user/shop/order/get", [\App\Controller\User\API\Shop\Order::class, "get"], "POST");
Route::add("/user/shop/order/items", [\App\Controller\User\API\Shop\Order::class, "items"], "POST");
Route::add("/user/shop/order/download", [\App\Controller\User\API\Shop\Order::class, "download"], "GET");
Route::add("/user/shop/order/item", [\App\Controller\User\API\Shop\Order::class, "item"], "POST");
#订单汇总
Route::add("/user/shop/order/summary", [\App\Controller\User\Shop\Order::class, "summary"], "GET");
Route::add("/user/shop/order/summary/get", [\App\Controller\User\API\Shop\OrderSummary::class, "get"], "POST");

//系统设置
Route::add("/user/config", [\App\Controller\User\Config\Config::class, "index"], "GET");
Route::add("/user/config/get", [\App\Controller\User\API\Config\Config::class, "get"], "POST");
Route::add("/user/config/save", [\App\Controller\User\API\Config\Config::class, "save"], "POST");
Route::add("/user/config/sms/test", [\App\Controller\User\API\Config\Config::class, "smsTest"], "POST");
Route::add("/user/config/smtp/test", [\App\Controller\User\API\Config\Config::class, "smtpTest"], "POST");
Route::add("/user/config/site/get", [\App\Controller\User\API\Config\Site::class, "get"], "POST");
Route::add("/user/config/site/save", [\App\Controller\User\API\Config\Site::class, "save"], "POST");
Route::add("/user/config/site/del", [\App\Controller\User\API\Config\Site::class, "del"], "POST");
Route::add("/user/config/site/certificate/get", [\App\Controller\User\API\Config\Site::class, "getCertificate"], "POST");
Route::add("/user/config/site/certificate/modify", [\App\Controller\User\API\Config\Site::class, "modifyCertificate"], "POST");
Route::add("/user/config/site/getDnsRecord", [\App\Controller\User\API\Config\Site::class, "getDnsRecord"], "POST");

//插件系统-----------------START---------------------
Route::add("/user/plugin", [\App\Controller\User\Plugin\Plugin::class, "index"], "GET");
Route::add("/user/plugin/get", [\App\Controller\User\API\Plugin\Plugin::class, "get"], "POST");
Route::add("/user/plugin/start", [\App\Controller\User\API\Plugin\Plugin::class, "start"], "POST");
Route::add("/user/plugin/restart", [\App\Controller\User\API\Plugin\Plugin::class, "restart"], "POST");
Route::add("/user/plugin/stop", [\App\Controller\User\API\Plugin\Plugin::class, "stop"], "POST");
Route::add("/user/plugin/getLogs", [\App\Controller\User\API\Plugin\Plugin::class, "getLogs"], "POST");
Route::add("/user/plugin/clearLog", [\App\Controller\User\API\Plugin\Plugin::class, "clearLog"], "POST");
Route::add("/user/plugin/setCfg", [\App\Controller\User\API\Plugin\Plugin::class, "setCfg"], "POST");
Route::add("/user/plugin/setSysCfg", [\App\Controller\User\API\Plugin\Plugin::class, "setSysCfg"], "POST");
Route::add("/user/plugin/config/get", [\App\Controller\User\API\Plugin\Config::class, "get"], "POST");
Route::add("/user/plugin/config/save", [\App\Controller\User\API\Plugin\Config::class, "save"], "POST");
Route::add("/user/plugin/config/del", [\App\Controller\User\API\Plugin\Config::class, "del"], "POST");
Route::add("/user/plugin/wiki", [\App\Controller\User\Plugin\Plugin::class, "wiki"], "GET");
Route::add("/user/plugin/submit/js", [\App\Controller\User\API\Plugin\Submit::class, "js"], "POST");
Route::add("/user/plugin/icon", [\App\Controller\User\API\Plugin\Plugin::class, "icon"], "GET");
Route::add("/user/plugin/ship/items", [\App\Controller\User\API\Plugin\Ship::class, "items"], "POST");
Route::add("/user/plugin/ship/import", [\App\Controller\User\API\Plugin\Ship::class, "import"], "POST");
Route::add("/user/plugin/ship/remote/items", [\App\Controller\User\API\Plugin\Ship::class, "getSyncRemoteItems"], "POST");
Route::add("/user/plugin/ship/remote/sync", [\App\Controller\User\API\Plugin\Ship::class, "syncRemoteItem"], "POST");
//插件系统-----------------END---------------------


//支付接口-----------------START---------------------
Route::add("/user/pay", [\App\Controller\User\Pay\Pay::class, "index"], "GET");
Route::add("/user/pay/get", [\App\Controller\User\API\Pay\PayManager::class, "get"], "POST");
Route::add("/user/pay/save", [\App\Controller\User\API\Pay\PayManager::class, "save"], "POST");
Route::add("/user/pay/del", [\App\Controller\User\API\Pay\PayManager::class, "del"], "POST");
//Route::add("/user/pay/config", [\App\Controller\User\API\Pay\PayManager::class, "config"], "POST"); -- 废弃
Route::add("/user/pay/code", [\App\Controller\User\API\Pay\PayManager::class, "code"], "POST");
Route::add("/user/pay/order", [\App\Controller\User\Pay\Pay::class, "order"], "GET");
Route::add("/user/pay/order/get", [\App\Controller\User\API\Pay\Order::class, "get"], "POST");
Route::add("/user/pay/order/close", [\App\Controller\User\API\Pay\Order::class, "close"], "POST");
Route::add("/user/pay/order/status", [\App\Controller\User\API\Pay\Order::class, "status"], "POST");
Route::add("/user/pay/order/successful", [\App\Controller\User\API\Pay\Order::class, "successful"], "POST");
Route::add("/user/pay/order/getLatestOrderId", [\App\Controller\User\API\Pay\Order::class, "getLatestOrderId"], "POST");
//支付接口-----------------END---------------------

//会员相关
Route::add("/user/user", [\App\Controller\User\User\User::class, "index"], "GET");
Route::add("/user/user/get", [\App\Controller\User\API\User\User::class, "get"], "POST");
Route::add("/user/user/transfer", [\App\Controller\User\API\User\User::class, "transfer"], "POST");
Route::add("/user/user/changeLevel", [\App\Controller\User\API\User\User::class, "changeLevel"], "POST");
#会员等级
Route::add("/user/user/level", [\App\Controller\User\User\Level::class, "index"], "GET");
Route::add("/user/user/level/get", [\App\Controller\User\API\User\Level::class, "get"], "POST");
Route::add("/user/user/level/save", [\App\Controller\User\API\User\Level::class, "save"], "POST");
Route::add("/user/user/level/del", [\App\Controller\User\API\User\Level::class, "del"], "POST");

#钱包相关
Route::add("/user/recharge", [\App\Controller\User\User\Recharge::class, "index"], "GET");
Route::add("/user/recharge/trade", [\App\Controller\User\API\User\Recharge::class, "trade"], "POST");
Route::add("/user/transfer", [\App\Controller\User\User\Transfer::class, "index"], "GET");
Route::add("/user/transfer/to", [\App\Controller\User\API\User\Transfer::class, "to"], "POST");

#提现
Route::add("/user/withdraw", [\App\Controller\User\User\Withdraw::class, "index"], "GET");
Route::add("/user/withdraw/get", [\App\Controller\User\API\User\Withdraw::class, "get"], "POST");
Route::add("/user/withdraw/apply", [\App\Controller\User\API\User\Withdraw::class, "apply"], "POST");


#银行卡
Route::add("/user/bank/card", [\App\Controller\User\User\BankCard::class, "index"], "GET");
Route::add("/user/bank/card/get", [\App\Controller\User\API\User\BankCard::class, "get"], "POST");
Route::add("/user/bank/card/save", [\App\Controller\User\API\User\BankCard::class, "save"], "POST");
Route::add("/user/bank/card/del", [\App\Controller\User\API\User\BankCard::class, "del"], "POST");

//货源管理-----------------START---------------------
# 商品管理
Route::add("/user/repertory/item", [\App\Controller\User\Repertory\Item::class, "index"], "GET");
Route::add("/user/repertory/item/get", [\App\Controller\User\API\Repertory\Item::class, "get"], "POST");
Route::add("/user/repertory/item/save", [\App\Controller\User\API\Repertory\Item::class, "save"], "POST");
Route::add("/user/repertory/item/del", [\App\Controller\User\API\Repertory\Item::class, "del"], "POST");
Route::add("/user/repertory/item/updateStatus", [\App\Controller\User\API\Repertory\Item::class, "updateStatus"], "POST");
# SKU管理
Route::add("/user/repertory/item/sku/get", [\App\Controller\User\API\Repertory\ItemSku::class, "get"], "POST");
Route::add("/user/repertory/item/sku/save", [\App\Controller\User\API\Repertory\ItemSku::class, "save"], "POST");
Route::add("/user/repertory/item/sku/del", [\App\Controller\User\API\Repertory\ItemSku::class, "del"], "POST");
# 维权订单
Route::add("/user/repertory/report/order", [\App\Controller\User\Repertory\OrderReport::class, "index"], "GET");
Route::add("/user/repertory/report/order/get", [\App\Controller\User\API\Repertory\OrderReport::class, "get"], "POST");
Route::add("/user/repertory/report/order/message", [\App\Controller\User\API\Repertory\OrderReport::class, "message"], "POST");
Route::add("/user/repertory/report/order/handle", [\App\Controller\User\API\Repertory\OrderReport::class, "handle"], "POST");
Route::add("/user/repertory/report/order/heartbeat", [\App\Controller\User\API\Repertory\OrderReport::class, "heartbeat"], "POST");
# 进货订单
Route::add("/user/repertory/order", [\App\Controller\User\Repertory\Order::class, "index"], "GET");
Route::add("/user/repertory/order/get", [\App\Controller\User\API\Repertory\Order::class, "get"], "POST");
Route::add("/user/repertory/order/detail", [\App\Controller\User\API\Repertory\Order::class, "detail"], "POST");
# 同步模版
Route::add("/user/repertory/item/markup", [\App\Controller\User\Repertory\ItemMarkupTemplate::class, "index"], "GET");
Route::add("/user/repertory/item/markup/get", [\App\Controller\User\API\Repertory\ItemMarkupTemplate::class, "get"], "POST");
Route::add("/user/repertory/item/markup/save", [\App\Controller\User\API\Repertory\ItemMarkupTemplate::class, "save"], "POST");
Route::add("/user/repertory/item/markup/del", [\App\Controller\User\API\Repertory\ItemMarkupTemplate::class, "del"], "POST");
//货源管理-----------------END---------------------


//我的购物----------------START---------------------
Route::add("/user/trade/order", [\App\Controller\User\Trade\Order::class, "index"], "GET");
Route::add("/user/trade/order/get", [\App\Controller\User\API\Trade\Order::class, "get"], "POST");
Route::add("/user/trade/order/download", [\App\Controller\User\API\Trade\Order::class, "download"], "GET");
Route::add("/user/trade/order/receipt", [\App\Controller\User\API\Trade\Order::class, "receipt"], "POST"); //确认收货
Route::add("/user/trade/order/item", [\App\Controller\User\API\Trade\Order::class, "item"], "POST");

//消费维权
Route::add("/user/report/order", [\App\Controller\User\Report\Order::class, "index"], "GET");
Route::add("/user/report/order/apply", [\App\Controller\User\API\Report\Order::class, "apply"], "POST");
Route::add("/user/report/order/get", [\App\Controller\User\API\Report\Order::class, "get"], "POST");
Route::add("/user/report/order/reply", [\App\Controller\User\API\Report\Order::class, "reply"], "POST");
Route::add("/user/report/order/message", [\App\Controller\User\API\Report\Order::class, "message"], "POST");
Route::add("/user/report/order/heartbeat", [\App\Controller\User\API\Report\Order::class, "heartbeat"], "POST");
#账单列表
Route::add("/user/bill", [\App\Controller\User\User\Bill::class, "index"], "GET");
Route::add("/user/bill/get", [\App\Controller\User\API\User\Bill::class, "get"], "POST");
#个人资料
Route::add("/user/personal", [\App\Controller\User\User\Personal::class, "index"], "GET");
Route::add("/user/personal/info", [\App\Controller\User\API\User\Personal::class, "info"], "POST");
Route::add("/user/logout", [\App\Controller\User\User\Personal::class, "logout"], "GET");

Route::add("/user/security", [\App\Controller\User\User\Security::class, "index"], "GET");
Route::add("/user/security/general/edit", [\App\Controller\User\API\User\Security::class, "editGeneral"], "POST");
Route::add("/user/security/email/current/code", [\App\Controller\User\API\User\Security::class, "sendCurrentEmailCode"], "POST");
Route::add("/user/security/email/new/code", [\App\Controller\User\API\User\Security::class, "sendNewEmailCode"], "POST");
Route::add("/user/security/email/bind", [\App\Controller\User\API\User\Security::class, "bindNewEmail"], "POST");
Route::add("/user/security/password/edit", [\App\Controller\User\API\User\Security::class, "editPassword"], "POST");
Route::add("/user/security/identity", [\App\Controller\User\API\User\Security::class, "identity"], "POST");
Route::add("/user/security/identity/resubmit", [\App\Controller\User\API\User\Security::class, "resubmitIdentity"], "POST");
Route::add("/user/security/login/log", [\App\Controller\User\User\Security::class, "loginLog"], "GET");
Route::add("/user/security/login/log", [\App\Controller\User\API\User\Security::class, "loginLog"], "POST");


#推广返利
Route::add("/user/inviter", [\App\Controller\User\User\Inviter::class, "index"], "GET");

#开通商家
Route::add("/user/merchant/open", [\App\Controller\User\User\OpenMerchant::class, "index"], "GET");
Route::add("/user/merchant/open/trade", [\App\Controller\User\API\User\OpenMerchant::class, "trade"], "POST");

#我的会员等级
Route::add("/user/level", [\App\Controller\User\User\UpgradeLevel::class, "index"], "GET");
Route::add("/user/level/trade", [\App\Controller\User\API\User\UpgradeLevel::class, "trade"], "POST");


//我的购物----------------END---------------------

#应用商店主页
Route::add("/user/store", [\App\Controller\User\Store\Store::class, "index"], "GET");
Route::add("/user/store/list", [\App\Controller\User\API\Store\Store::class, "list"], "POST");
Route::add("/user/store/install", [\App\Controller\User\API\Store\Store::class, "install"], "POST");
Route::add("/user/store/uninstall", [\App\Controller\User\API\Store\Store::class, "uninstall"], "POST");
Route::add("/user/store/recharge", [\App\Controller\User\API\Store\Store::class, "recharge"], "POST");
Route::add("/user/store/purchase", [\App\Controller\User\API\Store\Store::class, "purchase"], "POST");
Route::add("/user/store/powers", [\App\Controller\User\API\Store\Store::class, "powers"], "POST");
Route::add("/user/store/power/detail", [\App\Controller\User\API\Store\Store::class, "powerDetail"], "POST");
Route::add("/user/store/power/renewal", [\App\Controller\User\API\Store\Store::class, "powerRenewal"], "POST");
Route::add("/user/store/power/renewal/bind", [\App\Controller\User\API\Store\Store::class, "powerBind"], "POST");
Route::add("/user/store/power/renewal/auto", [\App\Controller\User\API\Store\Store::class, "openPowerAutoRenewal"], "POST");
Route::add("/user/store/plugin/versions", [\App\Controller\User\API\Store\Store::class, "getPluginVersions"], "POST");
Route::add("/user/store/plugin/version/list", [\App\Controller\User\API\Store\Store::class, "getPluginVersionList"], "POST");
Route::add("/user/store/plugin/version/update", [\App\Controller\User\API\Store\Store::class, "pluginUpdate"], "POST");

Route::add("/user/store/pay/list", [\App\Controller\User\API\Store\Pay::class, "getList"], "POST");
Route::add("/user/store/pay/order", [\App\Controller\User\API\Store\Pay::class, "getPayOrder"], "POST");

#个人资料
Route::add("/user/store/personal/info", [\App\Controller\User\API\Store\Personal::class, "info"], "POST");

#登录/注册/找回密码
Route::add("/user/store/auth/captcha", [\App\Controller\User\API\Store\Auth::class, "captcha"], "GET");
Route::add("/user/store/auth/login", [\App\Controller\User\API\Store\Auth::class, "login"], "POST");
Route::add("/user/store/auth/register", [\App\Controller\User\API\Store\Auth::class, "register"], "POST");
Route::add("/user/store/auth/reset", [\App\Controller\User\API\Store\Auth::class, "reset"], "POST");
Route::add("/user/store/auth/sms/send", [\App\Controller\User\API\Store\Auth::class, "sendSms"], "POST");


//会员-数据字典
Route::add("/user/dict/shopCategory", [\App\Controller\User\API\Dict::class, "shopCategory"], "POST");
Route::add("/user/dict/theme", [\App\Controller\User\API\Dict::class, "getTheme"], "POST");
Route::add("/user/dict/repertoryCategory", [\App\Controller\User\API\Dict::class, "repertoryCategory"], "POST");
Route::add("/user/dict/itemMarkupTemplate", [\App\Controller\User\API\Dict::class, "itemMarkupTemplate"], "POST");
Route::add("/user/dict/repertoryItemMarkupTemplate", [\App\Controller\User\API\Dict::class, "repertoryItemMarkupTemplate"], "POST");
Route::add("/user/dict/customer", [\App\Controller\User\API\Dict::class, "customer"], "POST");
Route::add("/user/dict/pay", [\App\Controller\User\API\Dict::class, "pay"], "POST");
Route::add("/user/dict/masterPay", [\App\Controller\User\API\Dict::class, "masterPay"], "POST");
Route::add("/user/dict/payApi", [\App\Controller\User\API\Dict::class, "payApi"], "POST");
Route::add("/user/dict/ship", [\App\Controller\User\API\Dict::class, "ship"], "POST");
Route::add("/user/dict/repertoryPluginItem", [\App\Controller\User\API\Dict::class, "repertoryPluginItem"], "POST");
Route::add("/user/dict/repertoryItem", [\App\Controller\User\API\Dict::class, "repertoryItem"], "POST");
Route::add("/user/dict/repertoryItemSku", [\App\Controller\User\API\Dict::class, "repertoryItemSku"], "POST");
Route::add("/user/dict/level", [\App\Controller\User\API\Dict::class, "level"], "POST");
Route::add("/user/dict/bank", [\App\Controller\User\API\Dict::class, "bank"], "POST");
Route::add("/user/dict/item", [\App\Controller\User\API\Dict::class, "item"], "POST");
Route::add("/user/dict/itemSku", [\App\Controller\User\API\Dict::class, "itemSku"], "POST");
Route::add("/user/dict/pluginConfig", [\App\Controller\User\API\Dict::class, "pluginConfig"], "POST");


//会员-任务TASK相关API
Route::add("/user/task/autoReceipt", [\App\Controller\User\API\Task::class, "autoReceipt"], "POST");

//会员-上传API
Route::add("/user/upload", [\App\Controller\User\API\Upload::class, "main"], "POST");
Route::add("/user/upload/get", [\App\Controller\User\API\Upload\Upload::class, "get"], "POST");

//订单查询
Route::add("/search", [\App\Controller\User\Index::class, "search"], "GET");


//语言包
Route::add("/language/pack", [\App\Controller\Language::class, "pack"], "GET");   //语言包
Route::add("/language/record", [\App\Controller\Language::class, "record"], "POST");    //临时记录


//开始安装
Route::add("/install", [\App\Controller\Install::class, "step"], "GET");
Route::add("/install/database", [\App\Controller\Install::class, "database"], "POST");
Route::add("/install/server", [\App\Controller\Install::class, "server"], "POST");
Route::add("/install/finish", [\App\Controller\Install::class, "finish"], "POST");
