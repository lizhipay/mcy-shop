const component = new class Component {
    isRowDetailOpen($table, index) {
        let $tr = $table.find('tr[data-index="' + index + '"]');
        let $detailView = $tr.next('.detail-view');
        return $detailView.length > 0;
    }

    /**
     * url : 拉取數據的地址
     * pageSize : 煤业显示的数量
     * pageList : [15, 25, 50, 100] - 页码
     * singleSelect 单选：true 还是多选：false
     * columns ：字段
     * private: 私密通讯
     */
    table(opt = {}) {
        let queryParams = null;
        let table = $(opt.container);
        let secret = CryptoJS.MD5(new Date().getTime().toString()).toString();
        opt.private = opt.private ?? true;
        let isDetail = opt.detail ?? false;
        let isToolbar = false;
        let form = layui.form;
        let unique = util.generateRandStr(8);
        table.append('<div class="' + unique + '-query"></div>');
        let toolbarInstance = $('.' + unique + '-query');
        let isSearch = false;
        let isPagination = opt.hasOwnProperty("pagination") ? opt.pagination : true;
        let search = null;

        if (opt.hasOwnProperty('search')) {
            isToolbar = true;
            isSearch = true;
            search = new Search(toolbarInstance, opt.search, data => {
                table.bootstrapTable('refresh', {
                    silent: false, pageNumber: 1, query: data
                });
            });
        }

        if (opt.hasOwnProperty("state")) {
            isToolbar = true;
            toolbarInstance.append('<div class="table-switch-state ' + (isSearch ? 'mt-26' : '') + '"><button type="button" class="active">' + i18n("全部") + '</button></div>');
            let tableStateInstance = toolbarInstance.find('.table-switch-state');
            _Dict.advanced(opt.state.dict, res => {
                res.forEach(state => {
                    let $button = $(`<button type="button" data-value="${state.id}">${util.plainText(state.name)}</button>`);
                    $button.click(function () {
                        $("." + unique + '-query .table-switch-state button').removeClass("active");
                        $(this).addClass("active");
                        let value = $(this).attr("data-value");
                        let data = {};
                        if (value !== undefined) {
                            data["equal-" + opt.state.field] = value;
                        } else {
                            data["equal-" + opt.state.field] = "";
                        }
                        table.bootstrapTable('refresh', {silent: false, pageNumber: 1, query: data});
                    })
                    tableStateInstance.append($button);
                });
            });
        }

        if (isDetail) {
            opt.columns.unshift({
                field: 'detail_view',
                title: "",
                width: 55,
                edit: 'button', dict: [{
                    icon: 'icon-youce',
                    class: "detail-view",
                    click: (event, value, row, index) => {
                        let dom = $(event.currentTarget);
                        if (!this.isRowDetailOpen(table, index)) {
                            table.bootstrapTable('expandRow', index);
                            dom.fadeOut(100, function () {
                                dom.html(util.icon("icon-xiala")).fadeIn(100);
                            });
                        } else {
                            table.bootstrapTable('collapseRow', index);
                            dom.fadeOut(100, function () {
                                dom.html(util.icon("icon-youce")).fadeIn(100);
                            });
                        }
                    }
                }]
            });
        }

        //预处理
        for (const index in opt.columns) {
            let column = opt.columns[index];
            let hasDict = column.hasOwnProperty("dict");
            let hasEdit = column.hasOwnProperty("edit");
            let tableReload = column.reload ? 'reload="true"' : "";
            column.title && (opt.columns[index]["title"] = i18n(column.title));

            if (column.hasOwnProperty("sort") && column.sort === true) {
                opt.columns[index]["title"] = column.title + " <span style='cursor: pointer;' data-field='" + column.field + "' class='btn-sort'><svg class='mcy-icon icon-14px' aria-hidden='true'><use xlink:href='#icon-paixu'></use></svg></span>";
            }

            //字典
            if (hasDict && !hasEdit) {
                opt.columns[index].formatter = (val, item) => {
                    let result = _Dict.result(column.dict, val);
                    if (result != undefined) {
                        return result;
                    }
                    let uuid = util.generateRandStr(10);
                    _Dict.advanced(column.dict, res => {
                        res.forEach(v => {
                            if (v.id == val) {
                                $('.' + uuid).parent("td").html(v.name);
                            }
                        });
                    })
                    return util.icon("icon-jiazai", "icon-spin " + uuid);
                }
            } else if (hasEdit && !hasDict) {
                switch (column.edit) {
                    case "text":
                        opt.columns[index].formatter = (val, item) => {
                            if (val === "" || val === undefined || val === null) {
                                val = "-";
                            }
                            return '<input class="metadata-text" data-field="' + column.field + '" data-id="' + item.id + '" type="text" value="' + val + '"  ' + tableReload + '>';
                        }
                        break;
                    case "switch":
                        opt.columns[index].formatter = (val, item) => {
                            let layText = column.text ? ' lay-text="' + column.text + '" ' : '';
                            let layClass = column.text ? 'layui-switch-text' : '';
                            let checked = val == 1 ? "checked" : '';
                            return '<div class="layui-form ' + layClass + '"><input ' + checked + ' data-field="' + column.field + '" data-id="' + item.id + '" lay-filter="' + unique + '-switch" type="checkbox" lay-skin="switch" ' + layText + ' ' + tableReload + '></div>';
                        }
                        break;
                }
            } else if (hasDict && hasEdit) {
                switch (column.edit) {
                    case "select":
                        opt.columns[index].formatter = (val, item) => {
                            let html = '<select lay-ignore class="metadata-select" data-field="' + column.field + '" data-id="' + item.id + '" ' + tableReload + '>'
                            _Dict.advanced(column.dict, res => {
                                res.forEach(dt => {
                                    html += '<option  value="' + dt.id + '" ' + (val === dt.id ? 'selected' : '') + '>' + dt.name + '</option>';
                                });
                            })
                            return html + '</select>';
                        }
                        break;
                    case "button":
                        let events = {};
                        let html = '';
                        column.dict.forEach((s, i) => {
                            s.title && (s.title = i18n(s.title));
                            let setKey = unique + "-button-hover-" + i;
                            let hide = s.hide ? ' hide ' : '';
                            // html += '<button type="button" class="btn btn-sm ' + hide + s.class + ' me-1 mb-1 index-' + i + '"><svg class="mcy-icon icon-15px" aria-hidden="true"><use xlink:href="#' + s.icon + '"></use></svg> <span class="btn-title">' + (s.title ?? "") + '</span></button>';
                            html += `<button type="button" class="btn btn-sm ${hide + (s.class ?? "")} me-1 mb-1 index-${i}"><svg class="mcy-icon icon-15px" aria-hidden="true"><use xlink:href="#${s.icon}"></use></svg> <span class="btn-title">${s.title ?? ""}</span></button>`;
                            events['click .index-' + i] = s.click;
                            events['mouseenter .index-' + i] = function (event, value, row, index) {
                                if (s.tips) {
                                    cache.set(setKey, layer.tips(s.tips, event.currentTarget, {
                                        tips: [1, '#501536'], time: 0
                                    }));
                                }
                                s.mouseenter && s.mouseenter(event, value, row, index);
                            };
                            events['mouseleave .index-' + i] = function (event, value, row, index) {
                                if (s.tips) {
                                    layer.close(cache.get(setKey));
                                }
                                s.mouseleave && s.mouseleave(event, value, row, index);
                            };
                        });
                        opt.columns[index].formatter = (val, item) => {
                            let temp = html;
                            column.dict.forEach((s, i) => {
                                let show = s.show ? s.show(item) : true;
                                if (!show) {
                                    let regex = new RegExp(`<button type="button" class="[^"]* index-${i}">[\\s\\S]*?<\/button>`, 'g');
                                    temp = temp.replace(regex, '');
                                } else {
                                    temp = `<span data-id="${item.id}">${temp}</span>`;
                                }
                            });
                            return temp === "" ? "<span class='text-gray'>-</span>" : temp;
                        }
                        opt.columns[index].events = events;
                        break;
                }
            } else if (column.hasOwnProperty('render')) {
                if (typeof column.render == "function") {
                    opt.columns[index].formatter = column.render;
                } else {
                    switch (column.render) {
                        case "image":
                            let circle = column.style ?? '';
                            opt.columns[index].formatter = (val, item) => {
                                return '<img style="' + circle + '" class="render-image"  src="' + val + '" data-id="' + item.id + '">';
                            }
                            break;
                    }
                }
            } else if (!hasDict && !hasEdit && !column.hasOwnProperty('render')) {
                opt.columns[index].formatter = function (content, item) {
                    if (content) {
                        return i18n(content);
                    }
                    return content;
                }
            }
        }


        let options = {
            pageSize: opt.pageSize ?? 10,
            pageList: opt.pageList ?? [10, 20, 50, 100, 500, 1000],
            showRefresh: false,
            cache: true,
            iconsPrefix: "fa",
            showToggle: false,
            toolbar: isToolbar ? '.' + unique + '-query' : '',
            cardView: false,
            pagination: isPagination,
            pageNumber: 1,
            singleSelect: opt.singleSelect ?? false,
            sidePagination: 'server',
            contentType: "text/plain",
            dataType: opt.private ? "text" : "json",
            processData: false,
            queryParamsType: 'limit',
            detailViewIcon: false,
            detailView: isDetail,
            ajaxOptions: () => {
                return {
                    headers: {
                        Secret: secret, Signature: util.generateSignature(queryParams, secret)
                    }
                };
            },
            queryParams: (params) => {
                params.page = (params.offset / params.limit) + 1;
                if (queryParams) {
                    for (const key in params) {
                        queryParams[key] = params[key];
                    }
                } else {
                    queryParams = params;
                }

                //自动搜索功能
                let searchData = search?.getData();
                if (searchData) {
                    for (const dataKey in searchData) {
                        if (searchData[dataKey] !== "") {
                            queryParams[dataKey] = searchData[dataKey];
                        }
                    }
                }


                util.debug("POST(↑):" + opt.url, "#ff4f33", queryParams);
                return opt.private ? util.encrypt(JSON.stringify(queryParams), secret.substring(0, 16)) : queryParams;
            },
            responseHandler: (res, xhr) => {
                search && search.resetButton();
                let response = res;
                if (typeof res != "object" && opt.private) {
                    try {
                        response = JSON.parse(util.decrypt(res, xhr.getResponseHeader('Secret').substring(0, 16)));
                    } catch (e) {
                        return {
                            "total": 0, "rows": []
                        }
                    }
                }

                util.debug("POST(↓):" + opt.url, "#0bbf4a", response);

                opt.response && opt.response(response);

                return {
                    "total": response.data.total, "rows": response.data.list
                }
            },
            detailFormatter: (index, item, element) => {
                if (!isDetail) {
                    return '';
                }

                if (typeof opt.detail == "function") {
                    return opt.detail(item);
                } else if (typeof opt.detail == "object") {
                    let html = '<table class="open-detail-view"><tbody>';
                    opt.detail.forEach(det => {
                        let val = (det.formatter ? det.formatter(item[det.field], item) : item[det.field] ?? "-");
                        if (val != "") {
                            html += '<tr><td>' + det.title + '</td><td>' + val + '</td></tr>';
                        }
                    });
                    html += '</tbody></table>';
                    return html;
                }
                return '';
            },
            columns: opt.columns ?? []
        };

        if (opt.hasOwnProperty("tree")) {
            options = {...options, ...opt.tree};
            options.onPostBody = () => {
                let columns = table.bootstrapTable('getOptions').columns;
                if (columns && columns[0][1].visible) {
                    table.treegrid({
                        treeColumn: opt.tree.treeShowFieldIndex ?? 1,
                        onChange: function () {
                            table.bootstrapTable('resetView');
                        }
                    })
                }
                typeof opt.complete == "function" && opt.complete(table, unique);
            }
        } else {
            if (typeof opt.complete == "function") {
                options.onPostBody = () => {
                    opt.complete(table, unique);
                };
            }
        }

        if (opt.url) {
            options.url = opt.url;
            options.method = "post";
        } else if (opt.data) {
            options.data = opt.data;
        }

        table.bootstrapTable(options);

        table.on('load-success.bs.table', function (data) {
            table.addClass(unique);

            $("." + unique + ' .metadata-text').change(function () {
                component.updateDatabase(opt.saveUrl, this.value, $(this).attr("data-field"), $(this).attr("data-id"), table, $(this).attr("reload"));
            });

            $("." + unique + ' .metadata-select').change(function () {
                component.updateDatabase(opt.saveUrl, this.value, $(this).attr("data-field"), $(this).attr("data-id"), table, $(this).attr("reload"));
            });

            form.on('switch(' + unique + '-switch)', function (data) {
                component.updateDatabase(opt.saveUrl, data.elem.checked ? 1 : 0, $(data.elem).attr("data-field"), $(data.elem).attr("data-id"), table, $(data.elem).attr("reload"));
            });

            $("." + unique + ' .render-image').click(function () {
                let size = 400;
                let imageUrl = $(this).attr("src");
                layer.open({
                    type: 1,
                    title: false,
                    closeBtn: 0,
                    anim: 5,
                    area: 'auto',
                    shadeClose: true,
                    content: '<img  src="' + imageUrl + '" style="width: auto;">'
                });
            });


            //排序组件
            let btnSort = $("." + unique + ' .btn-sort');
            btnSort.off('click');
            btnSort.click(function () {
                let field = $(this).attr("data-field");
                let key = unique + "_sort_" + field;
                let temp = cache.has(key) ? parseInt(cache.get(key)) : 0;
                if (temp >= 3) {
                    temp = 0;
                }
                let rule = ["asc", "desc", ""];
                let css = ["icon-paixubeifen", "icon-paixubeifen2", "icon-paixu"];
                $(this).html('<svg class="mcy-icon icon-14px" aria-hidden="true"><use xlink:href="#' + css[temp] + '"></use></svg>');
                table.bootstrapTable('refresh', {
                    silent: false, pageNumber: 1, query: {sort_rule: rule[temp], sort_field: field}
                });
                temp++;
                cache.set(key, temp);
            });
            form.render();
        });


        $("." + unique + '-query .table-switch-state button').click(function () {
            $("." + unique + '-query .table-switch-state button').removeClass("active");
            $(this).addClass("active");
            let value = $(this).attr("data-value");
            let data = {};
            if (value !== undefined) {
                data["equal-" + opt.state.field] = value;
            } else {
                data["equal-" + opt.state.field] = "";
            }
            table.bootstrapTable('refresh', {
                silent: false, pageNumber: 1, query: data
            });
        });


        opt.deleteButton && ($(opt.deleteButton).click(() => {
            let data = this.idObjToList(table.bootstrapTable('getSelections'));
            if (data.length == 0) {
                message.alert("请勾选您希望删除的数据项", "error");
                return;
            }
            this.deleteDatabase(opt.deleteUrl, data, () => {
                table.bootstrapTable('refresh', {silent: true});
            });
        }));

        table.getSelectionIds = () => {
            return this.idObjToList(table.bootstrapTable('getSelections'));
        }

        table.refresh = (silent = true) => {
            table.bootstrapTable('refresh', {silent: silent});
        };

        table.getSearchData = () => {
            return search.getData();
        }

        table.getState = () => {
            let value = $("." + unique + '-query .table-switch-state button[class=active]').attr("data-value");

            if (value === undefined) {
                value = "";
            }
            return {field: opt.state.field, value: value};
        }

        return table;
    }

    /**
     *
     * @param url
     * @param value
     * @param field
     * @param id
     */
    updateDatabase(url, value, field, id, table = null, reload = false) {
        let data = {};
        data[field] = value;
        data["id"] = id;
        util.post(url, data, res => {
            message.success("已更新 (｡•ᴗ-)");
            table && reload && table.bootstrapTable('refresh', {silent: true});
        });
    }

    deleteDatabase(url, list, done = null) {
        message.ask("一旦数据被遗弃，您将无法恢复它！", () => {
            util.post(url, {list: list}, res => {
                message.alert('您选择的数据已被系统永久删除。', 'success');
                done && done(res);
            });
        });
    }

    /**
     *
     * @param opt
     */
    popup(opt = {}) {
        const submitTab = getVar("HACK_SUBMIT_TAB"), submitForm = getVar("HACK_SUBMIT_FORM");

        if (submitTab instanceof Array) {
            submitTab.forEach(tmp => {
                if (tmp.submit == opt.submit) {
                    opt?.tab?.push(evalResults(tmp.code));
                }
            });
        }

        if (submitForm instanceof Array) {

            submitForm.forEach(tmp => {
                if (tmp.submit == opt.submit) {
                    for (let i = 0; i < opt?.tab?.length; i++) {
                        const forms = opt?.tab[i]?.form;
                        for (let j = 0; j < forms?.length; j++) {
                            const form = forms[j];
                            if (form.name == tmp.field) {
                                if (tmp.direction === "after") {
                                    opt?.tab[i]?.form?.splice(j + 1, 0, evalResults(tmp.code));
                                } else {
                                    opt?.tab[i]?.form?.splice(j, 0, evalResults(tmp.code));
                                    j++;
                                }
                            }
                        }
                    }
                }
            });
        }

        let form = new Form(opt);
        let tab = form.getTab();
        let area = '680px';

        if (opt.width && opt.height) {
            area = [opt.width, opt.height];
        } else if (opt.width) {
            area = opt.width;
        }

        if (!util.isPc()) {
            area = ["100%", "100%"];
        }

        //弹窗参数
        let openOption = {
            shade: opt.shade ?? 0.3,
            btn: opt.submit ? [(opt.confirmText ? i18n(opt.confirmText) : null) ?? '<svg class="mcy-icon icon-14px" aria-hidden="true"><use xlink:href="#icon-jinduquerentubiao"></use></svg><space></space>' + i18n("保存"), '<svg class="mcy-icon icon-14px" aria-hidden="true"><use xlink:href="#icon-quxiao-err1"></use></svg><space></space>' + i18n('取消')] : false,
            area: area,
            maxmin: opt.maxmin ?? true,
            closeBtn: opt.closeBtn ?? 1,
            shadeClose: opt.shadeClose ?? false,
            anim: 4,
            yes: (index, lay) => {
                let data = form.getData();
                if (!form.validator()) {
                    return;
                }

                if (typeof opt.submit == "function") {
                    opt.submit(data, index);
                    return;
                }
                opt.submit && (util.post(opt.submit, data, res => {
                    layer.close(index);
                    if (opt.message !== false) {
                        if (!res.msg || res.msg == "success") {
                            message.alert(opt.message ?? '您提交的数据已被系统存储(｡•ᴗ-)_', 'success');
                        } else {
                            message.alert(res.msg, 'success');
                        }
                    }
                    opt.done && opt.done(res, data);
                }, error => {
                    opt.error && opt.error(error);
                    message.alert(error.msg, 'error');
                }));
            },
            success: (lay, layIndex, that) => {
                let contentElem = $(lay).find('.layui-layer-content');

                form.setIndex(layIndex);
                form.registerEvent();
                $('.component-popup.' + form.getUnique()).append('<img src="/assets/common/images/popup.png" class="component-popup-acg">');


                if (opt.content && util.isPc()) {
                    if (opt.content.css) {
                        for (const cssKey in opt.content.css) {
                            contentElem.css(cssKey, opt.content.css[cssKey]);
                        }
                    }
                }

                if (opt.autoPosition && util.isPc()) {
                    this.resizeObserver($(lay).find(".layui-layer-content"), event => {
                        const heightValue = util.getDomHeight($(lay).find(".layui-layer-content")),
                            overflowValue = $(lay).find(".layui-layer-content").css("overflow");

                        if (/^\d+px$/.test(heightValue) && overflowValue == "auto") {
                            $(lay).find(".layui-layer-content").css("height", "auto");
                        }

                        let height = $(lay).find(".layui-layer-content").height() + 60 + 56;
                        if (height > $(window).height()) {
                            const autoHeight = $(window).height() - 155;
                            $(lay).find(".layui-layer-content").css("height", `${autoHeight}px`).css("overflow", "");
                        }

                        layer.iframeAuto(layIndex);
                        that.offset();
                    })
                }

                typeof opt.renderComplete == "function" && opt.renderComplete(form.getUnique(), layIndex);
            },
            end: () => {
                opt.end && opt.end();
            },
            full: (layero, index, that) => {
                let $handle = layero.addClass("border-none");
                $handle.find(".layui-layer-title").addClass("border-none");
                $handle.find(".layui-layer-btn").addClass("border-none");
            },
            restore: (layero, index, that) => {
                let $handle = layero.removeClass("border-none");
                $handle.find(".layui-layer-title").removeClass("border-none");
                $handle.find(".layui-layer-btn").removeClass("border-none");
            }
        };

        if (tab.length === 1) {
            //单选卡
            openOption.type = 1;
            openOption.content = tab[0].content;
            openOption.title = tab[0].title;
            openOption.skin = 'component-popup ' + form.getUnique();
            layer.open(openOption);
        } else {
            //多选卡
            openOption.tab = tab;
            openOption.skin = 'layui-layer-tab component-popup ' + form.getUnique();
            layer.tab(openOption);
        }
    }


    idObjToList(array = []) {
        let list = [];
        array.forEach(item => {
            list.push(item.id);
        });
        return list;
    }


    async loadScript(src) {
        return new Promise((resolve, reject) => {
            const script = document.createElement('script');
            script.src = src;
            script.onload = resolve;
            script.onerror = reject;
            document.body.appendChild(script);
        });
    }

    async run(scripts = []) {
        for (const src of scripts) {
            await this.loadScript(src);
        }
    }


    resizeObserver(element, done) {
        if ('ResizeObserver' in window) {
            let resizeObserver = new ResizeObserver(function (entries) {
                for (let entry of entries) {
                    done && done(entry);
                }
            });
            resizeObserver.observe(element.get(0));
        }
    }

}