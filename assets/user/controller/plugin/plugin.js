!function () {
    let table = null, isUpdates = {}, cloudVersions = null;

    const pluginHandle = {
        "Kernel\\Plugin\\Handle\\WebSocket": "WebSocket",
        "Kernel\\Plugin\\Handle\\Database": "数据库",
    }

    const handleConfigSubmit = (handle, row, table, assign = {}) => {
        let submit = eval(row.handleSubmit);
        submit[0].form.unshift({
            title: "配置名称",
            name: "name",
            type: "input",
            placeholder: "请输入配置文件名称",
            required: true
        });

        component.popup({
            submit: `/user/plugin/config/save?plugin=${row.name}&handle=${handle}`,
            confirmText: util.icon("icon-applyconfig") + " 保存配置文件",
            tab: submit,
            assign: assign,
            done: () => {
                table.refresh();
            },
            autoPosition: true,
            content: {
                css: {
                    height: "auto",
                    overflow: "inherit"
                }
            },
            height: "auto",
            width: "580px",
        });
    }

    const handleConfigTable = (handle, row, container, columns = []) => {
        let tables = [
            {checkbox: true},
            {
                field: 'name',
                title: '配置名称',
                class: "nowrap",
            },
            {
                field: 'create_time',
                title: '创建时间',
                class: "nowrap",
            },
            {
                field: 'operation',
                title: '操作',
                type: 'button',
                class: "nowrap",
                width: 170,
                buttons: [
                    {
                        icon: 'icon-biaoge-xiugai',
                        title: "修改",
                        class: 'acg-badge-h-dodgerblue',
                        click: (event, value, item, index) => {
                            let data = item.config;
                            data.name = item.name;
                            data.id = item.id;
                            handleConfigSubmit(handle, row, handleTable, data);
                        }
                    },
                    {
                        icon: 'icon-shanchu1 ',
                        title: "移除",
                        class: "btn-outline-danger",
                        click: (event, value, item, index) => {
                            message.dangerPrompt("请注意，您正在删除配置文件！", "我确认删除配置文件", () => {
                                util.post("/user/plugin/config/del", {list: [item.id]}, () => {
                                    message.success("[" + row?.info?.name + "] 配置文件(" + item.name + ")删除成功..");
                                    handleTable.refresh();
                                })
                            });
                        }
                    }
                ]
            },
        ];


        columns.forEach(item => {
            tables.splice(3, 0, item);
        });

        const handleTable = new Table(`/user/plugin/config/get?plugin=${row.name}&handle=${handle}`, container);
        handleTable.setPagination(3, [3]);
        handleTable.setColumns(tables);
        handleTable.render();
        return handleTable;
    }

    const getPluginLogs = (key, name, done) => {
        let hash = "";
        util.timer(() => {
            return new Promise(resolve => {
                if (cache.get(key) != 1) {
                    resolve(false);
                    return;
                }
                util.post({
                    url: "/user/plugin/getLogs?hash=" + hash,
                    loader: false,
                    data: {name: name},
                    done: res => {
                        hash = res.data.hash;
                        (typeof done == "function" && res?.data?.log) && done(res?.data?.log);
                        resolve(true);
                    },
                    error: () => {
                        setTimeout(() => {
                            resolve(true);
                        }, 3000);
                    },
                    fail: () => {
                        setTimeout(() => {
                            resolve(true);
                        }, 3000);
                    }
                });
            });
        }, 1, true);
    }

    const checkVersion = (uuid, key, version) => {
        console.log(uuid, key, version);
        util.timer(() => {
            return new Promise(resolve => {
                if (cloudVersions != null && $(`.${uuid}`).length > 0) {
                    if (cloudVersions.hasOwnProperty(key) && version != cloudVersions[key]) {
                        isUpdates[key] = true;
                        $(`.${uuid}`).css("color", "red").html(`${format.badge(`<i class="si si-refresh"></i> 更新 ${version} <i class="fa fa-angles-right"></i> ${cloudVersions[key]}`, "acg-badge-h-red")}`);
                    }
                    resolve(false);
                    return;
                }
                resolve(true);
            });
        }, 50, true);
    }

    table = new Table("/user/plugin/get", "#plugin-table");
    table.setPagination(10, [10, 25, 50]);
    table.setColumns([
        {checkbox: true},
        {
            field: 'info', class: "nowrap", title: '插件名称', formatter: function (info, item) {
                return `<span class="table-item table-item-cate"><img src="/user/plugin/icon?name=${item.name}" class="table-item-icon"><span class="table-item-name">${info.name}</span></span>`;
            }
        },
        {field: 'state.run', title: '状态', class: "nowrap", dict: "plugin_status"},
        {
            field: 'controls', title: '控制', class: "nowrap", type: 'button', buttons: [
                {
                    icon: 'icon-update', title: '重启', class: 'btn-outline-success',
                    show: item => item.state.run == 1,
                    click: (event, value, row, index) => {
                        util.post("/user/plugin/restart", {name: row.name}, res => {
                            table.refresh();
                            message.success("[" + row.info.name + "] 重启中，正在为您同步状态..");
                            util.waitSyncLoader(() => table.refresh());
                        }, res => {
                            message.error(`[${row.info.name}] ${res.msg}`);
                        });
                    }
                },
                {
                    icon: 'icon-qidong', title: '启动', class: 'btn-outline-success',
                    show: item => {
                        return item.state.run == 0 || item.state.run == 3;
                    }, click: (event, value, row, index) => {
                        $(event.currentTarget).find(".btn-title").html("正在启动");
                        util.post({
                            url: "/user/plugin/start",
                            data: {name: row.name},
                            done: (response, index) => {
                                table.refresh();
                                message.success("[" + row.info.name + "] 启动成功，正在为您同步状态..");
                                util.waitSyncLoader(() => table.refresh());
                            },
                            error: res => {
                                $(event.currentTarget).find(".btn-title").html("启动");
                                message.error(`[${row.info.name}] ${res.msg}`);
                            }
                        });
                    }
                },
                {
                    icon: 'icon-tingzhi2', title: '停止', class: 'btn-outline-danger',
                    show: item => {
                        return item.state.run == 1 || item.state.run == 2;
                    }, click: (event, value, row, index) => {
                        $(event.currentTarget).find(".btn-title").html("正在停止");
                        util.post("/user/plugin/stop", {name: row.name}, res => {
                            table.refresh();
                            message.success("[" + row.info.name + "] 已停止，正在为您同步状态..");
                            util.waitSyncLoader(() => table.refresh());
                        }, res => {
                            $(event.currentTarget).find(".btn-title").html("停止");
                            message.error(`[${row.info.name}] ${res.msg}`);
                        });
                    }
                },
                {
                    icon: 'icon-peizhixinxi',
                    title: '基本配置',
                    class: 'acg-badge-h-dodgerblue',
                    show: item => {
                        return item.submit != "";
                    },
                    click: (event, value, row, index) => {
                        let submit = eval(row.submit);
                        submit[0]["name"] = util.icon("icon-peizhixinxi") + " " + row.info.name;
                        component.popup({
                            submit: '/user/plugin/setCfg?name=' + row.name,
                            confirmText: util.icon("icon-applyconfig") + "<space></space>保存并应用配置",
                            tab: submit,
                            assign: row.config,
                            autoPosition: true,
                            content: {
                                css: {
                                    height: "auto",
                                    overflow: "inherit"
                                }
                            },
                            height: "auto",
                            done: () => {
                                table.refresh();
                            }
                        });
                    }
                },
                {
                    icon: 'icon-zhifu',
                    title: '支付配置',
                    class: 'acg-badge-h-turquoise',
                    click: (event, value, row, index) => {
                        component.popup({
                            tab: [
                                {
                                    name: util.icon("icon-zhifu") + " 支付配置 " + util.icon('icon-shuangyoujiantou') + " <b style='color: #2f8e99;'>" + row.info.name + "</b>",
                                    form: [
                                        {
                                            name: "custom",
                                            type: "custom",
                                            complete: (popup, dom) => {
                                                dom.html(`<div class="block block-rounded"><div class="block-header"><button type="button" class="btn btn-outline-success btn-sm add-config">` + util.icon("icon-tianjia") + ` 添加配置</button></div><div class="block-content pt-0"><table id="plugin-config-table"></table></div></div>`);
                                                const handleTable = handleConfigTable("pay", row, dom.find('#plugin-config-table'));
                                                $('.add-config').click(() => {
                                                    handleConfigSubmit("pay", row, handleTable);
                                                });
                                            }
                                        },
                                    ]
                                }
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
                            width: "620px",
                        });
                    },
                    show: item => item?.handle?.hasOwnProperty("Kernel\\Plugin\\Handle\\Pay") && item?.handleSubmit && item?.state?.run == 1
                },
                {
                    icon: 'icon-huoyuan',
                    title: '接入货源',
                    class: 'acg-badge-h-dodgerblue',
                    click: (event, value, row, index) => {
                        component.popup({
                            tab: [
                                {
                                    name: util.icon("icon-huoyuan") + " 外部货源配置 " + util.icon('icon-shuangyoujiantou') + " <b style='color: #2f8e99;'>" + row.info.name + "</b>",
                                    form: [
                                        {
                                            name: "custom",
                                            type: "custom",
                                            complete: (popup, dom) => {
                                                dom.html(`<div class="block block-rounded"><div class="block-header block-header-default"><button type="button" class="btn btn-outline-success btn-sm add-config">` + util.icon("icon-tianjia") + ` 添加配置</button></div><div class="block-content pt-0"><table id="plugin-config-table"></table></div></div>`);
                                                const payConfigTable = handleConfigTable("ship", row, dom.find('#plugin-config-table'), [
                                                    {
                                                        field: 'access',
                                                        title: '接入',
                                                        type: 'button',
                                                        class: "nowrap",
                                                        buttons: [
                                                            {
                                                                icon: 'icon-update',
                                                                class: 'acg-badge-h-green',
                                                                title: '拉取货源',
                                                                click: (event, value, row, index) => {
                                                                    component.popup({
                                                                        tab: [
                                                                            {
                                                                                name: util.icon("icon-huoyuan") + " 货源列表 " + util.icon('icon-shuangyoujiantou') + " <b style='color: #2f8e99;'>" + row.name + "</b>",
                                                                                form: [
                                                                                    {
                                                                                        name: "custom",
                                                                                        type: "custom",
                                                                                        complete: (popup, dom) => {
                                                                                            dom.html(`<div class="block block-rounded"><div class="block-header block-header-default">
<button type="button" class="btn btn-outline-success btn-sm transfer-repertory-item">${util.icon("icon-daochu2")} ${i18n("将货源接入至")} ${util.icon("icon-shuangyoujiantou")}${i18n("货源仓库")}</button>
</div><div class="block-content pt-0"><table id="plugin-ship-table"></table></div></div>`);

                                                                                            const shipTable = new Table(`/user/plugin/ship/items?configId=${row.id}`, dom.find("#plugin-ship-table"));
                                                                                            shipTable.setTree(1);

                                                                                            shipTable.setColumns([
                                                                                                {checkbox: true},
                                                                                                {
                                                                                                    field: 'name',
                                                                                                    title: '商品名称',
                                                                                                    class: "nowrap",
                                                                                                },
                                                                                                {
                                                                                                    field: 'skus',
                                                                                                    title: 'SKU/市场价/进货价/预估盈利',
                                                                                                    class: "nowrap",
                                                                                                    formatter: skus => {
                                                                                                        if (!skus || skus?.length <= 0) {
                                                                                                            return "-";
                                                                                                        }
                                                                                                        let html = "";
                                                                                                        skus.forEach(sku => {
                                                                                                            html += `<span type="button" class="acg-badge-h acg-badge-h-dodgerblue me-1 mb-1">${sku.name} / ${format.bold(format.color(format.amountRemoveTrailingZeros(sku.price), "#81cc5b"))}</span>`;
                                                                                                        });
                                                                                                        return html;
                                                                                                    }
                                                                                                }
                                                                                            ]);
                                                                                            shipTable.disablePagination();
                                                                                            shipTable.render();

                                                                                            dom.find('.transfer-repertory-item').click(() => {
                                                                                                const selections = shipTable.getSelections();
                                                                                                let items = [];

                                                                                                selections.forEach(item => {
                                                                                                    if (item?.skus?.length > 0) {
                                                                                                        items.push(item);
                                                                                                    }
                                                                                                });

                                                                                                if (items.length == 0) {
                                                                                                    layer.msg("至少选择一个商品，才能进行接入操作！");
                                                                                                    return;
                                                                                                }

                                                                                                component.popup({
                                                                                                    submit: (res, index) => {
                                                                                                        res.items = items;
                                                                                                        util.post(`/user/plugin/ship/import?configId=${row.id}`, res, res => {
                                                                                                            message.alert(`接入执行完成，您总共提交：${res?.data?.total}件商品，成功入库：${res?.data?.success}件，如有失败，请查看插件日志`);
                                                                                                        });
                                                                                                    },
                                                                                                    confirmText: util.icon("icon-yunxiazai") + " 立即导入",
                                                                                                    tab: [
                                                                                                        {
                                                                                                            name: util.icon("icon-shangxiajia") + " 配置入库信息",
                                                                                                            form: [
                                                                                                                {
                                                                                                                    title: "仓库分类",
                                                                                                                    name: "category_id",
                                                                                                                    type: "treeSelect",
                                                                                                                    placeholder: "请选择仓库分类",
                                                                                                                    dict: 'repertoryCategory',
                                                                                                                    parent: false,
                                                                                                                    regex: {
                                                                                                                        value: "^[1-9]\\d*$",
                                                                                                                        message: "必须选中一个分类"
                                                                                                                    },
                                                                                                                    required: true
                                                                                                                },
                                                                                                                {
                                                                                                                    title: "远程同步模板",
                                                                                                                    name: "markup_template_id",
                                                                                                                    type: "select",
                                                                                                                    placeholder: "请选择模板",
                                                                                                                    dict: 'repertoryItemMarkupTemplate',
                                                                                                                    tips: '如果没有模板，请在货源管理进行添加',
                                                                                                                    regex: {
                                                                                                                        value: "^[1-9]\\d*$",
                                                                                                                        message: "必须选中一个同步模板"
                                                                                                                    },
                                                                                                                    required: true
                                                                                                                },
                                                                                                                {
                                                                                                                    title: "图片本地化",
                                                                                                                    name: "image_download_local",
                                                                                                                    type: "switch",
                                                                                                                    tips: "将远程商品的图片下载到本地"
                                                                                                                },
                                                                                                                {
                                                                                                                    title: "退款方式",
                                                                                                                    name: "refund_mode",
                                                                                                                    type: "select",
                                                                                                                    placeholder: "请选择退款方式",
                                                                                                                    default: 0,
                                                                                                                    dict: "repertory_item_refund_mode",
                                                                                                                    tips: `
                        1.不支持退款：商品被购买，没有任何退款渠道
                        2.有条件退款：商品被购买，资金即时结算，就算退款，涉及的分红资金也不予回滚，供货商保留对退款金额进行调整的权利，确保双方权益得到合理处理。
                        3.无理由退款：根据商品设置的资金冻结期限，所有与订单相关的资金将被冻结，只有等到解冻时间后，才可以使用这部分资金。`.trim().replaceAll("\n", "<br><br>"),
                                                                                                                    required: true
                                                                                                                },
                                                                                                                {
                                                                                                                    title: "自动收货时效",
                                                                                                                    name: "auto_receipt_time",
                                                                                                                    type: "input",
                                                                                                                    placeholder: "自动收货时效",
                                                                                                                    default: 5040,
                                                                                                                    tips: "自动收货时效，单位/分钟，如果为'0'的情况下，货物会发货并且立即收货，不需要经过顾客同意"
                                                                                                                },
                                                                                                            ]
                                                                                                        }
                                                                                                    ],
                                                                                                    content: {
                                                                                                        css: {
                                                                                                            height: "auto",
                                                                                                            overflow: "inherit"
                                                                                                        }
                                                                                                    },
                                                                                                    autoPosition: true,
                                                                                                    height: "auto",
                                                                                                    width: "580px",
                                                                                                    maxmin: false
                                                                                                });
                                                                                            });
                                                                                        }
                                                                                    },
                                                                                ]
                                                                            }
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
                                                                        width: "820px",
                                                                    });


                                                                }
                                                            }
                                                        ]
                                                    },
                                                ]);
                                                dom.find('.add-config').click(() => {
                                                    handleConfigSubmit("ship", row, payConfigTable);
                                                });
                                            }
                                        },
                                    ]
                                }
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
                            width: "820px",
                        });
                    },
                    show: item => item?.handle?.hasOwnProperty("Kernel\\Plugin\\Handle\\ForeignShip") && item?.handleSubmit && item?.state?.run == 1
                },
                {
                    icon: 'icon-rizhi',
                    title: '日志',
                    class: 'acg-badge-h-tan',
                    click: (event, value, row, index) => {
                        let readLogsThreadKey = util.generateRandStr(6);
                        component.popup({
                            tab: [
                                {
                                    name: util.icon('icon-debug', 'icon-18px') + ' 插件:[' + row.info.name + ']->实时日志',
                                    form: [
                                        {
                                            name: "logs",
                                            type: "textarea",
                                            placeholder: "暂无日志",
                                            default: "正在读取..",
                                            height: "720px",
                                            disabled: true,
                                            complete: (popup, val, dom) => {
                                                let scroll = true;
                                                dom.parent().parent().parent().parent().css("padding", "0px");
                                                dom.get(0).style.setProperty("border-radius", "0px", "important");
                                                cache.set(readLogsThreadKey, 1);
                                                dom.hover(
                                                    () => scroll = false,
                                                    () => scroll = true
                                                );
                                                getPluginLogs(readLogsThreadKey, row.name, log => {
                                                    popup.setTextarea("logs", log);
                                                    if (scroll) {
                                                        dom.scrollTop(dom.prop("scrollHeight"));
                                                    }
                                                });
                                            }
                                        },
                                        {
                                            name: "handle",
                                            type: "custom",
                                            complete: (popup, dom) => {
                                                let logState = row.systemConfig.log;
                                                let logOpenBtn = `<button type="button" class="btn btn-outline-success btn-sm open-logs me-2 mb-2">` + util.icon("icon-qidong") + ` 开启日志</button>`;
                                                let logCloseBtn = `<button type="button" class="btn btn-outline-danger btn-sm open-logs me-2 mb-2">` + util.icon("icon-tingzhi2") + ` 关闭日志</button>`;

                                                dom.html(`<div style="text-align: center;"><span class="log-btn">` + (logState === 1 ? logCloseBtn : logOpenBtn) + `</span><button type="button" class="btn btn-outline-danger btn-sm clear-logs me-2 mb-2">` + util.icon("icon-shanchu") + ` 清空日志(无法恢复)</button></div>`);

                                                $('.log-btn').click(function () {
                                                    logState = (logState === 1 ? 0 : 1);
                                                    $('.log-btn').html(logState === 1 ? logCloseBtn : logOpenBtn);
                                                    util.post("/user/plugin/setSysCfg?name=" + row.name, {log: logState}, done => {
                                                        message.success("[" + row.info.name + "] " + (logState === 1 ? "开启日志" : "关闭日志"));
                                                    });
                                                });

                                                $('.clear-logs').click(() => {
                                                    util.post("/user/plugin/clearLog", {name: row.name}, done => {
                                                        message.success("[" + row.info.name + "] 日志已清空");
                                                    });
                                                });
                                            }
                                        },
                                    ]
                                }
                            ],
                            width: "920px",
                            end: () => {
                                cache.del(readLogsThreadKey);
                            }
                        });
                    }
                },
            ]
        },
        {
            field: 'info.arch', title: '支持架构', class: "nowrap", formatter: arch => {
                let html = "";
                if (arch & 1) {
                    html += format.badge('<i class="fa fa-window-restore opacity-50 me-1"></i>CLI', "acg-badge-h-green");
                }
                if (arch & 2) {
                    html += format.badge('<i class="fa fa-window-maximize opacity-50 me-1"></i>FPM', "acg-badge-h-dodgerblue");
                }
                return html;
            }
        },
        {
            field: 'info.version', title: '版本号',
            formatter: (version, item) => {
                const uuid = util.generateRandStr(12);
                checkVersion(uuid, item.name, version);
                return `<span class="plugin-update-log plugin-version-${item.name} ${uuid}" style="cursor: pointer;"><span class="badge badge-light-primary">${version}</span></span>`
            },
            class: "nowrap",
            events: {
                'click .plugin-update-log': (event, value, row, index) => {
                    let readLogsThreadKey = util.generateRandStr(6);
                    util.post({
                        url: "/user/store/plugin/version/list",
                        data: {key: row.name},
                        done: updateResult => {
                            if (updateResult?.data?.length <= 0) {
                                layer.msg("暂时没有更新内容");
                                return;
                            }
                            component.popup({
                                submit: isUpdates[row.name] ? () => {
                                    message.success("插件开始升级，请勿刷新网页或关闭网页..");

                                    const keydown = function (event) {
                                        if (event.key === 'F5') {
                                            event.preventDefault(); // 阻止默认行为，即刷新
                                            layer.msg("正在进行重要更新，请勿刷新网页！");
                                        }
                                    };

                                    const beforeunload = function (event) {
                                        const confirmationMessage = '您正在进行重要更新，请勿随意关闭网页！';
                                        event.returnValue = confirmationMessage;
                                        return confirmationMessage;
                                    }

                                    //开始升级
                                    util.post({
                                        url: "/user/store/plugin/version/update",
                                        data: {key: row.name},
                                        loader: false,
                                        done: () => {
                                            document.removeEventListener("keydown", keydown);
                                            window.removeEventListener("beforeunload", beforeunload);
                                            message.success("插件更新成功..");
                                            $('.update-tips').html("插件更新已完成，请耐心等待平台同步..");
                                            setTimeout(() => {
                                                cache.del(readLogsThreadKey);
                                            }, 300);


                                            util.waitSyncLoader(() => {
                                                layer.closeAll();
                                                table.refresh();
                                            });
                                        }
                                    });

                                    layer.closeAll();
                                    document.addEventListener('keydown', keydown);
                                    window.addEventListener('beforeunload', beforeunload);

                                    component.popup({
                                        tab: [
                                            {
                                                name: util.icon("icon-version") + " <span style='color: #63bfea' class='update-tips'>插件正在更新..</span>",
                                                form: [
                                                    {
                                                        name: "logs",
                                                        type: "textarea",
                                                        placeholder: "暂无日志",
                                                        default: "正在进行准备工作，请勿刷新或关闭网页..",
                                                        height: "662px",
                                                        disabled: true,
                                                        complete: (popup, val, dom) => {
                                                            dom
                                                                .css("padding", "15px")
                                                                .css("color", "#06b20f")
                                                                .css("background-color", "transparent")
                                                                .css('overflow', 'hidden')
                                                                .css('overflow-y', 'auto')
                                                                .css('scrollbar-width', 'none')
                                                                .css('border-radius', '0')
                                                                .css('resize', 'none')
                                                                .css('border', 'none');
                                                            dom.parent().parent().parent().parent().css("padding", "0px").parent().css("overflow", "hidden").css("background", "#fff").addClass("update-window");
                                                            $('.layui-layer-shade').addClass("update-window-shadow");
                                                            $('.component-popup').css("background", "#ffffffb0");
                                                            $('.layui-layer-title').css("background", "#ffffffb0");
                                                            let hash = "";
                                                            cache.set(readLogsThreadKey, 1);
                                                            getPluginLogs(readLogsThreadKey, row.name, log => {
                                                                popup.setTextarea("logs", log);
                                                                dom.scrollTop(dom.prop("scrollHeight"));
                                                            });
                                                        }
                                                    }
                                                ]
                                            }
                                        ],
                                        width: "1080px",
                                        height: "720px",
                                        maxmin: false,
                                        closeBtn: false,
                                        shade: 1,
                                        end: () => {
                                            cache.del(readLogsThreadKey);
                                        }
                                    });
                                } : false,
                                confirmText: util.icon("icon-update") + " 立即更新",
                                width: "620px",
                                height: "720px",
                                tab: [
                                    {
                                        name: util.icon("icon-version") + " <span style='color: #63bfea'>版本列表</span>",
                                        form: [
                                            {
                                                title: false,
                                                name: "custom",
                                                type: "custom",
                                                complete: (form, dom) => {
                                                    dom.html(`<div class="layui-timeline version-list"></div>`);
                                                    const $versionList = dom.find(".version-list");
                                                    updateResult.data.forEach(item => {
                                                        $versionList.append(`<div class="layui-timeline-item">
                                                                        <i class="layui-icon layui-timeline-axis"></i>
                                                                        <div class="layui-timeline-content">
                                                                          <h3 class="layui-timeline-title fs-5" style="color: #3ebe84;">${item.version} ${item.version == row.info.version ? "←" : ''}</h3>
                                                                          <p>${item.content}</p>
                                                                          <p class="fw-normal" style="font-size: 12px;color: #009a25;">${item.create_time}</p>
                                                                        </div>
                                                                      </div>`);
                                                    });
                                                }
                                            }
                                        ]
                                    }
                                ],
                                maxmin: false,
                                shadeClose: true,
                                assign: {}
                            });
                        }
                    });


                }
            }
        },
        {field: 'info.author', class: "nowrap", title: '开发商'},
        {
            field: 'systemConfig.top',
            title: 'TOP',
            class: "nowrap",
            type: "switch",
            text: "置顶|无",
            reload: true,
            change: (state, row) => {
                util.post("/user/plugin/setSysCfg?name=" + row.name, {top: state}, done => {
                    table.$table.bootstrapTable('refresh', {
                        silent: true, pageNumber: 1
                    });
                });
            }
        },
        {
            field: 'info.url', class: "nowrap", title: 'MORE', width: 170, type: 'button', buttons: [
                {
                    icon: 'icon-wendang', title: '文档', class: 'acg-badge-h-dodgerblue',
                    click: (event, value, row, index) => {
                        if (row?.state?.run != 1) {
                            layer.msg("查看插件文档，需要先启动插件。");
                            return;
                        }
                        window.open(`/user/plugin/wiki?name=${row.name}`);
                    }
                },
                {
                    icon: "icon-shanchu1",
                    class: 'btn-outline-danger',
                    title: "卸载",
                    click: (event, value, row, index) => {
                        message.ask(`您正在卸载插件(${row?.info?.name})，是否继续？`, () => {
                            util.post("/user/store/uninstall", {key: row.name}, res => {
                                message.success("[" + row?.info?.name + "] 卸载成功!");
                                table.refresh();
                            });
                        });
                    }
                },
            ]
        },
    ]);
    table.setSearch([
        {title: "插件名称/简介/插件作者/插件ID(模糊搜索)", name: "keyword", type: "input", width: 320},
        {title: "运行状态", name: "state", type: "select", dict: "plugin_status"},
    ]);
    table.setState("type", "plugin_type");
    table.setFloatMessage([
        {field: 'info.desc', title: '简介'},
        {
            field: 'handle', title: '能力', class: "nowrap", formatter: (handle, item) => {
                let html = format.badge(_Dict.result("plugin_type", item?.info?.type), "acg-badge-h-green");
                for (const pluginHandleKey in pluginHandle) {
                    if (handle.hasOwnProperty(pluginHandleKey)) {
                        html += format.badge(pluginHandle[pluginHandleKey], "acg-badge-h-dodgerblue");
                    }
                }

                if (item.command.length > 0) {
                    html += format.badge("Command", "acg-badge-h-dodgerblue");
                }

                if (item.language.length > 0) {
                    html += format.badge("国际化", "acg-badge-h-dodgerblue");
                }

                if (item.route.length > 0) {
                    html += format.badge("路由", "acg-badge-h-dodgerblue");
                }

                if (item.menu.length > 0) {
                    html += format.badge("菜单", "acg-badge-h-dodgerblue");
                }

                return html;
            }
        },
        {field: 'name', title: '标识'}
    ]);
    table.onResponse(res => {
        if (!cloudVersions) {
            util.post({
                url: "/user/store/plugin/versions",
                loader: false,
                done: result => {
                    cloudVersions = result.data;
                },
                error: () => false,
                fail: () => false
            });
        }
    });
    table.render();

    $('.plugin-starts').click(() => {
        const plugins = table.getSelections();
        if (plugins.length == 0) {
            message.error("(　◜◡‾) 请先至少勾选1个插件再进行一键启动!");
            return;
        }
        let index = 0;
        util.timer(() => {
            const startLoadIndex = layer.load(2, {shade: ['0.3', '#fff']});
            return new Promise(resolve => {
                const plugin = plugins[index];
                index++;
                if (plugin && plugin?.state?.run == 0) {
                    message.success(`(⁎⁍̴̛ᴗ⁍̴̛⁎)‼ [${plugin?.info?.name}] 开始启动..`);
                    util.post({
                        url: "/user/plugin/start",
                        data: {name: plugin.name},
                        loader: false,
                        done: (response, index) => {
                            table.refresh();
                            message.success(`(⁎⁍̴̛ᴗ⁍̴̛⁎)‼ [${plugin?.info?.name}] 已启动完成!`);
                            resolve(true);
                        },
                        error: (res) => {
                            message.error(`ヽ( ^ω^ ゞ ) [${plugin?.info?.name}] ${res?.msg}`);
                            resolve(true);
                        },
                        fail: () => {
                            message.error(`ヽ( ^ω^ ゞ ) [${plugin?.info?.name}] 网络错误，请重新尝试启动插件!`);
                            resolve(true);
                        }
                    });
                    return;
                } else if (plugin && plugin?.state?.run != 0) {
                    message.success(`(╯°▽°)╯ [${plugin?.info?.name}] 该插件正在运行，已跳过一键启动!`);
                    resolve(true);
                    return;
                }
                layer.close(startLoadIndex);
                //同步
                util.waitSyncLoader(() => table.refresh());
                resolve(false);
            });
        }, 300, true);


    });

    $('.plugin-restarts').click(() => {
        const plugins = table.getSelections();
        if (plugins.length == 0) {
            message.error("(　◜◡‾) 请先至少勾选1个插件再进行一键重启!");
            return;
        }
        let index = 0;
        util.timer(() => {
            const startLoadIndex = layer.load(2, {shade: ['0.3', '#fff']});
            return new Promise(resolve => {
                const plugin = plugins[index];
                index++;
                if (plugin && plugin?.state?.run == 1) {
                    message.success(`(⁎⁍̴̛ᴗ⁍̴̛⁎)‼ [${plugin?.info?.name}] 开始重启..`);
                    util.post({
                        url: "/user/plugin/restart",
                        data: {name: plugin.name},
                        loader: false,
                        done: (response, index) => {
                            table.refresh();
                            message.success(`(⁎⁍̴̛ᴗ⁍̴̛⁎)‼ [${plugin?.info?.name}] 已发送重启请求!`);
                            resolve(true);
                        },
                        error: (res) => {
                            message.error(`ヽ( ^ω^ ゞ ) [${plugin?.info?.name}] ${res?.msg}`);
                            resolve(true);
                        },
                        fail: () => {
                            message.error(`ヽ( ^ω^ ゞ ) [${plugin?.info?.name}] 网络错误，请重新尝试!`);
                            resolve(true);
                        }
                    });
                    return;
                } else if (plugin && plugin?.state?.run != 1) {
                    message.success(`(╯°▽°)╯ [${plugin?.info?.name}] 该插件状态无法发送重启请求`);
                    resolve(true);
                    return;
                }
                layer.close(startLoadIndex);
                //同步
                util.waitSyncLoader(() => table.refresh());
                resolve(false);
            });
        }, 300, true);
    });

    $('.plugin-stops').click(() => {
        const plugins = table.getSelections();
        if (plugins.length == 0) {
            message.error("(　◜◡‾) 请先至少勾选1个插件再进行一键停止!");
            return;
        }
        let index = 0;
        util.timer(() => {
            const startLoadIndex = layer.load(2, {shade: ['0.3', '#fff']});
            return new Promise(resolve => {
                const plugin = plugins[index];
                index++;
                if (plugin && plugin?.state?.run != 0) {
                    message.success(`(⁎⁍̴̛ᴗ⁍̴̛⁎)‼ [${plugin?.info?.name}] 正在停止..`);
                    util.post({
                        url: "/user/plugin/stop",
                        data: {name: plugin.name},
                        loader: false,
                        done: (response, index) => {
                            table.refresh();
                            message.success(`(⁎⁍̴̛ᴗ⁍̴̛⁎)‼ [${plugin?.info?.name}] 已停止!`);
                            resolve(true);
                        },
                        error: (res) => {
                            message.error(`ヽ( ^ω^ ゞ ) [${plugin?.info?.name}] ${res?.msg}`);
                            resolve(true);
                        },
                        fail: () => {
                            message.error(`ヽ( ^ω^ ゞ ) [${plugin?.info?.name}] 网络错误，请重新停止该插件!`);
                            resolve(true);
                        }
                    });
                    return;
                } else if (plugin && plugin?.state?.run == 0) {
                    message.success(`(╯°▽°)╯ [${plugin?.info?.name}] 该插件已处于停止态!`);
                    resolve(true);
                    return;
                }
                layer.close(startLoadIndex);
                //同步
                util.waitSyncLoader(() => table.refresh());
                resolve(false);
            });
        }, 300, true);
    });
}();