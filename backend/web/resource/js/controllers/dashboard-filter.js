$(document).ready(function () {
    $('#change-year').on('change', function () {
        changeYear('year', $(this).val());
    });
    $('#change-year-regis').on('change', function () {
        changeYear('yearRegis', $(this).val());
    });
    $('#change-year-used').on('change', function () {
        changeYear('yearUsed', $(this).val());
    });
    $('#change-year-book-again').on('change', function () {
        changeYear('yearBookAgain', $(this).val());
    });
    $('#change-year-coupon-business').on('change', function () {
        changeYear('yearCouponBusiness', $(this).val());
    });

    function changeYear(type, year) {
        var href, location;
        location = window.location;
        href = location.origin + location.pathname + '?'+type+'=' + year;
        window.location.href = href;
    }

    $('#change-month').on('change', function () {
        getParam();
    });
    $('#change-month-used').on('change', function () {
        var yearUsed, monthUsed, paramMonthUsed = "", location;
        yearUsed = $('#change-year-used').val();
        monthUsed = $('#change-month-used').val();
        location = window.location;
        if (monthUsed > 0) {
            paramMonthUsed = '&monthUsed=' + monthUsed;
        }
        window.location.href = location.origin + location.pathname + '?yearUsed=' + yearUsed + paramMonthUsed;
    });


    $('#change-shop').on('change', function () {
        getParam();
    });

    function getParam() {
        var year, month, shop, location, paramMonth = "", paramShop = "";
        year = $('#change-year').val();
        shop = $('#change-shop').val();
        month = $('#change-month').val();
        location = window.location;

        if (month > 0) {
            paramMonth = '&month=' + month;
        }
        if(shop > 0) {
            paramShop = "&shop=" + shop;
        }
        window.location.href = location.origin + location.pathname + '?year=' + year + paramMonth + paramShop;
    }
})