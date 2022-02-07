window.angularApp.controller('ScheduleController', ['$scope', '$http', '$window', '$location', '$filter', '$uibModal', 'SMSService',
    function ($scope, $http, $window, $location, $filter, $uibModal, SMSService) {
        $scope.shopId = null;
        $scope.schedule = {
            saveConfig: function () {
                var $form = $("#shop-schedule-config-form");
                var formData = $form.serializeArray();
                console.log("Schedule save config", $scope.shopId, formData);
                $.callAJAX({
                    url: "/calendar/schedule/save-schedule-configs?shop_id=" + $scope.shopId,
                    method: 'POST',
                    data: formData,
                    callbackSuccess: function (res) {
                        if (!res.success) {
                            toastr.error(res.message);
                        }
                    },
                    callbackFail: function (status, message) {
                        toastr.error(message);
                    }
                });
            }
        };
        $scope.shopSchedule = {
            shopId: null,
            formData: null,
            viewWorkerCalendar: function (worker_id, date) {
                var modalInstance = $uibModal.open({
                    animation: true,
                    ariaLabelledBy: 'modal-title',
                    ariaDescribedBy: 'modal-body',
                    templateUrl: 'modal-worker-calendar.html',
                    controller: ["$scope", "$uibModalInstance", "worker_id", "date", function ($scope, $uibModalInstance, SMSService, booking_id) {
                        $scope.workerScheduleData = null;
                        $scope.loadWorkerCalendar = function () {
                            $.callAJAX({
                                url: "/calendar/schedule/get-worker-schedule",
                                method: 'GET',
                                data: {
                                    worker_id: worker_id,
                                    date: date,
                                },
                                callbackSuccess: function (res) {
                                    if (res.success) {
                                        $scope.workerScheduleData = res.data;
                                        $scope.$apply();
                                    } else {
                                        toastr.error(res.message);
                                        $scope.closeModal();
                                    }
                                },
                                callbackFail: function (status, message) {
                                    toastr.error(message);
                                    $scope.closeModal();
                                }
                            });
                        };
                        $scope.closeModal = function () {
                            $uibModalInstance.dismiss('cancel');
                        };
                        $scope.loadWorkerCalendar();
                    }],
                    size: "lg",
                    resolve: {
                        worker_id: function () {
                            return worker_id;
                        },
                        date: function () {
                            return date;
                        },
                    }
                });
                modalInstance.result.then(function (smsContent) {
                }, function () {
                });
            },
            toCheckWorkerSchedule: function (calendarData) {
                if (calendarData.is_work_day) {
                    var data = {
                        shop_id: calendarData.shop_id,
                        worker_id: calendarData.worker_id,
                        date: calendarData.date,
                        work_start_hour: calendarData.work_start_hour,
                        work_start_minute: calendarData.work_start_minute,
                        work_end_hour: calendarData.work_end_hour,
                        work_end_minute: calendarData.work_end_minute,
                    };
                    $.callAJAX({
                        url: "/calendar/schedule/check-worker-schedule",
                        method: 'GET',
                        data: data,
                        callbackSuccess: function (res) {
                            if (!$scope.shopSchedule.formData.error) {
                                $scope.shopSchedule.formData.error = {};
                            }
                            if (!res.success) {
                                $scope.shopSchedule.formData.error[calendarData.date + "-" + calendarData.worker_id] = res.error;
                                $scope.$apply();
                            } else {
                                $scope.shopSchedule.formData.error[calendarData.date + "-" + calendarData.worker_id] = null;
                                $scope.$apply();
                            }
                        },
                        callbackFail: function (status, message) {
                            toastr.error(message);
                        }
                    }, false);
                } else {
                    if (!$scope.shopSchedule.formData.error) {
                        $scope.shopSchedule.formData.error = {};
                    }
                    $scope.shopSchedule.formData.error[calendarData.date + "-" + calendarData.worker_id] = null;
                }
            },
            toImportSchedule: function (shop_id, date) {
                var formData = new FormData();
                formData.append("import-file", $("input[type='file'][name='schedule_import']")[0].files[0]);
                console.log("form data", formData);
                $.callAJAX({
                    url: "/calendar/schedule-import/upload-data?shop_id=" + shop_id + "&date=" + date,
                    method: 'POST',
                    async: true,
                    data: formData,
                    cache: false,
                    contentType: false,
                    processData: false,
                    timeout: 60000,
                    callbackSuccess: function (res) {
                        console.log("Upload data", res);
                        if (res.success) {
                            $scope.shopSchedule.formData.scheduleWorkers = res.data.schedule;
                            $scope.shopSchedule.formData.error = res.data.error;
                            $scope.$apply();
                            console.log($scope.shopSchedule.formData);
                        } else {
                            toastr.error(res.message);
                        }
                    },
                    callbackFail: function (status, message) {
                        toastr.error(message);
                    }
                });
            },
            toSave: function () {
                $scope.shopSchedule.formData.error = null;
                var form = $("#shop-schedule-config-form");
                $.callAJAX({
                    url: "/calendar/schedule/save-config?shop_id=" + $scope.shopSchedule.shopId + "&date=" + $scope.shopSchedule.formData.date,
                    method: 'POST',
                    data: form.serialize(),
                    callbackSuccess: function (res) {
                        if (!res.success) {
                            toastr.error(res.message);
                            $scope.shopSchedule.formData.error = res.error;
                            $scope.$apply();
                            console.log("shopSchedule.formData", $scope.shopSchedule.formData);
                        }
                    },
                    callbackFail: function (status, message) {
                        // toastr.error(message);
                    }
                });
            }
        };
        $scope.intShopSchedule = function (shopId, formData) {
            console.log("shop schedule", shopId, formData);
            $scope.shopSchedule.shopId = shopId;
            $scope.shopSchedule.formData = formData;
        }
    }]);