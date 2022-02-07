window.angularApp.controller('CouponImportController', ['$scope', '$http', '$window', '$location', '$filter', '$uibModal', 'SMSService',
    function ($scope, $http, $window, $location, $filter, $uibModal, SMSService) {
        $scope.coupons = null;
        $scope.loadCouponData = function () {
            var formData = new FormData();
            formData.append("import-file", $("input[type='file'][name='coupon_import_file']")[0].files[0]);
            $.callAJAX({
                url: "/coupon/import/read-data",
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
                            toastr.error("権限がない店舗に対して、クーポンを登録しています。");
                        } else {
                            $scope.coupons = res.data.datas;
                            $scope.users = res.data.users;
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
            var data, errorYield = "", errorPhone = "", errorCouponName = "", error;
            data = angular.copy($scope.coupons);
            data.forEach(function (value, key) {
               if (value.yield > value.max || value.yield < value.min || value.yield%value.min != 0) {
                   errorYield += "【"+(key+1)+"】";
               }
               if (value.full_name == "" || value.phone_number.length != 11) {
                   errorPhone += "【"+(key+1)+"】";
               }
               if (value.coupon_name.length > value.validate_max_coupon_name) {
                   errorCouponName += "【"+(key+1)+"】";
               }
            });
            if (errorYield !== "") {
                errorYield = "<li>"+errorYield+"には誤りの料金があります。</li>";
            }
            if (errorPhone !== "") {
                errorPhone = "<li>"+errorPhone+"の番号は登録なし。</li>";
            }
            if (errorCouponName !== "") {
                errorCouponName = "<li>"+errorCouponName+"クーポン名 は " + data[0].validate_max_coupon_name + "  文字以下でなければいけません。</li>";
            }
            error = errorYield + errorPhone + errorCouponName;
            if (error !== "") {
                toastr.error(error);
            } else {
                $.callAJAX({
                    url: "/coupon/import/to-import-data",
                    method: 'POST',
                    data: {
                        data: data
                    },
                    callbackSuccess: function (res) {
                        if (res.success) {
                            $scope.coupons = res.data;
                            $scope.$apply();
                        } else {
                            toastr.error(res.message);
                        }
                    },
                    callbackFail: function (status, message) {
                    }
                });
            }
        };

        $scope.findPhoneNumber = function (key) {
            var phoneNumber, users, find;
            phoneNumber = $scope.coupons[key].phone_number;
            users = $scope.users;
            if (phoneNumber.length === 11) {
                find = users.find(x => x.phone_number == phoneNumber);
                if (find === undefined) {
                    $scope.coupons[key].full_name = "";
                } else {
                    $scope.coupons[key].full_name = find.full_name;
                }
            }
        };
        $scope.init = function () {
        }
    }]
);