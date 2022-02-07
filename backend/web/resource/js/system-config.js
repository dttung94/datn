var imgData = '';
var croppie = '', fileType = '';

function readURL(input) {
    if (input.files && input.files[0]) {
        fileType = input.files[0]["type"].split("/")[1]
        let reader = new FileReader();
        if(input.name == 'logoSite') {
            reader.onload = function (e) {
                imgData = e.target.result;
                croppie = $('#croppie').croppie({
                    viewport: {
                        width: 219,
                        height: 105,
                        type: 'square',
                    },
                    enforceBoundary: true,
                    enableResize: true,
                });
                croppie.croppie('bind', {
                    url : imgData
                });
                $('#value-avatar').val(imgData);
                $('#choose-image').addClass('hidden');
            };
            reader.readAsDataURL(input.files[0]);
        }
        if(input.name == 'logoSiteCharacter') {
            reader.onload = function (e) {
                imgData = e.target.result;
                croppie = $('#croppie-character').croppie({
                    viewport: {
                        width: 128,
                        height: 174,
                        type: 'square',
                    },
                    enforceBoundary: true,
                    enableResize: true,
                });
                croppie.croppie('bind', {
                    url : imgData
                });
                $('#value-avatar-character').val(imgData);
                $('#choose-image-character').addClass('hidden');
            };
            reader.readAsDataURL(input.files[0]);
        }
        if(input.name == 'logoSiteIntro') {
            reader.onload = function (e) {
                imgData = e.target.result;
                croppie = $('#croppie-intro').croppie({
                    viewport: {
                        width: 107,
                        height: 107,
                        type: 'square',
                    },
                    enforceBoundary: true,
                    enableResize: true,
                });
                croppie.croppie('bind', {
                    url : imgData
                });
                $('#value-avatar-intro').val(imgData);
                $('#choose-image-intro').addClass('hidden');
            };
            reader.readAsDataURL(input.files[0]);
        }
    }
}

$(document).on('click','#choose-image', function (e) {
    if (!e.isTrigger) {
        $('input[name="logoSite"]').trigger("click");
    }
});
$(document).on('click','#choose-image-character', function (e) {
    if (!e.isTrigger) {
        $('input[name="logoSiteCharacter"]').trigger("click");
    }
});
$(document).on('click','#choose-image-intro', function (e) {
    if (!e.isTrigger) {
        $('input[name="logoSiteIntro"]').trigger("click");
    }
});

function closeModalUploadImage() {
    if ($('#choose-image').hasClass('hidden') || $('#choose-image-character').hasClass('hidden') || $('#choose-image-intro').hasClass('hidden')) {
        $('#choose-image').removeClass('hidden');
        $('#choose-image-character').removeClass('hidden');
        $('#choose-image-intro').removeClass('hidden');
        $('#value-avatar').val(null);
        $('#value-avatar-character').val(null);
        $('#choose-image-intro').val(null);
        $('input[name="logoSite"]').val(null);
        $('input[name="logoSiteCharacter"]').val(null);
        $('input[name="logoSiteIntro"]').val(null);
    }
    if ($('#croppie').hasClass('croppie-container') || $('#croppie-character').hasClass('croppie-container') || $('#croppie-intro').hasClass('croppie-container')){
        croppie.croppie('destroy');
    }
}

$('#btn-setting-logo').on('click', function () {
    let avatar = $('#value-avatar');
    if (avatar.val() === '') {
        toastr.error('画像を設定してお願いします！');
        return false;
    }
    if (avatar.val().indexOf(';base64') !== -1) {
        croppie.croppie('result', {
            type: 'base64',
            size: {
                width: 219,
                height: 105,
            },
            format: 'png',
        }).then(function (resp) {
            avatar.val(resp);
            $.ajax({
                url: '/system/config/setting-logo-site',
                type: 'POST',
                dataType: 'json',
                data: {
                    logo: resp,
                },
                success: function (res) {
                    console.log(res);
                    if (res.success) {
                        toastr.success(res.message);
                        setTimeout((function() {
                            window.location.reload();
                        }), 1500);
                    } else {
                        toastr.error(res.message);
                    }
                }
            })
        });
    }
});

$('#btn-setting-logo-character').on('click', function () {
    let avatar = $('#value-avatar-character');
    if (avatar.val() === '') {
        toastr.error('画像を設定してお願いします！');
        return false;
    }
    if (avatar.val().indexOf(';base64') !== -1) {
        croppie.croppie('result', {
            type: 'base64',
            size: {
                width: 128,
                height: 174,
            },
            format: 'png',
        }).then(function (resp) {
            avatar.val(resp);
            $.ajax({
                url: '/system/config/setting-character-site',
                type: 'POST',
                dataType: 'json',
                data: {
                    character: resp,
                },
                success: function (res) {
                    console.log(res);
                    if (res.success) {
                        toastr.success(res.message);
                        setTimeout((function() {
                            window.location.reload();
                        }), 1500);
                    } else {
                        toastr.error(res.message);
                    }
                }
            })
        });
    }
});

$('#btn-setting-logo-intro').on('click', function () {
    let avatar = $('#value-avatar-intro');
    if (avatar.val() === '') {
        toastr.error('画像を設定してお願いします！');
        return false;
    }
    if (avatar.val().indexOf(';base64') !== -1) {
        croppie.croppie('result', {
            type: 'base64',
            size: {
                width: 107,
                height: 107,
            },
            format: 'png',
        }).then(function (resp) {
            avatar.val(resp);
            $.ajax({
                url: '/system/config/setting-intro-site',
                type: 'POST',
                dataType: 'json',
                data: {
                    intro: resp,
                },
                success: function (res) {
                    console.log(res);
                    if (res.success) {
                        toastr.success(res.message);
                        setTimeout((function() {
                            window.location.reload();
                        }), 1500);
                    } else {
                        toastr.error(res.message);
                    }
                }
            })
        });
    }
});