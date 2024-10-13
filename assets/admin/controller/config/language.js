!function () {
    let languages = [];

    const modal = (title, assign = {}) => {
        let forms = []
        if (assign?.language) {
            forms.push({
                title: false,
                name: "zh_cn",
                type: "textarea",
                default: assign.source,
                hide: true
            });
            forms.push({
                title: "原文(中国大陆)",
                name: "source",
                type: "textarea",
                placeholder: "原文",
                default: assign.source,
                height: 50,
                disabled: true
            });
            assign?.language.forEach(item => {
                forms.push({
                    title: item.localCountryName,
                    name: item.code.replaceAll("-", "_").toLowerCase(),
                    type: "textarea",
                    placeholder: "请输入翻译文本",
                    default: item.translate,
                    height: 50,
                    required: true
                });
            });
        } else {
            forms.push({
                title: "原文(中国大陆)",
                name: "zh_cn",
                type: "textarea",
                placeholder: "请输入需要翻译的文本(简体中文(ZH-CN))",
                default: assign.source,
                height: 50,
                required: true,
                regex: {
                    value: /[\u4e00-\u9fa5]+/,
                    message: "原文中没有需要翻译的中文"
                }
            });
            languages.forEach(item => {
                forms.push({
                    title: item.localCountryName,
                    name: item.code.replaceAll("-", "_").toLowerCase(),
                    type: "textarea",
                    placeholder: "请输入翻译文本",
                    height: 60,
                    required: true
                });
            });
        }


        component.popup({
            submit: '/admin/config/language/save',
            tab: [
                {
                    name: title,
                    form: forms
                }
            ],
            assign: assign,
            autoPosition: true,
            height: "auto",

            done: () => {
                table.refresh();
            }
        });
    }

    const table = new Table("/admin/config/language/get", "#translate-language-table");
    table.setColumns([
        {checkbox: true},
        {field: 'source', title: '中文', formatter: util.plainText},
        {
            field: 'language', title: '支持翻译', formatter: language => {
                let html = ``;

                language.forEach(item => {
                    html += format.badge(item.language, "acg-badge-h-dodgerblue");
                });

                return html;
            }
        },
        {
            field: 'operation', title: '操作', type: 'button', buttons: [
                {
                    icon: 'icon-biaoge-xiugai',
                    class: 'acg-badge-h-dodgerblue',
                    tips: "修改翻译",
                    click: (event, value, row, index) => {
                        modal(`${util.icon("icon-a-xiugai2")} 修改翻译`, row);
                    }
                },
                {
                    icon: 'icon-shanchu1',
                    class: 'acg-badge-h-red',
                    tips: "删除翻译",
                    click: (event, value, row, index) => {
                        message.ask(`你确定要删除【<b class="text-danger">${row.source}</b>】的所有国际化吗`, () => {
                            util.post("/admin/config/language/del", {source: [row.source]}, () => {
                                message.success(`【${row.source}】已移除国际化`);
                                table.refresh();
                            });
                        });
                    }
                }
            ]
        },
    ]);

    table.setSearch([
        {title: "关键词", name: "keywords", type: "input"},
    ]);
    table.onResponse(res => {
        languages = res.languages;
    });
    table.render();

    $('.add-translate-language').click(() => {
        modal(`${util.icon("icon-tianjia")} 添加翻译`);
    });


    $(`.del-translate-language`).click(() => {
        let selections = table.getSelections();

        if (selections.length == 0) {
            message.error("请勾选至少1个要删除的国际化原文");
            return;
        }

        let source = selections.map(item => item.source);

        message.ask(`你真的要删除这些勾选的所有国际化内容吗？`, () => {
            util.post("/admin/config/language/del", {source: source}, () => {
                message.success(`已删除勾选的所有国际化内容`);
                table.refresh();
            });
        });
    });

}();