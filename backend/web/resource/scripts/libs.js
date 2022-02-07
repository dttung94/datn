(function ($) {
    jQuery.fn.extend({
        isExist: function () {
            if (this.length > 0) {
                return true;
            } else {
                return false;
            }
        },
        countDown2Redirect: function (options) {
            var
                self = this,
                seconds = parseInt($(this).html()),
                redirectUrl = $(this).data("redirect-url");

            function toCountDown(seconds, cbDone) {
                if (seconds == 0) {
                    cbDone();
                } else {
                    setTimeout(function () {
                        seconds = seconds - 1;
                        $(self).html(seconds);
                        toCountDown(seconds, cbDone);
                    }, 1000);
                }
            }

            toCountDown(seconds, function () {
                window.location.href = redirectUrl;
            });
            return this;
        },
        size: function () {
            return this.length;
        }
    });

    jQuery.callAJAX = jQuery.fn.callAJAX = function (config, isShowProcess = true) {
        var request;

        function checkIsURL(url) {
            var pattern = new RegExp('^(https?:\\/\\/)?' + // protocol
                '((([a-z\\d]([a-z\\d-]*[a-z\\d])*)\\.?)+[a-z]{2,}|' + // domain name
                '((\\d{1,3}\\.){3}\\d{1,3}))' + // OR ip (v4) address
                '(\\:\\d+)?(\\/[-a-z\\d%_.~+]*)*' + // port and path
                '(\\?[;&a-z\\d%_.~+=-]*)?' + // query string
                '(\\#[-a-z\\d_]*)?$', 'i'); // fragment locator
            return pattern.test(url);
        }

        config = config || {
                url: '',
                method: 'POST',
                data: {},
                callbackSuccess: function (res) {
                },
                callbackFail: function (res, status) {
                }
            };

        config.method = config.method || 'POST';
        config.data = config.data || {};
        config.callbackSuccess = config.callbackSuccess || function (res) {
            };
        config.callbackFail = config.callbackFail || function (res, status) {
            };
        config.dataType = config.dataType || 'json';

        if (!checkIsURL(config.url)) {
            if (window.homeUrl) {
                config.url = window.homeUrl + config.url;
            }
        }
        var ajaxConfig = {
            url: config.url,
            type: config.method,
            data: config.data,
            processData: config.processData,
            contentType: config.contentType,
            dataType: config.dataType
        };
        if (isShowProcess) {
            Metronic.blockUI({
                animate: true,
                zIndex: 9999
            });
        }
        request = $.ajax(ajaxConfig);
        request.done(function (res) {
            config.callbackSuccess(res);
            if (isShowProcess) {
                Metronic.unblockUI();
            }
        });
        request.fail(function (res, status) {
            config.callbackFail(res, status);
            if (isShowProcess) {
                Metronic.unblockUI();
            }
        });
    }
})(jQuery);

Number.prototype.formatMoney = function (c = 0, d = ".", t = ",", u = "円") {
    var n = this,
        c = isNaN(c = Math.abs(c)) ? 2 : c,
        d = d == undefined ? "." : d,
        t = t == undefined ? "," : t,
        u = u == undefined ? "" : u,
        s = n < 0 ? "-" : "",
        i = String(parseInt(n = Math.abs(Number(n) || 0).toFixed(c))),
        j = (j = i.length) > 3 ? j % 3 : 0;
    return s + (j ? i.substr(0, j) + t : "") + i.substr(j).replace(/(\d{3})(?=\d)/g, "$1" + t) + (c ? d + Math.abs(n - i).toFixed(c).slice(2) : "") + " " + u;
};

String.prototype.isImageExtension = function () {
    var arrayExtension = [
        "jpg",
        "png",
        "gif",
        "jpeg",
        "bmp"
    ];
    var ext = this.split('.')[this.split('.').length - 1];
    ext = ext.toLowerCase();
    return (arrayExtension.indexOf(ext) != -1);
};

