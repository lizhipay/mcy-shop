<?php
declare (strict_types=1);

namespace App\Command;

use Kernel\Console\Command;
use Kernel\Util\Date;

/**
 * 使用该命令需要依赖nodejs，以及uglifyjs、cleancss
 * 安装uglifyjs: npm install uglify-js -g
 * 安装cleancss: npm install clean-css-cli -g
 */
class Compress extends Command
{

    /**
     * @return void
     */
    public function all(): void
    {
        $this->mergeCss();
        $this->mergeJs();
    }


    /**
     * @return void
     */
    public function mergeJs(): void
    {
        //公共
        $commons = [
            "/assets/common/js/jquery.min.js",
            "/assets/common/js/toastr.min.js",
            "/assets/common/js/language.js",
            "/assets/common/js/util/dict.js",
            "/assets/common/js/util.js",
            "/assets/common/js/layer/layer.js",
            "/assets/common/js/jquery.pjax.min.js",
            "/assets/common/js/format.js",
            "/assets/common/js/message.js",
            "/assets/common/js/component.js",
            "/assets/common/js/layui/layui.js",
            "/assets/common/js/jquery.treegrid.min.js",
            "/assets/common/js/table/bootstrap-table.min.js",
            "/assets/common/js/table/bootstrap-table-treegrid.min.js",
            "/assets/common/js/jquery.qrcode.min.js",
            "/assets/common/js/component/form.js",
            "/assets/common/js/component/search.js",
            "/assets/common/js/component/xm-select.js",
            "/assets/common/js/component/tree.select.js",
            "/assets/common/js/component/authtree.js",
            "/assets/common/js/component/table.js",
            "/assets/common/js/component/select2.min.js",
            "/assets/common/js/cache.js",
            "/assets/common/js/editor/editor.js",
            "/assets/common/js/editor/code/code.js",
            "/assets/common/js/component/decimal.js",
            "/assets/common/js/broadcast.js",
            "/assets/common/js/service/treasure.js",
            "/assets/common/fonts/base/iconfont.js",
        ];

        //后台
        $admins = [
            "/assets/admin/js/codebase.app.min.js",
            "/assets/admin/js/util/dict.js",
            "/assets/admin/js/common.js",
            "/assets/common/js/pjax.js",
        ];

        //用户
        $users = [
            "/assets/user/js/oneui.app.min.js",
            "/assets/user/js/util/dict.js",
            "/assets/user/js/widget.js",
            "/assets/user/js/personal.js",
            "/assets/user/js/pay.js",
            "/assets/common/js/pjax.js",
        ];

        //首页
        $index = [
            "/assets/user/js/oneui.app.min.js",
            "/assets/user/js/widget.js",
            "/assets/user/js/visitor.js",
            "/assets/user/js/pay.js",
            "/assets/user/js/util/dict.js",
            "/assets/user/controller/index/global.js",
        ];

        $startTime = Date::timestamp();
        $this->info("[JS]开始压缩..");
        shell_exec($this->createUglifyjs($commons, "/assets/common/js/base.js"));
        shell_exec($this->createUglifyjs($admins, "/assets/admin/js/admin.js"));
        shell_exec($this->createUglifyjs($users, "/assets/user/js/user.js"));
        shell_exec($this->createUglifyjs($index, "/assets/user/js/index.js"));
        $this->success(sprintf("[JS]压缩结束，总耗时：%d秒", (Date::timestamp() - $startTime) / 1000));
    }


    /**
     * @return void
     */
    public function mergeCss(): void
    {
        $startTime = Date::timestamp();
        $this->info("[CSS]开始压缩..");
        //公共
        $admins = [
            "/assets/admin/css/codebase.min.css",
            "/assets/admin/css/admin.css",
            "/assets/common/js/layui/css/layui.css",
            "/assets/common/css/select2.min.css",
            "/assets/common/css/component.css",
            "/assets/common/css/toastr.min.css",
            "/assets/common/js/table/bootstrap-table.css",
            "/assets/common/js/layer/theme/default/layer.css"
        ];

        $users = [
            "/assets/user/css/oneui.min.css",
            "/assets/user/css/user.css",
            "/assets/common/js/layui/css/layui.css",
            "/assets/common/css/select2.min.css",
            "/assets/common/css/component.css",
            "/assets/common/css/toastr.min.css",
            "/assets/common/js/table/bootstrap-table.css",
            "/assets/common/js/layer/theme/default/layer.css"
        ];

        $index = [
            "/assets/user/css/oneui.min.css",
            "/assets/user/css/index.css",
            "/assets/common/js/layui/css/layui.css",
            "/assets/common/css/select2.min.css",
            "/assets/common/css/component.css",
            "/assets/common/css/toastr.min.css",
            "/assets/common/js/table/bootstrap-table.css",
            "/assets/common/js/layer/theme/default/layer.css"
        ];


        shell_exec($this->createCss($admins, "/assets/admin/css/admin.min.css"));
        shell_exec($this->createCss($users, "/assets/user/css/user.min.css"));
        shell_exec($this->createCss($index, "/assets/user/css/index.min.css"));

        $this->success(sprintf("[CSS]压缩结束，总耗时：%d秒", (Date::timestamp() - $startTime) / 1000));
    }

    /**
     * @param array $js
     * @param string $to
     * @return string
     */
    private function createUglifyjs(array $js, string $to): string
    {
        $res = "uglifyjs ";
        foreach ($js as $value) {
            $res .= BASE_PATH . $value . " ";
        }
        return trim($res) . " -o " . BASE_PATH . "{$to} -c -m";
    }

    /**
     * @param array $css
     * @param string $to
     * @return string
     */
    private function createCss(array $css, string $to): string
    {
        $res = "cleancss -o " . BASE_PATH . "{$to} ";
        foreach ($css as $value) {
            $res .= BASE_PATH . $value . " ";
        }
        return trim($res);
    }
}