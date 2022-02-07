<style>
    .expansion-text {
        cursor: pointer;
    }
    .expansion-panel {
        padding-top: 8px;
    }
    .coupon-label, .booking-label {
        float: left;
        width: 90px;
        font-weight: 600;
    }
    .coupon-text {
    }
</style>
<div id="booking_history">
    <div class="portlet light">
        <div class="portlet-title">
            <div class="caption">Lịch sử đặt lịch</div>
        </div>
        <div class="row">
            <div class="col-md-3">
                <div class="form-group">
                    <input class="form-control" placeholder="Từ khóa" ng-model="keywordBooking" ng-change="resetBookingHistoryPage()">
                </div>
            </div>
            <div class="col-md-2">
                <div class="form-group">
                    <input class="form-control" placeholder="ページに当たる数件" ng-model="perPage" ng-change="resetBookingHistoryPage()">
                </div>
            </div>
            <div class="col-md-1">
                <div class="form-group">
                    <button class="btn btn-primary" ng-click="fetchBookingHistory()"><i class="fa fa-search"></i></button>
                </div>
            </div>
        </div>
        <div class="portlet-body">
            <p>Hiển thị từ <strong>{{bookingHistory.offset + key + 1}}</strong> đến <strong>{{bookingHistory.offset + bookingHistory.data.length}}</strong> trong tổng số <strong>{{bookingHistory.totalCount}}</strong> lượt</p>
            <table class="table table-striped table-bordered">
                <thead>
                    <th>#</th>
                    <th>Ngày</th>
                    <th>Dịch vụ</th>
                    <th>Tiệm salon</th>
                    <th>Khung thời gian sử dụng</th>
                    <th>Phí dịch vụ</th>
                </thead>
                <tbody>
                    <tr ng-repeat="(key, booking) in bookingHistory.data">
                        <td>{{bookingHistory.offset + key + 1}}</td>
                        <td>
                            <p><span class="booking-label">Ngày tạo: </span>{{booking.booking_created_at}}</p>
                            <p><span class="booking-label">Lần cập nhật cuối: </span>{{booking.booking_modified_at}}</p>
                        </td>
                        <td>{{bookingHistory.courses[booking.course_id]}}</td>
                        <td>{{bookingHistory.shops[booking.shop_id]}}</td>
                        <td>
                            <p><span class="booking-label">Thời gian: </span>{{booking.duration_minute}} phút</p>
                            <p><span class="booking-label">Bắt đầu: </span>{{booking.start_time}}</p>
                            <p><span class="booking-label">Kết thúc: </span>{{booking.end_time}}</p>
                        </td>
                        <td>{{booking.cost}} VNĐ</td>
                    </tr>
                </tbody>
            </table>
            <ul class="pager">
                <li ng-class="{disabled: bookingHistory.page == 1}">
                    <a ng-click="fetchBookingHistoryPrevious()"><span aria-hidden="true">&laquo;</span></a>
                </li>
                <li ng-class="{disabled: bookingHistory.page * bookingHistory.perPage >= bookingHistory.totalCount}">
                    <a ng-click="fetchBookingHistoryNext()"><span aria-hidden="true">&raquo;</span></a>
                </li>
            </ul>
        </div>
    </div>
</div>
