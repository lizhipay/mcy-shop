<?php
declare (strict_types=1);

namespace App\View\Admin;

use App\Const\Memory as MemoryConst;
use App\Model\User;
use App\Service\Common\Config;
use App\View\Helper as H;
use Kernel\Annotation\Inject;
use Kernel\Component\Singleton;
use Kernel\Container\Memory;
use Kernel\Context\App;
use Kernel\Context\Interface\Route;
use Kernel\Language\Language;
use Kernel\Plugin\Const\Point;
use Kernel\Plugin\Menu;
use Kernel\Plugin\Plugin;
use Kernel\Plugin\Usr;
use Kernel\Util\Context;
use Kernel\Util\Route as R;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class Helper extends AbstractExtension
{

    use Singleton;


    #[Inject]
    private Config $config;


    /**
     * @return TwigFunction[]
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction('sku_main_img', [$this, 'getSkuMainImg']),
            new TwigFunction('menus', [$this, 'getMenus']),
            new TwigFunction('admin_var', [$this, 'getAdminVar'])
        ];
    }


    /**
     * @return string
     * @throws \ReflectionException
     */
    public function getAdminVar(): string
    {
        return H::inst()->setScriptVar([
            "language" => strtolower(Context::get(\Kernel\Language\Entity\Language::class)->preferred),
            "DEBUG" => App::$debug,
            "version" => App::$version,
            "CCY" => $this->config->getCurrency()->symbol,
            "HACK_ROUTE_TABLE_COLUMNS" => Plugin::inst()->hook(Usr::MAIN, Point::HACK_ROUTE_TABLE_COLUMNS),
            "HACK_SUBMIT_FORM" => Plugin::inst()->hook(Usr::MAIN, Point::HACK_SUBMIT_FORM),
            "HACK_SUBMIT_TAB" => Plugin::inst()->hook(Usr::MAIN, Point::HACK_SUBMIT_TAB),
        ]);
    }


    /**
     * 渲染菜单
     * @return string
     * @throws \ReflectionException
     */
    public function getMenus(): string
    {
        $route = \Kernel\Util\Context::get(Route::class);
        $var = Memory::instance()->get(MemoryConst::ADMIN_MANAGE_MENU_ROUTE);


        //$pluginMenus = \Kernel\Plugin\Utility::instance()->getMenu("*");
        $pluginMenus = Menu::inst()->list("*");

        if (count($pluginMenus) > 0) {
            $plugin = [
                [
                    "name" => Language::inst()->output("应用后台"),
                    "icon" => 'icon-yunhang1',
                    "type" => R::TYPE_MENU,
                    "children" => $pluginMenus
                ]
            ];


            foreach ($var['menu'] as $index => $value) {
                if ($value['route'] == "admin.plugin") {
                    $var['menu'][$index]['children'] = array_merge($value['children'], $plugin);
                }
            }
        }


        $str = '';
        foreach ($var['menu'] as $menu) {
            $str .= '<li class="nav-main-heading">' . Language::inst()->output(trim($menu['name'])) . '</li>';
            if (isset($menu['children']) && count($menu['children']) > 0) {
                $str .= $this->getMenuChildren($menu['children'], $route);
            }
        }
        return $str;
    }

    /**
     * @param Route $route
     * @param array $item
     * @return bool
     */
    private function getActive(Route $route, array $item): bool
    {
        if (isset($item['route']) && $route->route() == $item['route']) {
            return true;
        }

        if (isset($item['children']) && count($item['children']) > 0) {
            foreach ($item['children'] as $child) {
                $state = $this->getActive($route, $child);
                if ($state === true) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * @param array $children
     * @param Route $route
     * @return string
     * @throws \ReflectionException
     */
    private function getMenuChildren(array $children, Route $route): string
    {
        $str = '';
        foreach ($children as $child) {
            $isChild = isset($child['children']) && count($child['children']) > 0;
            $active = $this->getActive($route, $child);
            //图标
            $icon = str_starts_with($child['icon'] ?? '', 'icon-') ? '<i class="nav-main-link-icon">' . \App\View\Helper::inst()->loadIcon($child['icon']) . '</i>' : '<img class="nav-menu-img-icon" src="' . $child["icon"] . '">';
            $target = isset($child['target']) ? 'target="' . $child['target'] . '"' : "";

            $html = '<li class="nav-main-item ' . ($active ? 'open' : '') . '"><a ' . $target . ' class="nav-main-link ' . ($active ? 'active' : '') . ($isChild ? ' nav-main-link-submenu ' : '') . '" ' . ($isChild ? 'data-toggle="submenu"' : '') . '
           aria-expanded="false" aria-haspopup="true" href="' . ($child['type'] == R::TYPE_PAGE ? $child['route'] : 'javascript:void(0);') . '"> 
           ' . ($child['icon'] ? $icon : '') . ' 
            <span class="nav-main-link-name">' . Language::inst()->output(trim($child['name'])) . '</span>
        </a>';
            if ($isChild) {
                $html .= '<ul class="nav-main-submenu">';
                $html .= $this->getMenuChildren($child['children'], $route);
                $html .= '</ul>';
            }
            $html .= '</li>';
            $str .= $html;
        }
        return $str;
    }
}