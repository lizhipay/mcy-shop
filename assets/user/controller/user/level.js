!function () {
    let table = null, upgradeRequirements = {
        total_consumption_amount: [0, "总消费"],
        total_recharge_amount: [1, "总充值"],
        total_referral_count: [2, "总推广人数"],
        total_profit_amount: [3, "总盈利"]
    };

    const modal = (title, assign = {}) => {
        const requirements = assign.hasOwnProperty("upgrade_requirements") ? JSON.parse(assign.upgrade_requirements) : {};
        let requirementValues = [];

        for (const key in upgradeRequirements) {
            if (requirements.hasOwnProperty(key)) {
                requirementValues.push(upgradeRequirements[key][0]);
                assign[key] = requirements[key];
            }
        }

        delete assign.upgrade_requirements;
        component.popup({
            submit: '/user/user/level/save',
            autoPosition: true,
            height: "auto",
            width: "680px",
            tab: [
                {
                    name: title,
                    form: [
                        {
                            title: "等级图标",
                            name: "icon",
                            type: "image",
                            placeholder: "请选择图标",
                            uploadUrl: '/user/upload',
                            photoAlbumUrl: '/user/upload/get',
                            height: 64,
                            required: true
                        },
                        {
                            title: "等级名称",
                            name: "name",
                            type: "input",
                            placeholder: "请输入等级名称",
                            required: true
                        },
                        {
                            title: "直升价格",
                            name: "upgrade_price",
                            type: "input",
                            default: "0",
                            tips: "会员可以通过付费，来直接升级至该等级，如果是0，代表此等级无法通过付费升级。",
                            required: true
                        },
                        {
                            title: "权益说明",
                            name: "privilege_introduce",
                            type: "editor",
                            placeholder: "请填写会员权益说明",
                            tips: "什么是会员权益？<br>你想给他什么特权？比如，VIP售后群，VIP专属客服，VIP专属活动等等，只要你想，这里都可以写。<br><br>注意：此处填写的内容，会员等级页面即可显示",
                            required: true,
                            uploadUrl: '/user/upload',
                            height: 130
                        },
                        {
                            title: "权益内容",
                            name: "privilege_content",
                            type: "editor",
                            placeholder: "请填写会员权益内容",
                            tips: "这里的权益内容，只会当会员成为这个等级后，才看得到，比如可以留一些群号，或者一些特权的使用方式等等。",
                            required: true,
                            uploadUrl: '/user/upload',
                            height: 130
                        },
                        {
                            title: "升级要求",
                            name: "upgrade_requirements",
                            type: "checkbox",
                            dict: [
                                {id: 0, name: "总消费"},
                                {id: 1, name: "总充值"},
                                {id: 2, name: "总推广人数"},
                                {id: 3, name: "总盈利"}
                            ],
                            default: requirementValues,
                            change: (form, val, selected) => {
                                val = parseInt(val);
                                switch (val) {
                                    case 0:
                                        selected ? form.show('total_consumption_amount') : form.hide('total_consumption_amount');
                                        break;
                                    case 1:
                                        selected ? form.show('total_recharge_amount') : form.hide('total_recharge_amount');
                                        break;
                                    case 2:
                                        selected ? form.show('total_referral_count') : form.hide('total_referral_count');
                                        break;
                                    case 3:
                                        selected ? form.show('total_profit_amount') : form.hide('total_profit_amount');
                                        break;
                                }
                            }
                        },
                        {
                            title: "总消费达到",
                            name: "total_consumption_amount",
                            type: "input",
                            placeholder: "总消费满足该金额则可以升级",
                            hide: !requirements.hasOwnProperty("total_consumption_amount")
                        },
                        {
                            title: "总充值达到",
                            name: "total_recharge_amount",
                            type: "input",
                            placeholder: "总充值满足该金额则可以升级",
                            hide: !requirements.hasOwnProperty("total_recharge_amount")
                        },
                        {
                            title: "总推广人数达到",
                            name: "total_referral_count",
                            type: "input",
                            placeholder: "总推广人数满足该数量则可以升级",
                            hide: !requirements.hasOwnProperty("total_referral_count")
                        },
                        {
                            title: "总盈利达到",
                            name: "total_profit_amount",
                            type: "input",
                            placeholder: "总盈利满足该金额则可以升级",
                            hide: !requirements.hasOwnProperty("total_profit_amount")
                        },
                        {
                            title: "价值排序",
                            name: "sort",
                            type: "input",
                            placeholder: "排序每个等级的价值",
                            tips: "数值越大，代表该等级价值越高，排序不可重复"
                        },
                        {
                            title: "可购买",
                            name: "is_upgradable",
                            type: "switch",
                            placeholder: "启用|关闭"
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

    table = new Table("/user/user/level/get", "#user-level-table");
    table.setUpdate("/user/user/level/save");
    table.disablePagination();
    table.setColumns([
        {
            field: 'name', title: '等级名称', formatter: (name, row) => {
                return format.group(row);
            }
        },
        {field: 'member_count', title: '会员数量'},
        {field: 'upgrade_price', title: '直升价格', formatter: format.amount},
        {
            field: 'upgrade_requirements', title: '升级要求(同时满足)', formatter: val => {
                const requirements = JSON.parse(val);
                let html = '';
                for (const key in upgradeRequirements) {
                    if (requirements.hasOwnProperty(key)) {
                        html += format.badge(upgradeRequirements[key][1] + " " + util.icon('icon-yunsuanfu-dayudengyu') + " " + requirements[key], "acg-badge-h-dodgerblue");
                    }
                }
                return html;
            }
        },
        {
            field: 'default', title: '默认等级', formatter: val => {
                if (val === true) {
                    return format.success(util.icon('icon-chenggong'));
                }
                return '-';
            }
        },
        {field: 'is_upgradable', title: '可购买', type: "switch", reload: true, text: "开启|关闭"},
        {field: 'sort', title: '价值排序', type: 'input', reload: true, width: 85},
        {
            field: 'operation', title: '操作', type: 'button', buttons: [
                {
                    icon: 'icon-biaoge-xiugai',
                    class: 'acg-badge-h-dodgerblue',
                    click: (event, value, row, index) => {
                        modal(util.icon("icon-a-xiugai2") + "<space></space> 修改等级", row);
                    }
                },
                {
                    icon: 'icon-shanchu1',
                    class: 'acg-badge-h-red',
                    click: (event, value, row, index) => {
                        message.ask("是否删除该等级？", () => {
                            util.post("/user/user/level/del", {id: row.id}, () => {
                                table.refresh();
                                layer.msg("删除成功");
                            });
                        });
                    }
                }
            ]
        },
    ]);
    table.render();
    $('.add-user-level').click(() => {
        modal(util.icon("icon-tianjia") + "<space></space>添加等级");
    });
}();