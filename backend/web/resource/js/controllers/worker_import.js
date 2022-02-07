window.angularApp.controller('WorkerImportController', ['$scope', '$http', '$window', '$location', '$filter', '$uibModal', 'SMSService',
    function ($scope, $http, $window, $location, $filter, $uibModal, SMSService) {
        $scope.workers = null;
        $scope.loadWorkerData = function (shop_id, date) {
            var formData = new FormData();
            formData.append("import-file", $("input[type='file'][name='worker_import_file']")[0].files[0]);
            $.callAJAX({
                url: "/worker/import/read-data",
                method: 'POST',
                async: true,
                data: formData,
                cache: false,
                contentType: false,
                processData: false,
                timeout: 60000,
                callbackSuccess: function (res) {
                    if (res.success) {
                        if (res.data == 'shop_error') {
                            toastr.error("この店舗にはエラーが発生しました。");
                        } else {
                            $scope.workers = res.data;
                            $scope.$apply();
                        }
                    } else {
                        toastr.error(res.message);
                    }
                },
                callbackFail: function (status, message) {
                    toastr.error(message);
                }
            });
        };

        $scope.toImport = function () {
            $.callAJAX({
                url: "/worker/import/to-import-data",
                method: 'POST',
                data: {
                    data: angular.copy($scope.workers),
                },
                callbackSuccess: function (res) {
                    if (res.success) {
                        $scope.workers = res.data;
                        $scope.$apply();
                    } else {
                        toastr.error(res.message);
                    }
                },
                callbackFail: function (status, message) {
                }
            });
        };
        $scope.init = function () {
        }
    }]
);