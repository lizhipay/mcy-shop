!function () {
    let table = null, versionTable = null, authorizationTable = null;

    const createPlugin = (title, assign = {}) => {
        component.popup({
            submit: '/admin/store/developer/plugin/save',
            autoPosition: true,
            tab: [
                {
                    name: title,
                    form: [
                        {
                            title: "插件图标",
                            name: "icon",
                            type: "image",
                            placeholder: "请选择此插件的图标",
                            uploadUrl: '/admin/upload',
                            photoAlbumUrl: '/admin/upload/get',
                            height: 64,
                            required: true,
                            tips: "请认真选择图标，创建插件成功后无法修改。"
                        },
                        {
                            title: "插件名称",
                            name: "name",
                            type: "input",
                            placeholder: "插件名称",
                            required: true,
                            tips: "请认真取好插件名称，创建插件成功后无法修改。"
                        },
                        {
                            title: "插件标识",
                            name: "key",
                            type: "input",
                            placeholder: "插件唯一标识",
                            tips: "此标识全网唯一，此标识作用于：本地插件目录、插件配置、插件路由、插件菜单等重要用途",
                            required: true,
                            regex: {
                                value: "^[A-Z][a-z]+(?:[A-Z][a-z]*)*$",
                                message: "插件标识格式错误，必须使用驼峰写法，如：UserCenter、User"
                            }
                        },
                        {
                            title: "版本号",
                            name: "version",
                            type: "input",
                            placeholder: "1.0.0",
                            default: "1.0.0",
                            tips: "版本号格式：x.y.z，x为主版本号，y为次版本号，z为修订版本号",
                            required: true
                        },
                        {
                            title: "插件描述",
                            name: "description",
                            type: "textarea",
                            placeholder: "简单的描述一下插件的功能",
                            required: true,
                            tips: "请认真描述插件，创建插件成功后无法修改。"
                        },
                        {
                            title: "插件类型",
                            name: "type",
                            type: "radio",
                            dict: [
                                {id: 0, name: "通用插件"},
                                {id: 1, name: "支付插件"},
                                {id: 2, name: "货源插件"},
                                {id: 3, name: "模版/主题"},
                            ],
                            tips: "请仔细选择插件类型，创建插件成功后无法修改。"
                        },
                        {
                            title: "支持架构",
                            name: "arch",
                            type: "radio",
                            dict: [
                                {id: 0, name: "全兼容"},
                                {id: 1, name: "仅支持CLI"},
                                {id: 2, name: "仅支持FPM"}
                            ],
                            tips: "请仔细思考你开发的插件，是否拥有全兼容的能力(如process、websocket仅支持CLI架构)，创建插件成功后无法修改。"
                        },
                        {
                            title: "安装范围",
                            name: "scope",
                            type: "radio",
                            dict: [
                                {id: 0, name: "仅支持主站安装"},
                                {id: 1, name: "主站/分站均可安装"},
                            ],
                            tips: "请仔细思考你的插件用途，他是否能够被主站和分站都能够兼容使用，比如：支付插件、网站主题、网站美化插件这种类型分站也需要使用，创建插件成功后无法修改。"
                        },
                        {
                            title: "插件介绍",
                            name: "introduce",
                            type: "editor",
                            placeholder: "请填写插件的详细介绍，比如一些功能",
                            tips: "在购买插件的时候，会显示该介绍，尽量提现出插件的重要功能介绍",
                            height: 200,
                            required: true,
                        },
                        {
                            title: "公益插件",
                            name: "is_free",
                            type: "switch",
                            placeholder: "开启|关闭",
                            tips: "开启后，插件将免费提供给所有用户",
                            default: 1,
                            change: (from, value) => {
                                const a = ["monthly_fee", "quarterly_fee", "half_yearly_fee", "yearly_fee", "permanent_fee"];
                                if (value) {
                                    a.forEach(v => from.hide(v) || from.setInput(v, 0));
                                    from.hide("buy_type");
                                    from.hide("group");
                                    for (let i = 0; i < 5; i++) {
                                        from.setCheckbox("buy_type", i, false);
                                    }
                                } else {
                                    a.forEach(v => from.hide(v));
                                    from.show("buy_type");
                                    from.show("group");
                                }
                            }
                        },
                        {
                            title: "授权用户组",
                            name: "group",
                            type: "checkbox",
                            tag: true,
                            dict: "storeGroup",
                            hide: true,
                            tips: "授权用户组后，该用户组则可以免费使用此插件",
                        },
                        {
                            title: "支持套餐",
                            name: "buy_type",
                            type: "checkbox",
                            tag: true,
                            dict: [
                                {id: 0, name: "月付费"},
                                {id: 1, name: "季付费"},
                                {id: 2, name: "半年付费"},
                                {id: 3, name: "年付费"},
                                {id: 4, name: "永久付费"}
                            ],
                            hide: true,
                            change: (form, value, state) => {
                                value == 0 && (state ? form.show("monthly_fee") : form.hide("monthly_fee"));
                                value == 1 && (state ? form.show("quarterly_fee") : form.hide("quarterly_fee"));
                                value == 2 && (state ? form.show("half_yearly_fee") : form.hide("half_yearly_fee"));
                                value == 3 && (state ? form.show("yearly_fee") : form.hide("yearly_fee"));
                                value == 4 && (state ? form.show("permanent_fee") : form.hide("permanent_fee"));
                            }
                        },
                        {
                            title: "<b style='color: #71cae7;'>月付(Month)</b>",
                            name: "monthly_fee",
                            type: "number",
                            placeholder: "元/月",
                            hide: true,
                            change: (form, val) => {
                                form.setInput("quarterly_fee", ((val * 3) * 0.9).toFixed(0) + ".9");
                                form.setInput("half_yearly_fee", ((val * 6) * 0.8).toFixed(0) + ".9");
                                form.setInput("yearly_fee", ((val * 12) * 0.7).toFixed(0) + ".9");
                                form.setInput("permanent_fee", ((val * 24) * 0.5).toFixed(0) + ".9");
                            }
                        },
                        {
                            title: "<b style='color: #32CD32;'>季付(Quarterly)</b>",
                            name: "quarterly_fee",
                            type: "number",
                            placeholder: "元/季",
                            hide: true
                        },
                        {
                            title: "<b style='color: #FFA500;'>半年(Half Year)</b>",
                            name: "half_yearly_fee",
                            type: "number",
                            placeholder: "元/半年",
                            hide: true
                        },
                        {
                            title: "<b style='color: #9370DB;'>年付(Year)</b>",
                            name: "yearly_fee",
                            type: "number",
                            placeholder: "元/年",
                            hide: true
                        },
                        {
                            title: "<b style='color: #FFD700;'>永久(Permanent)</b>",
                            name: "permanent_fee",
                            type: "number",
                            placeholder: "元/永久",
                            hide: true
                        }
                    ]
                }
            ],
            assign: assign,
            done: () => {
                table.refresh();
            }
        });
    }
    const editPlugin = (title, assign = {}) => {
        component.popup({
            submit: '/admin/store/developer/plugin/save',
            autoPosition: true,
            tab: [
                {
                    name: title,
                    form: [
                        {
                            title: "插件状态",
                            name: "maintenance",
                            type: "radio",
                            dict: [
                                {id: 0, name: "正常"},
                                {id: 1, name: "不停机维护"},
                                {id: 2, name: "停机维护"}
                            ],
                            complete: (form) => {
                                if (assign.status == 2) {
                                    form.setRadio("maintenance", "2", true);
                                } else if (assign.status == 3) {
                                    form.setRadio("maintenance", "1", true);
                                }
                            },
                            hide: assign.status == 0 || assign.status == 49 || assign.status == 50
                        },
                        {
                            title: "公益插件",
                            name: "is_free",
                            type: "switch",
                            placeholder: "开启|关闭",
                            tips: "开启后，插件将免费提供给所有用户",
                            change: (from, value) => {
                                const a = ["monthly_fee", "quarterly_fee", "half_yearly_fee", "yearly_fee", "permanent_fee"];
                                if (value) {
                                    a.forEach(v => from.hide(v) || from.setInput(v, 0));
                                    from.hide("group");
                                    from.hide("buy_type");
                                    for (let i = 0; i < 5; i++) {
                                        from.setCheckbox("buy_type", i, false);
                                    }
                                } else {
                                    a.forEach(v => from.hide(v));
                                    from.show("buy_type");
                                    from.show("group");
                                }
                            }
                        },
                        {
                            title: "授权用户组",
                            name: "group",
                            type: "checkbox",
                            tag: true,
                            dict: "storeGroup",
                            hide: assign.is_free == 1,
                            tips: "授权用户组后，该用户组则可以免费使用此插件",
                        },
                        {
                            title: "支持套餐",
                            name: "buy_type",
                            type: "checkbox",
                            tag: true,
                            dict: [
                                {id: 0, name: "按月付费"},
                                {id: 1, name: "按季付费"},
                                {id: 2, name: "按半年付费"},
                                {id: 3, name: "按年付费"},
                                {id: 4, name: "永久付费"}
                            ],
                            hide: assign.is_free == 1,
                            change: (form, value, state) => {
                                value == 0 && (state ? form.show("monthly_fee") : (form.hide("monthly_fee") || form.setInput("monthly_fee", 0)));
                                value == 1 && (state ? form.show("quarterly_fee") : (form.hide("quarterly_fee") || form.setInput("quarterly_fee", 0)));
                                value == 2 && (state ? form.show("half_yearly_fee") : (form.hide("half_yearly_fee") || form.setInput("half_yearly_fee", 0)));
                                value == 3 && (state ? form.show("yearly_fee") : (form.hide("yearly_fee") || form.setInput("yearly_fee", 0)));
                                value == 4 && (state ? form.show("permanent_fee") : (form.hide("permanent_fee") || form.setInput("permanent_fee", 0)));
                            },
                            complete: (form) => {
                                if (assign.is_free == 0) {
                                    const ps = ['monthly_fee', 'quarterly_fee', 'half_yearly_fee', 'yearly_fee', 'permanent_fee'];
                                    for (let i = 0; i < ps.length; i++) {
                                        assign[ps[i]] > 0 && form.setCheckbox("buy_type", i, true);
                                    }
                                }
                            }
                        },
                        {
                            title: "<b style='color: #71cae7;'>月付(Month)</b>",
                            name: "monthly_fee",
                            type: "number",
                            placeholder: "元/月",
                            hide: assign.is_free == 1 || assign.monthly_fee <= 0
                        },
                        {
                            title: "<b style='color: #32CD32;'>季付(Quarterly)</b>",
                            name: "quarterly_fee",
                            type: "number",
                            placeholder: "元/季",
                            hide: assign.is_free == 1 || assign.quarterly_fee <= 0
                        },
                        {
                            title: "<b style='color: #FFA500;'>半年(Half Year)</b>",
                            name: "half_yearly_fee",
                            type: "number",
                            placeholder: "元/半年",
                            hide: assign.is_free == 1 || assign.half_yearly_fee <= 0
                        },
                        {
                            title: "<b style='color: #9370DB;'>年付(Year)</b>",
                            name: "yearly_fee",
                            type: "number",
                            placeholder: "元/年",
                            hide: assign.is_free == 1 || assign.yearly_fee <= 0
                        },
                        {
                            title: "<b style='color: #FFD700;'>永久(Permanent)</b>",
                            name: "permanent_fee",
                            type: "number",
                            placeholder: "元/永久",
                            hide: assign.is_free == 1 || assign.permanent_fee <= 0
                        }
                    ]
                }
            ],
            assign: assign,
            width: "500px",
            done: () => {
                table.refresh();
            }
        });
    }
    const updatePlugin = (title, assign = {}) => {
        component.popup({
            submit: '/admin/store/developer/plugin/update?key=' + assign.key,
            autoPosition: true,
            tab: [
                {
                    name: title,
                    form: [
                        {
                            title: "版本号",
                            name: "version",
                            type: "input",
                            placeholder: "正在读取..",
                            disabled: true,
                            tips: "版本号由系统自动获取，来源于插件的 <b class='text-success'>Config/Info.php</b> 文件。提交版本号前，请先在此文件中更新为新版本号，如已更新版本号，但此处仍然不显示最新版本，则需要重启HTTP服务。"
                        },
                        {
                            title: "更新文件",
                            name: "tracked",
                            type: "html",
                            language: "ini",
                            placeholder: "正在读取..",
                            complete: (form, dom) => {
                                util.post("/admin/store/developer/plugin/tracked", {key: assign.key}, res => {
                                    const data = res.data.files.map(v => v.replace(/\\/g, "/"));
                                    form.setHtml("tracked", data.join("\n"));
                                    form.setInput("version", res.data.version);
                                });
                            },
                            disabled: true,
                            height: "180px",
                            tips: "这是你更改的文件列表，系统会自动打包这些文件为ZIP更新包，然后上传至应用商店进行审核"
                        },
                        {
                            title: "更新内容",
                            name: "update_content",
                            type: "editor",
                            placeholder: "请填写更新内容",
                            height: 190
                        },
                    ]
                }
            ],
            assign: assign,
            width: "560px",
            confirmText: util.icon("icon-querenshouhuo") + " 提交更新",
            done: () => {
                versionTable.refresh();
            }
        });
    }

    const publishPlugin = row => {
        message.ask(`<div style="text-align: left;">
<h4 class="mb-3">在发布插件前，请确保满足以下条件：</h4>
<ul class="list-default">
<li class="text-warning ">插件已完成开发并经过充分测试。</li>
<li class="text-warning ">插件的目录是否存在：<b class="text-danger">/app/Plugin/${row.key}</b></li>
<li class="text-warning ">插件源代码未加密。</li>
<li class="text-warning ">插件源代码为原创，未抄袭他人作品；若移植其他程序，请在文档中声明。</li>
<li class="text-warning ">插件文档已存在，文档目录：<b class="text-danger">/app/Plugin/${row.key}/Wiki</b></li>
</ul>
<div class="mt-3 text-primary">满足上述条件后，方可提交。系统将自动打包该目录[<b class="text-danger">/app/Plugin/${row.key}</b>]为 ZIP 文件并上传至应用商店进行审核。</div> </div>`, () => {
            util.post("/admin/store/developer/plugin/publish", {key: row.key}, res => {
                message.success("插件发布成功，请耐心等待审核");
                table.refresh();
            });
        }, format.plugin(row), "确认发布");
    }

    const identityCheck = (call) => {
        util.post({
            url: "/admin/store/identity/status",
            done: res => {
                if (!res.data.status) {
                    if (res?.data?.url) {
                        $('.cert-qrcode').qrcode({
                            render: "canvas",
                            width: 200,
                            height: 200,
                            text: res?.data?.url
                        });
                        $('.cert-status-scan').show(200);


                        util.timer(() => {
                            return new Promise(resolve => {
                                util.post({
                                    url: "/admin/store/identity/status?tradeNo=" + res.data.tradeNo,
                                    loader: false,
                                    done: result => {
                                        if (result.data.status) {
                                            window.location.reload();
                                            resolve(false);
                                        } else {
                                            resolve(true);
                                        }
                                    },
                                    fail: () => resolve(true),
                                    error: () => resolve(true)
                                });
                            });
                        }, 3000, true);

                    } else {
                        $('.cert-button').click(() => {
                            util.post("/admin/store/identity/certification", {
                                cert_name: $("#cert-name").val(),
                                cert_no: $("#cert-no").val()
                            }, response => {
                                window.location.reload();
                            });
                        });
                        $('.cert-status-submit').show(200);
                    }

                    $('.identity-block').show(200);
                } else {
                    $('.developer-block').show(100);
                    call();
                }
            }
        });
    }

    identityCheck(() => {
        table = new Table("/admin/store/developer/plugin/list", "#developer-table");
        table.setPagination(10, [10, 20, 50, 100]);
        table.setColumns([
            {
                field: 'name', title: '应用名称', formatter: (name, item) => {
                    return format.plugin(item);
                }
            },
            {field: 'type', title: '类型', dict: "developer_plugin_type"},
            {
                field: 'arch', title: '支持架构', formatter: arch => {
                    let archs = [format.badge('<i class="fa fa-window-restore opacity-50 me-1"></i>CLI', "acg-badge-h-green nowrap"), format.badge('<i class="fa fa-window-maximize opacity-50 me-1"></i>FPM', "acg-badge-h-dodgerblue nowrap")];
                    if (arch == 0) {
                        return archs.join("");
                    } else if (arch == 1) {
                        return archs[0];
                    } else if (arch == 2) {
                        return archs[1];
                    }
                    return '-';
                }
            },
            {field: 'key', title: '标识'},
            {field: 'version', title: '版本号'},
            {field: 'install_num', title: '安装量'},
            {field: 'status', title: '状态', dict: "developer_plugin_status"},

            {
                field: 'scope', title: '安装范围', dict: [
                    {id: 0, name: "仅主站"},
                    {id: 1, name: "通用"},
                ]
            },
            {
                field: 'operation', title: '操作', type: 'button', buttons: [
                    {
                        icon: 'icon-jiaoseguanli',
                        class: 'acg-badge-h-green nowrap',
                        title: '授权管理',
                        click: (event, value, row, index) => {
                            const projectId = row.id;
                            component.popup({
                                tab: [
                                    {
                                        name: util.icon("icon-jiaoseguanli") + "<space></space> 授权管理 [" + row.name + "]",
                                        form: [
                                            {
                                                name: "sku",
                                                type: "custom",
                                                complete: (popup, dom) => {
                                                    dom.html(`<div class="block block-rounded"><div class="block-header block-header-default">
            <button type="button" class="btn btn-outline-success btn-sm add-plugin-authorization">${util.icon("icon-tianjia")}<space></space>${language.output("添加授权")}</button>
        </div><div class="block-content pt-0"><table id="plugin-authorization-table"></table></div></div>`);
                                                    authorizationTable = new Table("/admin/store/developer/plugin/authorization/list?pluginId=" + row.id, dom.find('#plugin-authorization-table'));
                                                    authorizationTable.setPagination(10, [10, 20, 50, 100]);
                                                    authorizationTable.setColumns([
                                                        {field: 'user', title: '用户', formatter: format.user},
                                                        {
                                                            field: 'server_ip',
                                                            title: '服务器IP',
                                                        },
                                                        {
                                                            field: 'hwid',
                                                            title: 'HWID'
                                                        },
                                                        {
                                                            field: 'expire_time',
                                                            title: '到期时间'
                                                        },
                                                        {
                                                            field: 'create_time',
                                                            title: '购买时间'
                                                        },
                                                        {
                                                            field: 'operation',
                                                            title: '操作',
                                                            type: 'button',
                                                            buttons: [
                                                                {
                                                                    icon: 'icon-shanchu1',
                                                                    class: 'acg-badge-h-red',
                                                                    click: (event, value, item, index) => {
                                                                        message.ask("你确认删除此授权吗，该操作会导致用户插件无法正常使用。", () => {
                                                                            util.post("/admin/store/developer/plugin/authorization/remove", {id: item.id}, res => {
                                                                                authorizationTable.refresh();
                                                                            });
                                                                        });

                                                                    }
                                                                }
                                                            ]
                                                        },
                                                    ]);
                                                    authorizationTable.render();


                                                    $(".add-plugin-authorization").click(() => {
                                                        component.popup({
                                                            submit: '/admin/store/developer/plugin/authorization/add',
                                                            autoPosition: true,
                                                            tab: [
                                                                {
                                                                    name: util.icon("icon-tianjia") + " 授权插件给用户",
                                                                    form: [
                                                                        {
                                                                            title: false,
                                                                            name: "plugin_id",
                                                                            type: "input",
                                                                            hide: true,
                                                                            default: row.id,
                                                                            required: true
                                                                        },
                                                                        {
                                                                            title: "用户名",
                                                                            name: "username",
                                                                            type: "input",
                                                                            placeholder: "请输入要授权的用户名",
                                                                            required: true
                                                                        },
                                                                        {
                                                                            title: "授权套餐",
                                                                            name: "subscription",
                                                                            dict: [
                                                                                {id: 0, name: "1个月"},
                                                                                {id: 1, name: "3个月"},
                                                                                {id: 2, name: "6个月"},
                                                                                {id: 3, name: "1年"},
                                                                                {id: 4, name: "永久"}
                                                                            ],
                                                                            type: "radio"
                                                                        }
                                                                    ]
                                                                }
                                                            ],
                                                            assign: {},
                                                            width: "460px",
                                                            maxmin: false,
                                                            confirmText: util.icon("icon-querenshouhuo") + " 确认授权",
                                                            done: () => {
                                                                authorizationTable.refresh();
                                                            }
                                                        });
                                                    });
                                                }
                                            },
                                        ]
                                    },
                                ],
                                assign: {},
                                autoPosition: true,
                                content: {
                                    css: {
                                        height: "auto",
                                        overflow: "inherit"
                                    }
                                },
                                height: "auto",
                                width: "1030px",
                                maxmin: false,
                                shadeClose: true
                            });
                        },
                        show: row => [1, 2, 3].includes(row.status) && row.is_free != 1
                    },
                    {
                        icon: 'icon-banbenxinxi',
                        class: 'acg-badge-h-dodgerblue nowrap',
                        title: '版本管理',
                        click: (event, value, row, index) => {
                            const projectId = row.id;
                            component.popup({
                                tab: [
                                    {
                                        name: util.icon("icon-banbenxinxi") + "<space></space> 版本管理 [" + row.name + "]",
                                        form: [
                                            {
                                                name: "sku",
                                                type: "custom",
                                                complete: (popup, dom) => {
                                                    dom.html(`<div class="block block-rounded"><div class="block-header block-header-default">
            <button type="button" class="btn btn-outline-success btn-sm add-project-version">${util.icon("icon-tianjia")}<space></space>${language.output("提交更新")}</button>
        </div><div class="block-content pt-0"><table id="project-version-table"></table></div></div>`);
                                                    versionTable = new Table("/admin/store/developer/plugin/version/list?pluginId=" + row.id, dom.find('#project-version-table'));
                                                    versionTable.setPagination(10, [10, 20, 50, 100]);
                                                    versionTable.setUpdate("/admin/project/version/save?projectId=" + row.id);
                                                    versionTable.setColumns([
                                                        {field: 'version', title: '版本号'},
                                                        {
                                                            field: 'update_hash',
                                                            title: 'hash',
                                                        },
                                                        {
                                                            field: 'download_num',
                                                            title: '下载次数'
                                                        },
                                                        {
                                                            field: 'create_time',
                                                            title: '更新时间'
                                                        },
                                                        {
                                                            field: 'status',
                                                            title: '状态',
                                                            dict: [
                                                                {id: 0, name: format.danger("正在审核")},
                                                                {id: 1, name: format.success("已推送")},
                                                            ]
                                                        },
                                                    ]);
                                                    versionTable.render();


                                                    $(".add-project-version").click(() => {
                                                        updatePlugin(util.icon("icon-tianjia") + " 提交更新 -> " + row.name, row);
                                                    });
                                                }
                                            },
                                        ]
                                    },
                                ],
                                assign: {},
                                autoPosition: true,
                                content: {
                                    css: {
                                        height: "auto",
                                        overflow: "inherit"
                                    }
                                },
                                height: "auto",
                                width: "860px",
                                maxmin: false,
                                shadeClose: true
                            });
                        }
                    },
                    {
                        icon: 'icon-shangxiajia',
                        class: 'acg-badge-h-dodgerblue nowrap',
                        title: '发布插件',
                        click: (event, value, row, index) => {
                            publishPlugin(row);
                        },
                        show: row => row.status == 0
                    },
                    {
                        icon: 'icon-shangxiajia',
                        class: 'acg-badge-h-dodgerblue nowrap',
                        title: '重新发布',
                        click: (event, value, row, index) => {
                            publishPlugin(row);
                        },
                        show: row => row.status == 49
                    },
                    {
                        icon: 'icon-biaoge-xiugai',
                        class: 'acg-badge-h-dodgerblue nowrap',
                        title: '修改',
                        click: (event, value, row, index) => {
                            let map = Object.assign({}, row);
                            map.group = component.idObjToList(row.group);
                            editPlugin(util.icon("icon-a-xiugai2") + "<space></space> 修改插件", map);
                        }
                    },
                ]
            },
        ]);
        table.setFloatMessage([
            {
                field: 'is_free', title: '插件价格', formatter: (free, item) => {
                    if (free == 1) {
                        return format.success("公益免费");
                    }
                    let html = "";
                    if (item?.monthly_fee > 0) {
                        html += format.badge(`${format.amounts(item.monthly_fee)}/月`, "acg-badge-h-red");
                    }

                    if (item?.quarterly_fee > 0) {
                        html += format.badge(`${format.amounts(item.quarterly_fee)}/季`, "acg-badge-h-red");
                    }

                    if (item?.half_yearly_fee > 0) {
                        html += format.badge(`${format.amounts(item.half_yearly_fee)}/半年`, "acg-badge-h-red");
                    }

                    if (item?.yearly_fee > 0) {
                        html += format.badge(`${format.amounts(item.yearly_fee)}/年`, "acg-badge-h-red");
                    }

                    if (item?.permanent_fee > 0) {
                        html += format.badge(`${format.amounts(item.permanent_fee)}/永久`, "acg-badge-h-red");
                    }

                    item?.group?.forEach(group => {
                        html += format.badge(group.name, "acg-badge-h-green");
                    });

                    return html;
                }
            },
            {field: 'description', title: '插件介绍'},
            {field: 'review_message', title: '审核结果'},
        ]);
        table.setSearch([
            {title: "插件名称关键词", name: "search-name", type: "input"}
        ]);
        table.setState("type", "developer_plugin_type");
        table.render();
        $('.add-plugin').click(() => {
            createPlugin(util.icon("icon-tianjia") + " 创建插件");
        });
    });
}();