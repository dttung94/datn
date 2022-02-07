window.angularApp.controller('ManageController', [
    '$scope', '$http', '$window', '$location', '$filter', '$q', '$uibModal', 'SMSService',
    function ($scope, $http, $window, $location, $filter, $q, $uibModal, SMSService) {
        $scope.timezone = undefined;
        $scope.formData = {};
        $scope.type = 'LAST_TIME_USER_RECEIVED_COUPON';
        $scope.totalMemberReceiveCoupon = 0;
        $scope.totalCouponRelease = 0;
        $scope.yield = 0;
        $scope.password = '';
        $scope.coupon_name = '';
        $scope.memo = '';
        $scope.location = 0;
        $scope.totalYield = 0;
        $scope.users = [];
        $scope.isDisable = false;
        $scope.messageError = [];
        $scope.smsForm = '';
        $scope.typeSmsForm = 'COUPON_BUSINESS';
        $scope.loadGetUserReceive = function (isShowProgress = true) {
            $.callAJAX({
                url: "/coupon/manage/get-total-user-receive",
                method: 'GET',
                data: {
                    type: $scope.type,
                },
                callbackSuccess: function (res) {
                    if (res === null) {
                        $scope.totalMemberReceiveCoupon = 0;
                        $scope.totalCouponRelease = 0;
                    } else {
                        $scope.totalMemberReceiveCoupon = res.length;
                        $scope.totalCouponRelease = res.length;
                        $scope.users = res;
                        $scope.totalYield = parseInt(res.length) * $scope.formData.yield;
                    }
                    $scope.$apply();
                },
                callbackFail: function (res, status) {
                    toastr.error(status)
                }
            }, isShowProgress)
        };

        // $scope.openModalConfirm = function () {
        //     var formData = $('#coupon-form').serialize();
        //     $.callAJAX({
        //         url: "/coupon/manage/create-coupon-business?tab=3",
        //         method: 'POST',
        //         data: formData,
        //         callbackSuccess: function (res) {
        //             let arrayMessage = $.map(res.error, function (value) {
        //                 return [value];
        //             });
        //             $scope.messageError = arrayMessage;
        //             $scope.$apply();
        //             if (arrayMessage.length < 1) {
        //                 var modalInstance = $uibModal.open({
        //                     animation: true,
        //                     ariaLabelledBy: 'modal-title',
        //                     ariaDescribedBy: 'modal-body',
        //                     templateUrl: 'modal-coupon.html',
        //                     scope: $scope,
        //                     controller: ["$scope", "$uibModalInstance", "SMSService", function ($scope, $uibModalInstance, SMSService) {
        //                         $scope.closeModal = function () {
        //                             $uibModalInstance.dismiss('cancel');
        //                         };
        //                         $scope.saveCoupon = function () {
        //                             $.callAJAX({
        //                                 url: "/calendar/sms/load-template",
        //                                 method: 'POST',
        //                                 data: {
        //                                     type: $scope.typeSmsForm,
        //                                 },
        //                                 callbackSuccess: function (res) {
        //                                     if (res.success) {
        //                                         $scope.smsForm = res.data;
        //                                         $scope.$apply();
        //                                     } else {
        //                                         toastr.error(res.message);
        //                                     }
        //                                 },
        //                                 callbackFail: function (status, message) {
        //                                     toastr.error(message);
        //                                 }
        //                             });
        //
        //                             var modalTemplateSms = $uibModal.open({
        //                                 animation: true,
        //                                 ariaLabelledBy: 'modal-title',
        //                                 ariaDescribedBy: 'modal-body',
        //                                 templateUrl: 'modal-write-sms-business.html',
        //                                 scope: $scope,
        //                                 controller: ["$scope", "$uibModalInstance", "SMSService", function ($scope, $uibModalInstance, SMSService) {
        //                                     $scope.closeModal = function () {
        //                                         $uibModalInstance.dismiss('cancel');
        //                                     };
        //                                     $scope.saveCoupon = function () {
        //                                         $('#content_sms').val($scope.smsForm.content)
        //                                         $.callAJAX({
        //                                             url: $('#coupon-form').attr('action'),
        //                                             method: 'POST',
        //                                             data: $('#coupon-form').serialize(),
        //                                             callbackSuccess: function (res) {
        //                                                 if (res.success) {
        //                                                     toastr.success(res.message);
        //                                                     window.location.href = '/coupon/manage/create-coupon-business';
        //                                                 } else {
        //                                                     toastr.error(res.message);
        //                                                 }
        //                                             },
        //                                             callbackFail: function (status, message) {
        //                                                 toastr.error(message);
        //                                             }
        //                                         });
        //                                     };
        //                                 }],
        //                                 size: "md",
        //                             });
        //                         };
        //                     }],
        //                     size: "md",
        //                 });
        //             }
        //         },
        //         callbackFail: function (res, status) {
        //             toastr.error(status)
        //         }
        //     })
        // };

        $scope.openModalConfirm = function () {
            var formData = $('#coupon-form').serialize();
            $.callAJAX({
                url: "/coupon/manage/create-coupon-business?tab=3",
                method: 'POST',
                data: formData,
                callbackSuccess: function (res) {
                    let arrayMessage = $.map(res.error, function (value) {
                        return [value];
                    });
                    $scope.messageError = arrayMessage;
                    $scope.$apply();
                    if (arrayMessage.length < 1) {
                        $.callAJAX({
                            url: "/calendar/sms/load-template",
                            method: 'GET',
                            data: {
                                type: $scope.typeSmsForm,
                            },
                            callbackSuccess: function (res) {
                                if (res.success) {
                                    $scope.smsForm = res.data;
                                    $scope.$apply();
                                } else {
                                    toastr.error(res.message);
                                }
                            },
                            callbackFail: function (status, message) {
                                toastr.error(message);
                            }
                        });

                        var modalTemplateSms = $uibModal.open({
                            animation: true,
                            ariaLabelledBy: 'modal-title',
                            ariaDescribedBy: 'modal-body',
                            templateUrl: 'modal-write-sms-business.html',
                            scope: $scope,
                            controller: ["$scope", "$uibModalInstance", "SMSService", function ($scope, $uibModalInstance, SMSService) {
                                $scope.closeModal = function () {
                                    $uibModalInstance.dismiss('cancel');
                                };
                                $scope.saveCoupon = function () {
                                    $('#content_sms').val($scope.smsForm.content);
                                    var modalInstance = $uibModal.open({
                                        animation: true,
                                        ariaLabelledBy: 'modal-title',
                                        ariaDescribedBy: 'modal-body',
                                        templateUrl: 'modal-coupon.html',
                                        scope: $scope,
                                        controller: ["$scope", "$uibModalInstance", "SMSService", function ($scope, $uibModalInstance, SMSService) {
                                            $scope.closeModal = function () {
                                                $uibModalInstance.dismiss('cancel');
                                            };
                                            $scope.saveCoupon = function () {
                                                $('#content_sms').val($scope.smsForm.content)
                                                $.callAJAX({
                                                    url: $('#coupon-form').attr('action'),
                                                    method: 'POST',
                                                    data: $('#coupon-form').serialize(),
                                                    callbackSuccess: function (res) {
                                                        if (res.success) {
                                                            toastr.success(res.message);
                                                            window.location.href = '/coupon/manage/create-coupon-business';
                                                        } else {
                                                            toastr.error(res.message);
                                                        }
                                                    },
                                                    callbackFail: function (status, message) {
                                                        toastr.error(message);
                                                    }
                                                });
                                            };

                                        }],
                                        size: "md"
                                    });
                                };
                            }],
                            size: "md"
                        });
                    }
                },
                callbackFail: function (res, status) {
                    toastr.error(status)
                }
            });
        }

        $scope.init = function (formData, timezone) {
            $scope.formData = formData;
            $scope.timezone = timezone;
            $scope.loadGetUserReceive();
        }
    }
]);
