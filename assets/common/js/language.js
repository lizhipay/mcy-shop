const language = new class Language {

    constructor() {
        this.pack = {};
        this.queue = {};
        this.preferred = getVar("language");

        if (this.preferred !== "zh-cn") {
            this.loadJSONSync(`/language/pack`);
        }
    }


    output(text) {
        if (this.preferred === "zh-cn") {
            return text;
        }
        if (!this.containsChinese(util.plainText(text))) {
            return text;
        }

        return this.textPars(text);
    }

    textPars(text) {
        const chineseRegex = /[\p{Script=Han}a-zA-Z0-9&;#=。！？，、；：“”‘’（）《》【】\[\]]+/gu;
        return text.replace(chineseRegex, (match) => {
            if (this.containsChinese(match)) {
                //TODO : 该方法需要移除
                /*       util.post({
                           url: "/language/record?t=" + match,
                           loader: false
                       });*/
                const hash = CryptoJS.MD5(match).toString();
                if (this.pack.hasOwnProperty(hash)) {
                    return this.pack[hash];
                }
            }
            return match;
        });
    }

    loadJSONSync(url) {
        let result = localStorage.getItem("language.pack"), async = false;

        if (result) {
            this.pack = JSON.parse(result) ?? {};
            async = true;
        }
        $.ajax({
            url: url,
            dataType: 'json',
            async: async,
            success: data => {
                this.pack = data ?? {};
                localStorage.setItem("language.pack", JSON.stringify(data));
            }
        });
    }


    containsChinese(text) {
        return /[\u4e00-\u9fa5]/.test(text);
    }


    change(language) {
        localStorage.removeItem("language.pack");
        util.setCookie("language", language);
        window.location.reload();
    }

};

