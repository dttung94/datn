$(document).ready(function () {
    let behavior = $('#behavior');
    let technique = $('#technique');
    let service = $('#service');
    let satisfaction = $('#satisfaction');
    let price = $('#price');
    let memo = $('#memo');

   $('#btn-save-rating').on('click', function () {
       behavior.val() === '' ? behavior.val(0) : behavior.val();
       technique.val() === '' ? technique.val(0) : technique.val();
       service.val() === '' ? service.val(0) : service.val();
       satisfaction.val() === '' ? satisfaction.val(0) : satisfaction.val();
       price.val() === '' ? price.val(0) : price.val();
       if (memo.val().length > 200) {
           toastr.error('Ghi chú tối đa chỉ 200 kí tự');
           return false;
       }
       $.ajax({
           url: '/booking/save-rating',
           type: 'POST',
           dataType: 'json',
           data: {
               worker_id: $('#worker-id').val(),
               booking_id: $('#booking-id').val(),
               rating_id: $('#rating-id').val(),
               behavior: behavior.val(),
               technique: technique.val(),
               service: service.val(),
               price: price.val(),
               satisfaction: satisfaction.val(),
               memo: memo.val()
           },
           beforeSend: function(){
               $("#loader").addClass('show-loading');
           },
           success: function (res) {
               $("#loader").removeClass('show-loading');
               $('#myModal').modal('hide');
               toastr.info(res.message);
               setTimeout((function() {
                   window.location.reload();
               }), 1000);
           },
           error: function () {
               toastr.error('Có lỗi xảy ra');
               setTimeout((function() {
                   window.location.reload();
               }), 1000);
           }
       });
   });

   $('.box-info-worker').on('click', function (e) {
       let workerId = $(this).attr('data-worker-id');
       $.ajax({
           url: '/recommend/get-list-shop-of-worker',
           type: 'POST',
           dataType: 'json',
           data: {
               workerId: workerId,
           },
           beforeSend: function(){
               $("#loader").addClass('show-loading');
           },
           success: function (res) {
               let html = '';
               $("#loader").removeClass('show-loading');
               if (res.length > 1) {
                   res.forEach(function (item) {
                       html += '<tr><td class="shop_name"><a href="'+ item['url'] +'" target="_blank">' + item['shop_name'] + '</a></td></tr>'
                   });
                   $('#info-worker-mapping-shop').html(html);
                   $('#myModal').modal('show');
               }
           },
           error: function () {
               toastr.error('Da co loi xay ra vui long thu lai sau hoac lien he voi quan tri vien');
           }
       });
   })
});

function openModalRating(data) {
    console.log(1);
    let bookingId = $(data).attr("data-booking-id");
    let workerId = $(data).attr("data-worker-id");
    $('input[type=hidden]').rating('rate', '');
    $('input[type=hidden]').removeAttr('disabled');
    $('#memo').val(''); $('#memo').removeAttr('disabled');

    if ($(data).attr('data-type') === 'open') {
        console.log(2);
        $('#myModal').modal('show');
        $('#btn-save-rating').removeAttr('disabled');
    } else {
        console.log(3);
        $.ajax({
            url: '/booking/get-rating',
            type: 'POST',
            dataType: 'json',
            data: {
                bookingId: bookingId,
            },
            beforeSend: function(){
                // Show image container
                $("#loader").addClass('show-loading');
            },
            success: function (res) {
                $("#loader").removeClass('show-loading');
                $('#myModal').modal('show');
                if (res !== null) {
                    $('#rating-id').val(res.id);
                    $('#behavior').rating('rate', res.behavior); $('#behavior').attr('disabled', 'disabled');
                    $('#technique').rating('rate', res.technique); $('#technique').attr('disabled', 'disabled');
                    $('#service').rating('rate', res.service); $('#service').attr('disabled', 'disabled');
                    $('#satisfaction').rating('rate', res.satisfaction); $('#satisfaction').attr('disabled', 'disabled');
                    if (res.memo !== '' && res.memo !== null) {
                        $('#memo').val(res.memo); $('#memo').attr('disabled', 'disabled');
                    }
                    $('#btn-save-rating').attr('disabled', 'disabled');
                }
            },
        });
    }

    $('#booking-id').val(bookingId);
    $('#worker-id').val(workerId);
}

$('#memo').on('input', function (evt) {
    var value = evt.target.value;

    if (value.length === 0) {
        $('#btn-save-rating').attr('disabled', 'disabled');
    } else {
        $('#btn-save-rating').removeAttr('disabled');
    }
});

function openModalRatingRecommend(data) {
    let workerId = $(data).attr('data-worker-id');
    $('input[type=hidden]').rating('rate', '');
    $.ajax({
        url: '/recommend/get-rating',
        type: 'POST',
        dataType: 'json',
        data: {
            workerId: workerId,
        },
        beforeSend: function(){
            // Show image container
            $("#loader").addClass('show-loading');
        },
        success: function (res) {
            $("#loader").removeClass('show-loading');
            $('#myModal').modal('show');
            // if (res !== null) {
                $('#behavior').rating('rate', res.data[0].behavior);
                $('#technique').rating('rate', res.data[0].technique);
                $('#service').rating('rate', res.data[0].service);
                $('#satisfaction').rating('rate', res.data[0].satisfaction);
            // }
        },
    });
    console.log(workerId);
}

function receiveCalendarWorker(data) {
    let workerId = $(data).attr('data-worker-id');
    $.ajax({
        url: '/recommend/receive-calendar-worker',
        type: 'POST',
        dataType: 'json',
        data: {
            workerId: workerId,
        },
        // beforeSend: function(){
        //     // Show image container
        //     $("#loader").addClass('show-loading');
        // },
        success: function (res) {
            if (res.success) {
                toastr.success(res.message);
                $('#btn-remind-' + res.worker_id).attr('disabled', 'disabled');
            } else {
                toastr.error(res.message);
            }
        },
    });
}
