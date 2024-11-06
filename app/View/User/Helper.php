<?php
declare (strict_types=1);

namespace App\View\User;

use App\Model\Site;
use App\Model\User;
use App\Service\User\Item;
use App\View\Helper as H;
use Kernel\Annotation\Inject;
use Kernel\Component\Singleton;
use Kernel\Container\Di;
use Kernel\Context\App;
use Kernel\Context\Interface\Request;
use Kernel\Context\Interface\Route;
use Kernel\Exception\NotFoundException;
use Kernel\Language\Language;
use Kernel\Plugin\Const\Point;
use Kernel\Plugin\Menu;
use Kernel\Plugin\Plugin;
use Kernel\Plugin\Usr;
use Kernel\Util\Config;
use Kernel\Util\Context;
use Kernel\Waf\Filter;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class Helper extends AbstractExtension
{
    use Singleton;

    #[Inject]
    private \App\Service\Common\Config $config;

    /**
     * @return TwigFunction[]
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction('sku_main_img', [$this, 'getSkuMainImg']),
            new TwigFunction('cat_html', [$this, 'getCatHtml']),
            new TwigFunction('widget_name', [$this, 'getWidgetName']),
            new TwigFunction('text_o', [$this, 'textOptimize']),
            new TwigFunction('order_status', [$this, 'getOrderStatus']),
            new TwigFunction('order_item_status', [$this, 'getOrderItemStatus']),
            new TwigFunction('convert_class', [$this, 'convertClass']),
            new TwigFunction('link_active', [$this, 'linkActive']),
            new TwigFunction('submenu_open', [$this, 'submenuOpen']),
            new TwigFunction('plugin_menus', [$this, 'getPluginMenus']),
            new TwigFunction('view_languages', [$this, 'getViewLanguages']),
            new TwigFunction('items', [$this, 'getItems']),
            new TwigFunction('user_var', [$this, 'getUserVar']),
            new TwigFunction('index_var', [$this, 'getIndexVar']),
        ];
    }


    /**
     * @return string
     * @throws \ReflectionException
     */
    public function getUserVar(): string
    {
        $user = Context::get(User::class);
        $env = Usr::inst()->userToEnv($user?->id);
        return H::inst()->setScriptVar([
            "language" => strtolower(Context::get(\Kernel\Language\Entity\Language::class)->preferred),
            "DEBUG" => App::$debug,
            "CCY" => $this->config->getCurrency()->symbol,
            "HACK_ROUTE_TABLE_COLUMNS" => Plugin::inst()->hook($env, Point::HACK_ROUTE_TABLE_COLUMNS),
            "HACK_SUBMIT_FORM" => Plugin::inst()->hook($env, Point::HACK_SUBMIT_FORM),
            "HACK_SUBMIT_TAB" => Plugin::inst()->hook($env, Point::HACK_SUBMIT_TAB),
            "group" => $user?->group?->toArray() ?? [],
            "PAY_CONFIG_CHECKOUT_COUNTER" => $this->config->getMainConfig("pay.checkout_counter") ?? 0
        ]);
    }

    /**
     * @return string
     * @throws \ReflectionException
     */
    public function getIndexVar(): string
    {
        return H::inst()->setScriptVar([
            "language" => strtolower(Context::get(\Kernel\Language\Entity\Language::class)->preferred),
            "DEBUG" => App::$debug,
            "CCY" => $this->config->getCurrency()->symbol,
            "PAY_CONFIG_CHECKOUT_COUNTER" => $this->config->getMainConfig("pay.checkout_counter") ?? 0
        ]);
    }

    /**
     * @param int|null $page
     * @param int|null $size
     * @return array
     * @throws NotFoundException
     * @throws \ReflectionException
     */
    public function getItems(?int $page = null, ?int $size = null): array
    {
        /**
         * @var Request $request
         */
        $request = Context::get(Request::class);
        /**
         * @var Item $item
         */
        $item = Di::inst()->make(Item::class);
        $siteOwner = Site::getUser((string)$request->header("Host"));
        return $item->list(
            customer: Context::get(User::class),
            categoryId: $request->get("cid", Filter::INTEGER),
            merchant: $siteOwner,
            keywords: $request->get("keywords"),
            page: $page,
            size: $size
        );
    }


    public function getViewLanguages(): string
    {

        /**
         * @var \Kernel\Language\Entity\Language $var
         */
        $var = Context::get(\Kernel\Language\Entity\Language::class);


        $language = Config::get("language");

        $html = '<select class="language-select d-hide" style="width: auto;">';
        foreach ($language['languages'] as $lang) {
            $html .= sprintf('<option value="%s" data-icon="%s" ' . ($var->preferred == strtolower($lang['code']) ? 'selected' : '') . '>%s</option>', $lang['code'], "/assets/common/fonts/language/" . strtolower($lang['code']) . ".svg", $lang['language']);
        }

        $html .= '</select>';
        return $html;
    }

    /**
     * @param array $links
     * @return string
     */
    public function submenuOpen(array $links): string
    {
        $var = Context::get(Route::class);
        return in_array($var->route(), $links) ? 'open' : '';
    }

    /**
     * @param string $link
     * @return string
     */
    public function linkActive(string $link): string
    {
        /**
         * @var Route $var
         */
        $var = Context::get(Route::class);
        return $var->route() == $link ? 'active' : '';
    }

    /**
     * @param array $skus
     * @return string
     */
    public function getSkuMainImg(array $skus): string
    {
        foreach ($skus as $sku) {
            if ($sku["picture_url"]) {
                return $sku["picture_url"];
            }
        }
        return "/favicon.ico";//default img
    }

    /**
     * @param array $category
     * @return string
     */
    public function getCatHtml(array $category): string
    {
        $html = ' <ul class="nav-main-submenu"> ';
        foreach ($category as $cate) {
            if (isset($cate['children']) && is_array($cate['children']) && count($cate['children']) > 0) {
                $html .= sprintf('<li class="nav-main-item" ><a class="nav-main-link nav-main-link-submenu" data-toggle = "submenu" aria-haspopup = "true" aria-expanded = "false" href = "#" ><img src = "%s" class="category-icon" ><span class="nav-main-link-name" >%s(%d)</span > </a > ', $cate['icon'], $cate['name'], $cate['item_count']);
                $html .= $this->getCatHtml($cate['children']);
                $html .= ' </li > ';
            } else {
                $html .= sprintf('<li class="nav-main-item" ><a class="nav-main-link" href = "/?cid=%d" ><img src = "%s" class="category-icon" ><span class="nav-main-link-name" >%s(%d)</span ></a > </li > ', $cate['id'], $cate['icon'], $cate['name'], $cate['item_count']);
            }
        }
        $html .= ' </ul> ';
        return $html;
    }

    /**
     * @param array $widget
     * @return array
     */
    public function getWidgetDict(array $widget): array
    {
        if (empty($widget['data'])) {
            return [];
        }

        $options = preg_split("/\r\n|\n|\r/", trim($widget['data']));
        if (count($options) === 0) {
            return [];
        }
        $list = [];
        $defaults = null;
        if ($widget['type'] === "checkbox") {
            $defaults = [];
        }

        foreach ($options as $item) {
            $item = trim($item);
            $arr = explode("=", $item);
            if (count($arr) === 2) {
                $text = $arr[0];
                $para = explode(",", $arr[1]);
                $list[] = [
                    'id' => $para[0],
                    'name' => $text
                ];
                if (isset($para[1]) && $para[1] === "default") {
                    if ($widget['type'] === "checkbox") {
                        $defaults[] = $para[0];
                    } else {
                        $defaults = $para[0];
                    }
                }
            }
        }
        return ['dict' => $list, 'default' => $defaults];
    }


    /**
     * @param array $widget
     * @param mixed $value
     * @return string
     */
    public function getWidgetName(array $widget, mixed $value): string
    {
        $map = $this->getWidgetDict($widget);
        $str = "";

        if ($widget['type'] === "checkbox") {
            foreach ($value as $val) {
                foreach ($map['dict'] as $dict) {
                    if ($val == $dict['id']) {
                        $str .= $dict['name'] . ",";
                    }
                }
            }
            return trim($str, ",");
        } elseif ($widget['type'] === "select" || $widget['type'] === "radio") {
            foreach ($map['dict'] as $dict) {
                if ($value == $dict['id']) {
                    $str = $dict['name'];
                }
            }
            return $str;
        }
        return $value;
    }


    /**
     * @param string $text
     * @return string
     * @throws \ReflectionException
     */
    public function textOptimize(string $text): string
    {
        $text = strip_tags($text);

        $maxLength = 24; // 设置最大长度
        $baseFontSize = 22; // 基础字体大小
        $textLength = mb_strlen($text); // 获取文本长度

        // 根据文本长度调整字体大小
        if ($textLength > 10) {
            $fontSize = max($baseFontSize - ($textLength - 10), 12); // 文字越多，字体越小，最小到12px
        } else {
            $fontSize = $baseFontSize;
        }

        // 如果文本长度超过最大长度，则截取并添加...
        if ($textLength > $maxLength) {
            $text = mb_substr($text, 0, $maxLength) . ' ..';
        }

        // 返回调整后的文本和字体大小
        return '<span style = "font-size:' . $fontSize . 'px;" > ' . Language::inst()->output($text) . '</span> ';
    }


    /**
     * @param int $status
     * @return string
     * @throws \ReflectionException
     */
    public function getOrderStatus(int $status): string
    {
        $html = [
            '<status style = "color: red;" > ' . Language::inst()->output('未支付') . '</status > ',
            '<status style = "color: #2bc40b;" > ' . Language::inst()->output('已支付') . '</status > ',
            '<status style = "color: gray;" > ' . Language::inst()->output('已取消') . '</status > ',
            '<status style = "color: #7275f2;" > ' . Language::inst()->output('正在支付') . '</status > '
        ];
        return $html[$status];
    }

    /**
     * @param int $status
     * @param int $orderStatus
     * @return string
     * @throws \ReflectionException
     */
    public function getOrderItemStatus(int $status, int $orderStatus): string
    {
        if ($orderStatus != 1) {
            return ' - ';
        }

        $html = [
            '<span class="fw-semibold d-inline-block py-1 px-3 rounded-pill bg-warning-light text-warning fs-sm" > ' . Language::inst()->output('等待发货') . '</span > ',
            '<span class="fw-semibold d-inline-block py-1 px-3 rounded-pill bg-success-light text-success fs-sm" > ' . Language::inst()->output('已发货') . '</span > ',
            '<span class="fw-semibold d-inline-block py-1 px-3 rounded-pill bg-danger-light text-danger fs-sm" > ' . Language::inst()->output('发货异常') . '</span > ',
            '<span class="fw-semibold d-inline-block py-1 px-3 rounded-pill bg-primary-light text-white fs-sm" > ' . Language::inst()->output('已收货') . '</span > ',
            '<span class="fw-semibold d-inline-block py-1 px-3 rounded-pill bg-warning-light text-white fs-sm" > ' . Language::inst()->output('正在维权') . '</span > ',
            '<span class="fw-semibold d-inline-block py-1 px-3 rounded-pill bg-danger-light text-white fs-sm" > ' . Language::inst()->output('已退款') . '</span > ',
            '<span class="fw-semibold d-inline-block py-1 px-3 rounded-pill bg-danger-light text-white fs-sm" > ' . Language::inst()->output('平台发货失败') . '</span > '
        ];
        return $html[$status];
    }

    /**
     * @param int|bool $state
     * @param array $class
     * @return string
     */
    public function convertClass(mixed $state, array $class = ["d-show", "d-hide"]): string
    {
        if ((bool)$state) {
            return $class[0];
        }
        return $class[1];
    }

    /**
     * @param string $usr
     * @return string
     * @throws \ReflectionException
     */
    public function getPluginMenus(string $usr): string
    {
        if ($usr == "*") {
            return "";
        }

        $pluginMenus = Menu::inst()->list($usr);
        $html = "";
        if (count($pluginMenus) > 0) {
            $submenu = "";
            $routes = [];
            $alert = Language::inst()->output('要使用插件的路由和菜单功能，必须通过您自己绑定的域名进行访问。当前您正在通过主站域名访问，这是不支持的。');
            foreach ($pluginMenus as $menu) {
                $route = $menu['route'] ?? "";
                $routes[] = $route;
                $icon = str_starts_with($menu['icon'] ?? '', 'icon-') ? H::inst()->loadIcon($menu['icon'], "nav-main-link-icon") : '<img class="nav-menu-img-icon" src="' . $menu["icon"] . '">';
                $target = isset($menu['target']) ? 'target="' . $menu['target'] . '"' : "";

                $submenu .= <<<HTML
<li class="nav-main-item">
            <a class="nav-main-link {$this->linkActive($route)}" href="{$route}"  {$target}>
              {$icon}
              <span class="nav-main-link-name">{$menu['name']}</span>
            </a>
          </li>
HTML;
            }

            $icon = H::inst()->loadIcon("icon-yunhang1", "nav-main-link-icon");
            $html = <<<HTML
<li class="nav-main-item {$this->submenuOpen($routes)}"><a class="nav-main-link nav-main-link-submenu" data-toggle="submenu" aria-haspopup="true" aria-expanded="true" href="#">
            {$icon}
          <span class="nav-main-link-name">应用后台</span>
        </a><ul class="nav-main-submenu">{$submenu}</ul></li>
HTML;

        }

        return $html;
    }

}