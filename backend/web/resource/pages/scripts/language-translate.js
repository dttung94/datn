var LanguageTranslate = function () {
    var init = function () {
        $("#translate_modal").draggable({
            handle: ".modal-header"
        });
    };
    var openTranslate = function (el) {
        $('#translate_message').html($(el).attr("data-message"));

        $('#translate_id').val($(el).attr("data-id"));
        $('#translate_language').val($(el).attr("data-language"));
        $('#translate_translation').val($(el).attr("data-translation"));

        $('#translate_modal').modal('show');
    };
    var saveTranslate = function () {
        var params = {
            'id': $('#translate_id').val(),
            'language': $('#translate_language').val(),
            'translation': $('#translate_translation').val()
        };
        $.callAJAX({
            url: "/i18n/translation/save",
            method: 'POST',
            data: params,
            callbackSuccess: function (data) {
                $('#translate_modal').modal('hide');
                $.pjax.reload({container: '#grid_view_language_message'});
            },
            callbackFail: function (status, message) {
            }
        });
    };
    var autoTranslate = function () {
        var params = {
            'key': 'AIzaSyCCCLdyP6YIItJ0Lea_bUpzN3NDpAZfis4', //GOOGLE API KEY
            'source': 'en',
            'target': $('#translate_language').val(),
            'q': $('#translate_translation').val()
        };
        $.callAJAX({
            url: "https://www.googleapis.com/language/translate/v2",
            method: 'GET',
            data: params,
            callbackSuccess: function (res) {
                if (res.data && res.data.translations) {
                    $("#translate_translation").val(res.data.translations[0].translatedText);
                } else {
                }
            },
            callbackFail: function (res, status) {
            }
        });
    };
    var deleteSource = function (id) {
        if (confirm("Do you want to delete?")) {
            $.callAJAX({
                url: "/i18n/translation/delete",
                method: 'GET',
                data: {
                    id: id
                },
                callbackSuccess: function (res) {
                    if (res.success) {
                        $.pjax.reload({container: '#grid_view_language_message'});
                    } else {
                    }
                },
                callbackFail: function (res, status) {
                }
            });
        }
    };
    return {
        init: function () {
            init();
        },
        openTranslate: function (el) {
            openTranslate(el)
        },
        saveTranslate: function (url) {
            saveTranslate(url);
        },
        autoTranslate: function () {
            autoTranslate();
        },
        deleteSource: function (id) {
            deleteSource(id);
        }
    };
}();