window.angularApp.controller('MemberController', ['$scope', '$http', '$window', '$uibModal',
    function ($scope, $http, $window, $uibModal) {
        $scope.openModalConfigBlackList = function (member_id) {
            var modalInstance = $uibModal.open({
                animation: true,
                ariaLabelledBy: 'modal-title',
                ariaDescribedBy: 'modal-body',
                templateUrl: 'modal-black-list-config.html',
                controller: function ($scope, $uibModalInstance) {
                    $scope.blackListForm = null;
                    $scope.removeWorker = function (worker) {
                        console.log("worker", worker);
                        var index = $scope.blackListForm.blackListWorkerIds.indexOf(worker.worker_id + "");
                        if (index >= 0) {
                            $scope.blackListForm.blackListWorkerIds.splice(index, 1);
                        }
                        index = $scope.blackListForm.blackListWorkers.indexOf(worker);
                        if (index >= 0) {
                            $scope.blackListForm.blackListWorkers.splice(index, 1);
                        }
                    };
                    $scope.loadBlackListForm = function () {
                        $.callAJAX({
                            url: "/member/manage/load-black-list-form?id=" + member_id,
                            method: 'GET',
                            data: {},
                            callbackSuccess: function (res) {
                                if (res.success) {
                                    $scope.blackListForm = res.data;
                                    $scope.$apply();
                                } else {
                                    toastr.error(res.message);
                                    //todo close modal
                                    $scope.closeModal();
                                }
                            },
                            callbackFail: function (status, message) {
                                toastr.error(message);
                                //todo close modal
                                $scope.closeModal();
                            }
                        });
                    };
                    $scope.saveBlackList = function () {
                        if ($scope.blackListForm.isAddedWorkerBlackList == 1 && $scope.blackListForm.blackListWorkerIds.length == 0) {
                            toastr.error('最小一人ワーカを入力してください 。');
                        } else {
                            $.callAJAX({
                                url: "/member/manage/save-black-list?id=" + member_id,
                                method: 'POST',
                                data: {
                                    isAddedBlackList: $scope.blackListForm.isAddedBlackList,
                                    isAddedWorkerBlackList: $scope.blackListForm.isAddedWorkerBlackList,
                                    blackListWorkerIds: $scope.blackListForm.blackListWorkerIds,
                                },
                                callbackSuccess: function (res) {
                                    if (res.success) {
                                        toastr.success(res.message);
                                        window.location.reload();
                                    } else {
                                        toastr.error(res.message);
                                    }
                                },
                                callbackFail: function (status, message) {
                                    if (status.status == 403) {
                                        message = "このアクションの実行は許可されていません。"
                                    }
                                    toastr.error(message);
                                    //todo close modal
                                    $scope.closeModal();
                                }
                            });
                        }
                    };

                    $scope.closeModal = function () {
                        $uibModalInstance.dismiss('cancel');
                    };
                    $scope.loadBlackListForm();
                },
                size: "md",
                resolve: {
                    member_id: function () {
                        return member_id;
                    }
                }
            });
            modalInstance.result.then(function () {
            }, function () {
            });
        };

        $scope.openModalAddPrivateCoupon = function (member_id) {
            $.callAJAX({
                url: "/member/manage/load-private-coupon-form?id=" + member_id,
                method: 'GET',
                data: {},
                callbackSuccess: function (res) {
                    if (res.success) {
                        var modalInstance = $uibModal.open({
                            animation: true,
                            ariaLabelledBy: 'modal-title',
                            ariaDescribedBy: 'modal-body',
                            templateUrl: 'modal-add-private-coupon.html',
                            controller: function ($scope, $uibModalInstance, couponForm) {
                                $scope.couponForm = couponForm;
                                $scope.addPrivateCoupon = function () {
                                    var data = angular.copy($scope.couponForm);
                                    if (!data.shop_ids) {
                                        data.shop_ids = 'all';
                                    }
                                    $.callAJAX({
                                        url: "/member/manage/save-private-coupon?id=" + member_id,
                                        method: 'POST',
                                        data: data,
                                        callbackSuccess: function (res) {
                                            if (res.success) {
                                                toastr.success(res.message);
                                                $uibModalInstance.close({});
                                                setTimeout(function () {
                                                    window.location.reload();
                                                }, 500);
                                            } else {
                                                toastr.error(res.message);
                                            }
                                        },
                                        callbackFail: function (status, message) {
                                            if (status.status == 403) {
                                                message = "このアクションの実行は許可されていません。"
                                            }
                                            toastr.error(message);
                                            $scope.closeModal();
                                        }
                                    });
                                };
                                $scope.closeModal = function () {
                                    $uibModalInstance.dismiss('cancel');
                                };
                            },
                            size: "md",
                            resolve: {
                                couponForm: function () {
                                    return res.data;
                                }
                            }
                        });
                        modalInstance.result.then(function () {
                            //todo reload grid: pjax-grid-view-member
                            $.pjax.reload({container: '#pjax-grid-view-member'});
                        }, function () {
                        });
                    } else {
                        toastr.error(res.message);
                    }
                },
                callbackFail: function (status, message) {
                    toastr.error(message);
                    //todo close modal
                    $scope.closeModal();
                }
            });
        };
    }
]);