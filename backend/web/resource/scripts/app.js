var App = function () {
    function playAudio(file, vol) {
        var audio = document.getElementById('audio_'+file);
        if (audio) {
            audio.volume = vol;
            setTimeout(function(){
                audio.pause();
                let playPromise = audio.play();
                if (playPromise !== undefined) {
                    playPromise.then(_ => {
                        console.log('audio play success');
                    })
                        .catch(error => {
                            console.log('audio play false');
                        })
                }
            }, 10);
        }
    }

    function init() {
        if ($(".countdown2redirect").isExist()) {
            $(".countdown2redirect").countDown2Redirect();
        }
        if ($("[data-click-url]").isExist()) {
            $("[data-click-url]").on("click", function () {
                window.location.href = $(this).data("click-url");
            });
        }
        if ($(".draggable-modal").isExist()) {
            $(".draggable-modal").draggable({
                handle: ".modal-header"
            });
        }
        if ($('.timepicker-24').isExist()) {
            $('.timepicker-24').timepicker({
                autoclose: true,
                minuteStep: $(this).data("minute-step"),
                showSeconds: false,
                showMeridian: false
            });
        }
        if (jQuery().datepicker) {
            $('.date-picker').datepicker({
                rtl: Metronic.isRTL(),
                orientation: "left",
                autoclose: true,
                todayBtn: true,
                todayHighlight: true,
                format: "yyyy-mm-dd",
            });
        }
        if (jQuery().pulsate) {
            jQuery('.pulsate-regular').each(function () {
                if (!$(this).hasClass("now") && !$(this).hasClass("past")) {
                    $(this).find(".info").pulsate({
                        color: "#bf1c56",
                        reach: 20,                              // how far the pulse goes in px
                        speed: 1500,                            // how long one pulse takes in ms
                        pause: 0,                               // how long the pause between pulses is in ms
                        glow: true,                             // if the glow should be shown too
                        repeat: true,                           // will repeat forever if true, if given a number will repeat for that many times
                        onHover: false                          // if true only pulsate if user hovers over the element
                    })
                }
            });
        }
        toInitChangeStatus();
    }

    function initPjax() {
        var ngRefresh = function (element) {
            var scope = $(element).scope();
            var compile = $(element).injector().get('$compile');
            compile($(element).contents())(scope);
            scope.$apply();
        };
        $(document).on('pjax:success', function (event) {
            console.log("pjax:success", event.target);
            try {
                ngRefresh(event.target);
            } catch ($ex) {
                console.log("Ex", $ex);
            }
            Metronic.initAjax();
            init();
        });
    }

    function toInitChangeStatus() {
        $("input.switch-status").on("switchChange.bootstrapSwitch", function (event, state) {
            var id = $(this).data("id"),
                url = $(this).data("url"),
                pjaxId = $(this).data("pjax-id");
            console.log(id, url, pjaxId);
            $.callAJAX({
                url: url,
                method: 'POST',
                callbackSuccess: function (res) {
                    if (res.success) {
                        toastr.success(res.message);
                        $.pjax.reload({container: '#' + pjaxId});
                    } else {
                        toastr.error(res.message);
                        $.pjax.reload({container: '#' + pjaxId});
                    }
                },
                callbackFail: function (status, message) {
                    toastr.error(status.statusText);
                    $.pjax.reload({container: '#' + pjaxId});
                }
            });
        });
    }

    return {
        init: function () {
            init();
            initPjax();
            setInterval(function () {
                if (typeof(autosize) == "function") {
                    autosize($('textarea.autosizeme'));
                }
                if ($(".maxlength-validate").length > 0) {
                    $(".maxlength-validate").each(function () {
                        $(this).maxlength({
                            limitReachedClass: "label label-danger",
                            alwaysShow: true
                        })
                    });
                }
            }, 1000);
        },
        initAjax: function () {
            init();
        },
        playAudio: function (file, vol) {
            playAudio(file, vol);
        },
    }
}();