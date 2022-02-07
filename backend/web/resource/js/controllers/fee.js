window.angularApp.controller('FeeController', ['$scope', '$http', '$window', '$location', '$filter', '$uibModal',
    function ($scope, $http, $window, $location, $filter, $uibModal) {
        $scope.data = {};
        $scope.toSaveOptionFee = function (item) {
            $.callAJAX({
                url: '/data/price/save-fee?id=' + item.id,
                method: 'POST',
                data: angular.copy(item),
                callbackSuccess: function (res) {
                    if (res.success) {
                        item.price = res.data.price;
                        item.action = null;
                        $scope.$apply();
                    }
                },
                callbackFail: function (status, message) {
                    if (status.status == 403) {
                        message = "このアクションの実行は許可されていません。"
                    }
                    toastr.error(message);
                }
            });
        };

        $scope.init = function (data) {
            $scope.data = data;
        }
    }]);