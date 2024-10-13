class WidgetUtil {

    static getWidget(widgets) {
        let html = "";

        widgets.forEach(item => {
            switch (item.type) {
                case "select":
                    html += this.createSelect(item);
                    break;
                case "radio":
                    html += this.createRadio(item);
                    break;
                case "text":
                case "password":
                case "number":
                case "textarea":
                    html += this.createTextBox(item);
                    break;
                case "checkbox":
                    html += this.createCheckbox(item);
                    break;
            }
        });

        return html + "";
    }


    static widgetToPopup(widget) {
        let option = {
            title: widget.title,
            name: widget.name,
            placeholder: widget.placeholder
        };
        let map = WidgetUtil.getDataToDict(widget);
        if (widget.regex !== "") {
            option.regex = {
                value: widget.regex,
                message: widget.error
            };
        }

        switch (widget.type) {
            case "text":
                option.type = "input";
                break;
            case "password":
                option.type = "password";
                break;
            case "number":
                option.type = "number";
                break;
            case "select":
                option.type = "select";
                option.dict = map.dict;
                break;
            case "checkbox":
                option.type = "checkbox";
                option.dict = map.dict;
                break;
            case "radio":
                option.type = "radio";
                option.dict = map.dict;
                break;
            case "textarea":
                option.type = "textarea";
                option.height = 200;
                break;
        }
        return option;
    }


    static getDataToDict(widget) {
        if (!widget?.data) {
            return [];
        }
        let options = widget?.data?.trim().split("\r\n");
        if (options.length === 0) {
            return [];
        }
        let list = [], defaults;
        if (widget.type === "checkbox") {
            defaults = [];
        }

        options.forEach(item => {
            let arr = item.trim().split("=");
            if (arr.length === 2) {
                let text = arr[0];
                let para = arr[1].split(",");
                list.push({
                    id: para[0],
                    name: text
                });
                if (para[1] === "default") {
                    if (widget.type === "checkbox") {
                        defaults.push(para[0]);
                    } else {
                        defaults = para[0];
                    }
                }
            }
        });
        return {dict: list, default: defaults};
    }

    /**
     * 创建下拉框
     * @param widget
     */
    static createSelect(widget) {
        const map = this.getDataToDict(widget);

        if (map.dict.length === 0) {
            return "";
        }

        let option = "";


        map.dict.forEach(item => {
            option += `<option value="${item.id}" ${item.id === map.default ? "selected" : ""}>${item.name}</option>`;
        });

        return `<tr class="item-widget"><td class="sku-cate-td"><span class="cate-name">${widget.title}:</span></td><td> <select class="form-select" name="` + widget.name + `"><option>${widget.placeholder}</option>${option}</select></td></tr>`;

    }

    /**
     * 创建单选框
     * @param widget
     * @returns {string}
     */
    static createRadio(widget) {
        const map = this.getDataToDict(widget);

        if (map.dict.length === 0) {
            return "";
        }

        let option = "";

        map.dict.forEach((item, index) => {
            option += `<div class="form-check form-check-inline">
                          <input class="form-check-input" type="radio" id="${widget.name}-${index}" name="${widget.name}" value="${item.id}" ` + (item.id === map.default ? "checked" : "") + `>
                          <label class="form-check-label" for="${widget.name}-${index}">${item.name}</label>
                        </div>`;
        });

        return `<tr class="item-widget"><td class="sku-cate-td"><span class="cate-name">${widget.title}:</span></td><td>${option}</td></tr>`;
    }

    /**
     * 创建文本框，支持：text、password、number、textarea
     * @param widget
     * @returns {string}
     */
    static createTextBox(widget) {
        let text = `<input type="${widget.type}" class="form-control" name="${widget.name}" placeholder="${widget.placeholder}">`;
        if (widget.type === 'textarea') {
            text = `<textarea class="form-control form-control-alt" name="${widget.name}" rows="5" placeholder="${widget.placeholder}"></textarea>`;
        }
        return `<tr class="item-widget"><td class="sku-cate-td"><span class="cate-name">${widget.title}:</span></td><td>${text}</td></tr>`;
    }

    /**
     * 创建多选框
     * @param widget
     * @returns {string}
     */
    static createCheckbox(widget) {
        const map = this.getDataToDict(widget);

        if (map.dict.length === 0) {
            return "";
        }

        let option = "";

        map.dict.forEach((item, index) => {
            option += `<div class="form-check form-check-inline">
         <input class="form-check-input" type="checkbox" id="${widget.name}-${index}" value="${item.id}"  name="${widget.name}[]" ${map.default.includes(item.id) ? "checked" : ""}>
         <label class="form-check-label" for="${widget.name}-${index}">${item.name}</label></div>`;
        });

        return `<tr class="item-widget"><td class="sku-cate-td"><span class="cate-name">${widget.title}:</span></td><td>${option}</td></tr>`;
    }
}