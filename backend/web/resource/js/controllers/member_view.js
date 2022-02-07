window.angularApp.controller('MemberViewController', ['$scope', '$http', '$window', '$uibModal',
    function ($scope, $http, $window, $uibModal) {
      $scope.memberId = null;
      $scope.filterCoupon = true;
      $scope.keywordBooking = '';
      $scope.perPage = 10;
      $scope.bookingHistory = {
        totalCount: 0,
        page: 1,
        perPage: 10,
        data: [],
      };
      $scope.keywordCouponLog = '';
      $scope.logType = '';
      $scope.date = '';
      $scope.perPageLog = 100;
      $scope.couponLog = {
        totalCount: 0,
        page: 1,
        perPage: 100,
        data: [],
      };
      $scope.keywordWorkerRemind = '';
      $scope.workerRemind = {
        totalCount: 0,
        page: 1,
        perPage: 10,
        data: [],
      };
      $scope.logOption = 'LOG_SMS';
      $scope.smsEmailLog = {
          totalCount: 0,
          page: 1,
          perPage: 100,
          data: [],
          log_type: '',
      };

      $scope.fetchBookingHistory = function(page) {
        if (!$scope.memberId) {
          return;
        }
        $.callAJAX({
          url: "/member/manage/load-booking-history",
          data: {
            member_id: $scope.memberId,
            filter_coupon: Number($scope.filterCoupon),
            keyword: $scope.keywordBooking,
            per_page: $scope.perPage,
            page: page ? page : $scope.bookingHistory.page,
          },
          method: 'GET',
          callbackSuccess: function (res) {
              if (res.success) {
                  $scope.bookingHistory = Object.assign({}, $scope.bookingHistory, res);
                  $scope.$apply();
              } else {
                  toastr.error(res.message);
              }
          },
          callbackFail: function (status, message) {
              toastr.error(message);
          }
        });
      }

      $scope.fetchBookingHistoryPrevious = function() {
        if ($scope.bookingHistory.page == 1) {
          return;
        }
        $scope.fetchBookingHistory(Number($scope.bookingHistory.page) - 1);
      }

      $scope.fetchBookingHistoryNext = function() {
        if ($scope.bookingHistory.page * $scope.bookingHistory.perPage >= $scope.bookingHistory.totalCount) {
          return;
        }
        $scope.fetchBookingHistory(Number($scope.bookingHistory.page) + 1);
      }

      $scope.resetBookingHistoryPage = function () {
        $scope.bookingHistory.page = 1
      }



      $scope.init = function (memberId) {
        $scope.memberId = memberId;
        $scope.fetchBookingHistory();
      };
    }
]);
