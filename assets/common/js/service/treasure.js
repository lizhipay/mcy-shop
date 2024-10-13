const treasure = new class Treasure {
    show(item) {
        const title = util.icon("icon-dingdan2", "icon-18px") + `<span class="text-success acg-bold">${item.item.name}</span>` + ` <span class="text-danger">(${item.sku.name})</span>` + " 的宝贝内容";
        if (item.render === false) {
            component.popup({
                tab: [
                    {
                        name: title,
                        form: [
                            {
                                name: "treasure",
                                type: "textarea",
                                hide: true,
                                placeholder: "无",
                                default: item.treasure,
                                height: 200,
                                disabled: true,
                                complete: (popup, val, dom) => {
                                    dom.parent().parent().parent().parent().css("padding", "0px");
                                    //dom.get(0).style.setProperty("border-radius", "0 0 25px 25px", "important");

                                    dom.get(0).style.setProperty("background-color", "#fff", "important");
                                    dom.get(0).style.setProperty("border", "none", "important");
                                    dom.get(0).style.setProperty("overflow", "hidden", "important");
                                    dom.get(0).style.setProperty("resize", "none", "important");
                                    dom.parent().get(0).style.setProperty("padding", "0", "important");
                                    // dom.parent().parent().parent().get(0).style.setProperty("left", "0", "important");
                                    dom.get(0).style.setProperty("width", "100%", "important");
                                    // dom.css('height', (dom.parent().parent().parent().parent().parent().height()) + "px");
                                    dom.parent().parent().fadeIn("slow");
                                    dom.parent().parent().parent().parent().parent().css('overflow', 'hidden');
                                    dom.parent().parent().get(0).style.setProperty("margin", "0", "important");
                                    dom.parent().get(0).style.setProperty("left", "0", "important");

                                    dom.get(0).style.setProperty("overflow", "hidden", "important");
                                    dom.get(0).style.setProperty("overflow-y", "scroll", "important");
                                    dom.get(0).style.setProperty("scrollbar-width", "none", "important");
                                    dom.get(0).style.setProperty("-ms-overflow-style", "none", "important");
                                }
                            },
                            {
                                title: false,
                                name: "message",
                                type: "custom",
                                hide: !item.message,
                                complete: (form, dom) => {
                                    dom.parent().get(0).style.setProperty("margin", "0", "important");
                                    dom.get(0).style.setProperty("left", "0", "important");

                                    dom.html(`<div class="handover-line"></div><div class="leave-message">${item.message}</div>`);
                                }
                            },
                        ]
                    }
                ],
                maxmin: false,
                width: "580px",
                autoPosition: true,
                content: {
                    css: {
                        height: "auto",
                    }
                },
                height: "auto",
                shadeClose: true
            });
        } else {
            component.popup({
                tab: [
                    {
                        name: title,
                        form: [
                            {
                                title: false,
                                name: "custom",
                                type: "custom",
                                complete: (form, dom) => {
                                    dom.parent().parent().parent().parent().parent().css("overflow", "hidden");
                                    dom.parent().parent().parent().css("padding", "0px");
                                    dom.parent().css("margin-bottom", "0px");
                                    dom.html(item.treasure);
                                }
                            },
                        ]
                    }
                ],
                maxmin: false,
                width: "580px",
                autoPosition: true,
                content: {
                    css: {
                        height: "auto",
                    }
                },
                height: "auto",
                shadeClose: true
            });
        }
    }
}