String.prototype.isFileExtension = function () {
    var arrayExtension = [
        "pdf",
        "xls",
        "xlsx",
        "doc",
        "docx"
    ];
    var ext = this.split('.')[this.split('.').length - 1];
    ext = ext.toLowerCase();
    return (arrayExtension.indexOf(ext) != -1);
};

String.prototype.linkify = function () {
    // http://, https://, ftp://
    var urlPattern = /\b(?:https?|ftp):\/\/[a-z0-9-+&@#\/%?=~_|!:,.;]*[a-z0-9-+&@#\/%=~_|]/gim;
    // www. sans http:// or https://
    var pseudoUrlPattern = /(^|[^\/])(www\.[\S]+(\b|$))/gim;
    // Email addresses
    var emailAddressPattern = /[\w.]+@[a-zA-Z_-]+?(?:\.[a-zA-Z]{2,6})+/gim;
    return this
        .replace(urlPattern, '<a href="$&" target="_blank">$&</a>')
        .replace(pseudoUrlPattern, '$1<a href="http://$2">$2</a>')
        .replace(emailAddressPattern, '<a href="mailto:$&">$&</a>');
};

String.prototype.removeHtmlTag = function () {
    var rex = /(<([^>]+)>)/ig;
    return this.replace(rex, "");
};

String.prototype.nl2br = function () {
    return this.replace(RegExp("\n", "g"), "<br>")
};

String.prototype.removeSpecialChar = function () {
    return this.replace(/[^a-zA-Z0-9]/g, '_').toLocaleLowerCase();
};

String.prototype.replaceAll = function (search, replacement) {
    var target = this;
    return target.replace(new RegExp(search, 'g'), replacement);
};

String.prototype.time2milliseconds = function () {
    var t = this;
    return (Number(t.split(':')[0]) * 60 * 60 + Number(t.split(':')[1]) * 60 + Number(t.split(':')[2])) * 1000;
};

String.prototype.truncate = function (length, ending) {
    if (this.length > length) {
        if (length == null) {
            length = 100;
        }
        if (ending == null) {
            ending = '...';
        }
        return this.substring(0, length - ending.length) + ending;
    } else {
        return this.toString();
    }
};

String.prototype.parseDateTime = function (format) {
    var datetime = "";
    if (this == "" || this == null) {
        datetime = "";
    } else if (format == null) {
        datetime = moment(this).calendar(null, {
            sameDay: '[Today]',
            nextDay: '[Tomorrow]',
            nextWeek: 'dddd',
            lastDay: '[Yesterday]',
            lastWeek: '[Last] dddd',
            sameElse: 'DD-MMM-YYYY'
        });
    } else {
        datetime = moment(this).tz('Asia/Ho_Chi_Minh').format(format);
    }
    return datetime;
};

Number.prototype.formatFileSize = function () {
    var b = this;
    if (b > 1024) {
        var kb = Math.round(parseFloat(b / 1024) * 100) / 100;
        if (kb > 1024) {
            var mb = Math.round(parseFloat(kb / 1024) * 100) / 100;
            return "(" + mb + "MB)";
        }
        return "(" + kb + "KB)";
    }
    return "(" + b + " B)";
};

Array.prototype.dynamicSort = function (property) {
    var sortOrder = 1;
    if (property[0] === "-") {
        sortOrder = -1;
        property = property.substr(1);
    }
    return this.sort(function (a, b) {
        var result = (a[property] < b[property]) ? -1 : (a[property] > b[property]) ? 1 : 0;
        return result * sortOrder;
    });
};

Array.prototype.toObject = function (key) {
    var data = {};
    this.map(function (e) {
        data[e[key]] = e;
    });
    return data;
};

Array.prototype.existItem = function (item) {
    return this.indexOf(item) != -1;
};

Array.prototype.isEmpty = function () {
    return this.length == 0;
};

Array.prototype.removeItem = function (item) {
    if (this.existItem(item)) {
        return this.splice(this.indexOf(item), 1);
    }
    return false;
};

Object.prototype.hasOwnProperty = function (property) {
    return this[property] !== undefined;
};