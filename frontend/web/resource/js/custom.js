// Balance height in class maxH
$(document).ready(function () {
    if ($(".maxH")[0]) {
        resizeH(".maxH");
    }
});

$(window).on("resize", function () {
    if ($(".maxH")[0]) {
        resizeH(".maxH");
    }
});

function resizeH(eleH) {
    if ($(window).width() > 767) {
        $(eleH).find(".hTit").css("height", "");
        $(eleH).find(".hBody").css("height", "");
        $(eleH).each(function () {
            var maxH = 0;
            var maxH2 = 0;
            $(this).find(".hTit").each(function () {
                hTit = $(this).outerHeight();
                if (maxH2 < hTit) {
                    maxH2 = hTit;
                }
            });
            $(this).find(".hTit").outerHeight(maxH2);
            $(this).find(".hBody").each(function () {
                hBody = $(this).outerHeight();
                if (maxH < hBody) {
                    maxH = hBody;
                }
            });
            $(this).find(".hBody").outerHeight(maxH);
        });
    } else {
        $(eleH).find(".hTit").css("height", "");
        $(eleH).find(".hBody").css("height", "");
    }
}

// Button go to top page
$(document).ready(function () {
    $(window).scroll(function () {
        if ($(this).scrollTop() > 100) {
            $('.pagetop').fadeIn();
        } else {
            $('.pagetop').fadeOut();
        }
    });

    $('.pagetop').click(function () {
        $('html, body').animate({
            scrollTop: 0
        }, 800);
        $('header').css('top', '0');
        return false;
    });
});

// Dropdown collapse
$(document).ready(function () {
    $('.dropdown-icon').click(function () {
        if ($(this).html() == 'keyboard_arrow_down') {
            $(this).html('keyboard_arrow_up');
        } else if ($(this).html() == 'keyboard_arrow_up') {
            $(this).html('keyboard_arrow_down');
        }
    });
});
