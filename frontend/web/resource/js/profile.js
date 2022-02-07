$(function(){
    var current = location.pathname;
    $('#pf_nav a').each(function(){
        var $this = $(this);
        // if the current path is like this link, make it active
        if($this.attr('href').indexOf(current) !== -1){
            $this.addClass('active');
        }
    });
    $('#config_mail').on('click', function () {
        $('.item-config-mail').css('display', 'block');
    })
    $('#your_email').on('click', function () {
        $('.item-your-email').css('display', 'block');
    })
});

function openModalReply(data) {
    let commentId = $(data).attr('data-id');
    if ($('#replyTwo-' + commentId).hasClass('show')) {
        $('#replyTwo-' + commentId).removeClass('show');
    }
}

function openModalListReply(data) {
    let commentId = $(data).attr('data-id');
    if ($('#reply-' + commentId).hasClass('show')) {
        $('#reply-' + commentId).removeClass('show');
    }
}

function replyComment(data) {
    let idBtn = $(data).attr('id');
    let id = $(data).attr('data-id');
    let txtReply = $('#txt-reply-comment-' + id).val();
    if (txtReply === '') {
        toastr.error("コメントの内容を入力してください!!!");
        return false;
    }
    $.ajax({
        url: '/reply/create',
        type: 'POST',
        dataType: 'json',
        data: {
            content: txtReply,
            commentId : id,
        },
        success: function (res) {
            if (res.success) {
                toastr.success(res.message);
                setTimeout((function() {
                    window.location.reload();
                }), 2000);
            } else {
                toastr.error(res.message);
            }
        }
    })
}

// $('.dropdown-menu-custom .pf-nav-item').click(function () {
//     $(this).siblings('.sub-menu').toggleClass('show');
//     $(this).find('.arrow').toggleClass('open');
// })





