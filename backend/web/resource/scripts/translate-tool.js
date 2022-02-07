var TranslateTool = function () {
    function toTranslate(el) {
        var category = $(el).data("category"),
            message = $(el).data("message"),
            language = $(el).data("language");
        if ((tranmessage = prompt("[" + language + "] " + category + " - " + message, message)) !== null) {
            $.callAJAX({
                url: '/i18n/translation/translate',
                method: 'POST',
                data: {
                    category: category,
                    message: message,
                    language: language,
                    tranmessage: tranmessage,
                },
                callbackSuccess: function (res) {
                    console.log(res);
                    if (res.success) {
                        $(el).html(tranmessage);
                    } else {
                        toastr.error("Error, please check!");
                    }
                },
                callbackFail: function (res, status) {
                }
            });
        }
    };
    var toPrepare = function (el) {
        var transEl = null;
        if ($(el).attr("placeholder") != undefined && $(el).attr("placeholder") != "") {
            try {
                transEl = $($(el).attr("placeholder"));
            } catch (ex) {
                transEl = null;
            }
        } else if ($(el).data("placeholder") != undefined && $(el).data("placeholder") != "") {
            try {
                transEl = $($(el).data("placeholder"));
            } catch (ex) {
                transEl = null;
            }
        }
        if (transEl != null) {
            try {
                transEl.addClass("form-control");
                // transEl.on("click", function () {
                //     toTranslate(this);
                // });
                // parentEl.html(transEl);
                el.replaceWith($(transEl)[0]);
            } catch (ex) {
            }
        }
    };

    return {
        init: function () {
            console.log("console log translation");
            $("input").each(function () {
                toPrepare(this);
            });
            $("textarea").each(function () {
                toPrepare(this);
            });
            $("select").each(function () {
                toPrepare(this);
            });
            $("span.text-translate").on("click", function () {
                toTranslate(this);
                return false;
            });
        },
    };
}();