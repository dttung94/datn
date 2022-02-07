window.angularApp.controller('BookingController', [
        '$scope', '$http', '$window', '$location', '$filter', '$q', '$uibModal', 'SMSService', 'MailService',
        function ($scope, $http, $window, $location, $filter, $q, $uibModal, SMSService, MailService) {
            $scope.timezone = undefined;
            $scope.formData = {};
            $scope.calendarData = {};
            $scope.workers = [];

            $scope.checkSlotBookingConfirm = function (booking) {
                $.callAJAX({
                    url: "/calendar/online-booking/check-slot-booking-confirm?id=" + booking.booking_id + "&countdown=true",
                    method: 'POST'
                });
            };

            $scope.totalDisplayed = 1;
            $scope.loadListWorker = function (callback, isShowProgress = false, worker_id = null) {

                $.callAJAX({
                    url: "/calendar/booking/load-list-worker",
                    method: 'GET',
                    data: angular.copy($scope.formData),
                    callbackSuccess: function (res) {

                        if (res.success) {
                            let workers = [];
                            let sortFunc = function (a, b) {
                                return a.worker_name.localeCompare(b.worker_name, 'ja');
                            };
                            for (let index = 0; index < $scope.formData.shop_ids.length; index++) {
                                const shopId = $scope.formData.shop_ids[index];
                                let tmp = res.data.filter(v => v.shop_id === shopId)
                                    .map(worker => Object.assign({}, worker, {
                                        startTime: $scope.convertTime(worker.startTime),
                                        endTime: $scope.convertTime(worker.endTime)
                                    }));
                                tmp.sort(sortFunc);
                                workers = workers.concat(tmp);
                            }
                            $scope.workers = workers;
                            $scope.$apply();
                        } else {
                            toastr.error(res.message);
                        }
                        if (callback) {
                            callback(res);
                        }
                    },
                    callbackFail: function (status, message) {
                        toastr.error(message);
                    }
                }, isShowProgress);
            };

            $scope.convertTime = function (data) {
                var times = data.split(':');
                times[0] = parseInt(times[0]) < 10 ? '0'+parseInt(times[0]) : times[0];
                times[1] = parseInt(times[1]) < 10 ? '0'+parseInt(times[1]) : times[1];
                data = String(times[0] + ':' + times[1]);
                return data;
            };

            $scope.loadCalendarWorker = function (worker_id = null, callback = null, isShowProgress = false) {
                $.callAJAX({
                    url: "/calendar/booking/load-calendar-worker?worker_id=" + worker_id,
                    method: 'GET',
                    data: angular.copy($scope.formData),
                    callbackSuccess: function (res) {
                        if (res.success) {
                            $scope.background = res.background;
                            $scope.timeConfirmExpired = res.timeConfirmExpired;
                            if (worker_id) {
                                $scope.calendarData[worker_id] = renderCalendar(res)[worker_id];
                                $scope.$apply();
                            } else {
                                $scope.calendarData = renderCalendar(res);
                                $.each(Object.keys($scope.workers), function (key) {
                                    setTimeout(function(){
                                        $scope.totalDisplayed += 1;
                                        $scope.$apply();
                                        setScrollTop();
                                    }, 500 *(key));
                                });
                                $scope.$apply();
                            }
                        } else {
                            toastr.error(res.message);
                        }
                        if (callback) {
                            callback(res);
                        }
                    },
                    callbackFail: function (status, message) {
                        toastr.error(message);
                    }
                }, isShowProgress);
            };

            function renderCalendar(res)
            {
                var response = [], colspan = 1, startTime;
                if ($scope.startTime && $scope.startTime > res.startTime) {
                    $scope.startTime = res.startTime
                }
                if ($scope.startTime == undefined) {
                    $scope.startTime = res.startTime;
                }

                startTime = $scope.startTime;
                $.each(res.data, function (key, value) {
                    var iHour, iMinute, arr = [], checkTime, flag = false, shopId = 0, colspanFalse = 0, hourFalse = startTime, minuteFalse = 0;
                    for (iHour = startTime; iHour < 29; iHour++) {
                        for (iMinute = 0; iMinute < 12; iMinute ++) {
                            checkTime = iHour+":"+iMinute*5;
                            if ((checkTime === "28:55" || value.schedules[checkTime]) && flag === false && colspanFalse > 0) {
                                if (checkTime === "28:55") {
                                    colspanFalse += 1;
                                }
                                arr.push({
                                    'colspan': colspanFalse,
                                    'hour': hourFalse,
                                    'minute': minuteFalse*5,
                                    'totalMin': (hourFalse*60 + minuteFalse*5),
                                    'colorShop': value.colorShop
                                });
                            }

                            if (value.schedules[checkTime]) {
                                flag = flag == true ? false : true;
                                shopId = value.schedules[checkTime];
                                colspanFalse = 0;
                            }

                            if (flag === true) {
                                if (value.slots && value.slots[checkTime]) {
                                    var slotData = value.slots[checkTime];
                                    colspan = parseInt(slotData.duration_minute/5);
                                    arr.push({
                                        'hour': iHour,
                                        'minute': iMinute*5,
                                        'totalMin': (iHour*60 + iMinute*5),
                                        'colspan': colspan,
                                        'date': res.params[0],
                                        'isWorkingTime': true,
                                        'shop_id': shopId,
                                        'worker_id': parseInt(key),
                                        'worker_rank': value.worker_rank,
                                        'slotData': slotData,
                                        'colorShop': value.colorShop,
                                    });
                                } else {
                                    if (colspan > 1) {
                                        arr.push({
                                            'hour': iHour,
                                            'minute': iMinute*5,
                                            'totalMin': (iHour*60 + iMinute*5),
                                            'isInvisible': true,
                                            'date': res.params[0],
                                            'isWorkingTime': true,
                                            'shop_id': shopId,
                                            'worker_id': parseInt(key),
                                            'worker_rank': value.worker_rank,
                                            'colorShop': value.colorShop
                                        });
                                        colspan -=1;
                                    } else {
                                        arr.push({
                                            'hour': iHour,
                                            'minute': iMinute*5,
                                            'totalMin': (iHour*60 + iMinute*5),
                                            'date': res.params[0],
                                            'isWorkingTime': true,
                                            'shop_id': shopId,
                                            'worker_id': parseInt(key),
                                            'worker_rank': value.worker_rank,
                                            'colorShop': value.colorShop,
                                        });
                                    }
                                }
                            } else {
                                if (colspanFalse === 0) {
                                    hourFalse = iHour;
                                    minuteFalse = iMinute;
                                }
                                colspanFalse += 1;
                            }
                        }
                    }
                    response[key] = arr;
                });
                return response;
            }

            $scope.getColSpanBefore = function(shopId, colDatas) {
                if (shopId === undefined || colDatas === undefined) {
                    return 0;
                }
                let colspan = 0;
                for (let index = 0; index < colDatas.length; index++) {
                    const col = colDatas[index];
                    if (col.shop_id !== undefined && shopId === col.shop_id.toString()) {
                        break;
                    }
                    if (!col.shop_id || shopId !== col.shop_id.toString()) {
                        colspan += col.colspan
                            ? col.slotData === undefined ? col.colspan : 1
                            : 1;
                    }
                }
                return colspan;
            }

            $scope.getColSpanAfter = function(shopId, colDatas) {
                if (shopId === undefined || colDatas === undefined) {
                    return 0;
                }
                let colspan = 0;
                let touchedShopId = false;
                for (let index = 0; index < colDatas.length; index++) {
                    const col = colDatas[index];
                    if (col.shop_id !== undefined && shopId === col.shop_id.toString()) {
                        touchedShopId = true;
                        continue;
                    }
                    if (touchedShopId && (!col.shop_id || shopId !== col.shop_id.toString())) {
                        colspan += col.colspan
                            ? col.slotData === undefined ? col.colspan : 1
                            : 1;
                    }
                }
                return colspan;
            }

            function setScrollTop() {
                var tableCalendar, widthDiv1, widthScrollTop, bottom, top;
                top = $(".table-scrollable-top");
                bottom = $(".table-scrollable");
                tableCalendar = $("#table-calendar");

                widthScrollTop = bottom[0].offsetWidth;
                $('#table-scrollable-top').css('width', widthScrollTop);
                widthDiv1 = tableCalendar[0].offsetWidth;
                $('#scroll-div1').css('width', widthDiv1);

                top[0].scrollLeft = bottom[0].scrollLeft;
            }

            $scope.toCheckTime = function (colDate, colHour, colMinute, colspan) {
                var now = moment().tz($scope.timezone);
                var hour = parseInt(now.format("H")),
                    minute = parseInt(now.format("m") / 5) * 5,
                    date = now.format("YYYY-MM-DD"),
                    nowMinute = hour * 60 + minute;
                //todo check current time
                if (date == colDate) {//today
                    if (
                        (nowMinute >= (colHour * 60 + colMinute)) &&
                        (nowMinute < (colHour * 60 + colMinute + colspan * 5))
                    ) {//now
                        return 0;
                    } else if (nowMinute > (colHour * 60 + colMinute)) {//past
                        return -1;
                    } else {//future
                        return 1;
                    }
                } else if (date > colDate) {//past
                    return -1;
                } else {//future
                    return 1;
                }
            };

            $scope.toColCheckTime = function (data) {
                return $scope.toCheckTime(data.date, data.hour, data.minute, (data.colspan ? data.colspan : 1));
            };

            $scope.toCheckAddable = function (data) {
                return $scope.toColCheckTime(data) == 1
                    && data.isWorkingTime
                    && data.slotData == undefined;
            };

            $scope.getColClass = function (data) {
                var className = "";
                if (data.slotData) {
                    var slotData = data.slotData;
                    if (data.slotData.bookingInfo) {
                        var bookingInfo = data.slotData.bookingInfo;
                        var text = bookingInfo.slotInfo.is_change_duration_minute ? 'status-pending-change ' : 'status-pending ';

                        className += "slot-booking-online ";
                        switch (bookingInfo.status) {//todo check booking status
                            case 1: //pending
                                className += text;
                                break;
                            case 2: //confirming
                                className += "status-confirming ";
                                break;
                            case 3: //accepted
                                className += "status-accepted ";
                                break;
                            case 4: //canceled
                                className += "status-canceled ";
                                break;
                            case 5: //updating
                                className += "status-updating ";
                                break;
                        }
                        switch (slotData.status) {//todo check action
                            case 4://booked
                                switch (bookingInfo.status) {
                                    case 1: //pending
                                        className += "pulsate-regular ";
                                        break;
                                }
                        }
                    }
                }
                return className;
            };

            $scope.toSelectTimelineItem = function (colData) {
                if ($scope.toCheckAddable(colData)) {
                    $scope.selectedColMin = colData.worker_id + "-" + colData.totalMin;
                    var modalInstance = $uibModal.open({
                        animation: true,
                        ariaLabelledBy: 'modal-title',
                        ariaDescribedBy: 'modal-body',
                        templateUrl: 'modal-select-timeline-item.html',
                        controller: function ($scope, $uibModalInstance, colData) {
                            $scope.colData = colData;
                            $scope.createBookingSlot = function () {
                                $uibModalInstance.close("booking-slot");
                            };
                            $scope.createOfflineBooking = function () {
                                $uibModalInstance.close("offline-booking");
                            };
                            $scope.closeModal = function () {
                                $uibModalInstance.dismiss('cancel');
                            };
                        },
                        size: "md",
                        resolve: {
                            colData: function () {
                                return colData;
                            }
                        }
                    });
                    modalInstance.result.then(function (type) {
                        if (type == "booking-slot") {
                            $scope.scheduleSlot.openFormModal(colData, function () {
                                $scope.selectedColMin = null;
                            });
                        } else {
                            $scope.offlineBookingManage.openFormModal(colData, null, function () {
                                $scope.selectedColMin = null;
                            });
                        }
                    }, function () {
                        $scope.selectedColMin = null;
                    });
                }
            };

            $scope.toConfigShop = function () {
                var modalInstance = $uibModal.open({
                    animation: true,
                    ariaLabelledBy: 'modal-title',
                    ariaDescribedBy: 'modal-body',
                    templateUrl: 'modal-shop-config.html',
                    controller: function ($scope, $uibModalInstance) {
                        $scope.shops = [];
                        $scope.loadShops = function () {
                            $.callAJAX({
                                url: "/calendar/shop/load-shop-config",
                                method: 'GET',
                                data: {},
                                callbackSuccess: function (res) {
                                    if (res.success) {
                                        $scope.shops = res.data;
                                        $scope.$apply();
                                    } else {
                                        toastr.error(res.message);
                                    }
                                },
                                callbackFail: function (status, message) {
                                    toastr.error(message);
                                }
                            });
                        };
                        $scope.saveConfig = function () {
                            var data = {};
                            for (var i = 0; i < $scope.shops.length; i++) {
                                var shop = $scope.shops[i];
                                // data[shop.shop_id] = shop.isAllowFreeBooking;
                            }
                            console.log("Shop config data", data);
                            $.callAJAX({
                                url: "/calendar/shop/save-shop-config",
                                method: 'POST',
                                data: {
                                    shopConfigs: data,
                                },
                                callbackSuccess: function (res) {
                                    if (res.success) {
                                        toastr.success(res.message);
                                    } else {
                                        toastr.error(res.message);
                                    }
                                },
                                callbackFail: function (status, message) {
                                    toastr.error(message);
                                }
                            });
                        };
                        $scope.closeModal = function () {
                            $uibModalInstance.dismiss('cancel');
                        };
                        $scope.loadShops();
                    },
                    // size: "sm",
                    resolve: {}
                });
                modalInstance.result.then(function (type) {
                }, function () {
                });
            };

            $scope.bookingManage = {
                viewBookingInfo: function (booking_id) {
                    var modalInstance = $uibModal.open({
                        animation: true,
                        ariaLabelledBy: 'modal-title',
                        ariaDescribedBy: 'modal-body',
                        templateUrl: 'modal-booking-view.html',
                        controller: ["$scope", "$uibModalInstance", "SMSService", "booking_id", function ($scope, $uibModalInstance, SMSService, booking_id) {
                            $scope.booking_id = booking_id;
                            $scope.bookingForm = null;
                            $scope.loadBookingInfo = function () { //todo load booking info
                                $.callAJAX({
                                    url: "/calendar/booking/load-booking-info",
                                    method: 'GET',
                                    data: {
                                        id: booking_id
                                    },
                                    callbackSuccess: function (res) {
                                        if (res.success) {
                                            var data = res.data["booking-info"];
                                            data["bookingHistories"] = res.data["booking-histories"];
                                            $scope.bookingForm = data;
                                            $scope.$apply();
                                        } else {
                                            $uibModalInstance.dismiss('cancel');
                                        }
                                    },
                                    callbackFail: function (status, message) {
                                        toastr.error(message);
                                        $uibModalInstance.dismiss('cancel');
                                    }
                                });
                            };
                            $scope.acceptBooking = function () {
                                // console.log('freeBooking', $scope.bookingForm);
                                if ($scope.bookingForm.memberInfo.type_notification === 1) {
                                    if ($scope.bookingForm.status === 5) {
                                        MailService.composerMail("BOOKING_ONLINE_UPDATE", function (type, smsContent, titleEmail) {
                                            $scope.acceptBookingCallAjax(type, smsContent, titleEmail);
                                        });
                                    } else {
                                        MailService.composerMail("BOOKING_ONLINE_ACCEPT", function (type, smsContent, smsTitle) {
                                            $scope.acceptBookingCallAjax(type, smsContent, smsTitle);
                                        });
                                    }
                                } else {
                                    if ($scope.bookingForm.status === 5) {
                                        SMSService.composerSMS("BOOKING_ONLINE_UPDATE", function (type, smsContent) {
                                            $scope.acceptBookingCallAjax(type, smsContent);
                                        });
                                    } else {
                                        SMSService.composerSMS("BOOKING_ONLINE_ACCEPT", function (type, smsContent) {
                                            $scope.acceptBookingCallAjax(type, smsContent);
                                        });
                                    }
                                }
                            };
                            $scope.acceptBookingCallAjax = function (type, smsContent, titleEmail) {
                                $.callAJAX({
                                    url: "/calendar/online-booking/accept-booking?id=" + $scope.booking_id,
                                    method: 'POST',
                                    data: {
                                        type: type,
                                        sms_content: smsContent,
                                        title_email: titleEmail,
                                    },
                                    callbackSuccess: function (res) {
                                        if (res.success) {
                                            toastr.success(res.message);
                                            $scope.loadBookingInfo();
                                        } else {
                                            toastr.error(res.message);
                                        }
                                    },
                                    callbackFail: function (status, message) {
                                        toastr.error(message);
                                    }
                                });
                            };
                            $scope.rejectBooking = function () {
                                if ($scope.bookingForm.memberInfo.type_notification === 1) {
                                    if ($scope.bookingForm.status === 5) {
                                        MailService.composerMail("BOOKING_ONLINE_UPDATE_REJECT", function (type, smsContent, titleEmail) {
                                            $scope.rejectBookingAjax(type, smsContent, titleEmail);
                                        });
                                    } else {
                                        MailService.composerMail("BOOKING_ONLINE_REJECT", function (type, smsContent, titleEmail) {
                                            $scope.rejectBookingAjax(type, smsContent, titleEmail);
                                        });
                                    }
                                } else {
                                    if ($scope.bookingForm.status === 5) {
                                        SMSService.composerSMS("BOOKING_ONLINE_UPDATE_REJECT", function (type, smsContent) {
                                            $scope.rejectBookingAjax(type, smsContent);
                                        });
                                    } else {
                                        SMSService.composerSMS("BOOKING_ONLINE_REJECT", function (type, smsContent) {
                                            $scope.rejectBookingAjax(type, smsContent);
                                        });
                                    }
                                }
                            };
                            $scope.rejectBookingAjax = function (type, smsContent, titleEmail) {
                                $.callAJAX({
                                    url: "/calendar/online-booking/reject-booking?id=" + $scope.booking_id,
                                    method: 'POST',
                                    data: {
                                        type: type,
                                        sms_content: smsContent,
                                        title_email: titleEmail,
                                    },
                                    callbackSuccess: function (res) {
                                        if (res.success) {
                                            toastr.success(res.message);
                                            $scope.loadBookingInfo();
                                        } else {
                                            toastr.error(res.message);
                                        }
                                    },
                                    callbackFail: function (status, message) {
                                        toastr.error(message);
                                    }
                                });
                            };

                            $scope.editBooking = function () {
                                $uibModalInstance.close({
                                    "action": "edit",
                                    "booking": $scope.bookingForm,
                                });
                            };
                            $scope.closeModal = function () {
                                $uibModalInstance.dismiss('cancel');
                            };
                            $scope.loadBookingInfo();
                        }],
                        size: "md",
                        resolve: {
                            booking_id: function () {
                                return booking_id;
                            }
                        }
                    });
                    modalInstance.result.then(function (data) {
                        if (data.action == "edit") {
                            $scope.bookingManage.editBookingInfo(data.booking);
                        }
                    }, function () {
                    });
                },
                editBookingInfo: function (booking) {
                    console.log("Edit booking", booking);
                    $scope.onlineBookingManage.openFormModal(booking.booking_id);
                },
                cancelBookingInfo: function (booking) {
                    /*SMSService.composerSMS("BOOKING_REMOVE_SMS", function (type, smsContent) {
                        $.callAJAX({
                            url: "/calendar/booking/cancel-booking-info?id=" + booking.booking_id,
                            method: 'POST',
                            data: {
                                "sms_content": smsContent,
                                "type": type,
                            },
                            callbackSuccess: function (res) {
                                if (res.success) {
                                    toastr.success(res.message);
                                } else {
                                    toastr.error(res.message);
                                }
                            },
                            callbackFail: function (status, message) {
                                toastr.error(message);
                            }
                        });
                    });*/
                    if (confirm("Bạn muốn hủy lượt đặt này không?")) {
                        $.callAJAX({
                            url: "/calendar/booking/cancel-booking-info?id=" + booking.booking_id,
                            method: 'POST',
                            data: {},
                            callbackSuccess: function (res) {
                                if (res.success) {
                                    //$('.minute-col.working-time.col-shop-' + booking.slotInfo.shop_id).attr('style', 'background-color:' + $('#color-change option[value="'+booking.slotInfo.shop_id+'"]').data('color') + "!important");
                                    toastr.success(res.message);
                                } else {
                                    toastr.error(res.message);
                                }
                            },
                            callbackFail: function (status, message) {
                                toastr.error(message);
                            }
                        });
                    }
                },
                removeBookingInfo: function (booking) {
                    if (confirm("Bạn muốn xóa bỏ chứ ?")) {
                        $.callAJAX({
                            url: "/calendar/booking/delete-booking-info?id=" + booking.booking_id,
                            method: 'POST',
                            data: {},
                            callbackSuccess: function (res) {
                                if (res.success) {
                                    //$('.minute-col.working-time.col-shop-' + booking.slotInfo.shop_id).attr('style', 'background-color:' + $('#color-change option[value="'+booking.slotInfo.shop_id+'"]').data('color') + "!important");
                                    toastr.success(res.message);
                                } else {
                                    toastr.error(res.message);
                                }
                            },
                            callbackFail: function (status, message) {
                                toastr.error(message);
                            }
                        });
                    }
                },
                sendSMS: function (colData) {
                    if (colData.slotData.bookingInfo.memberInfo.type_notification === 1) {
                        MailService.composerMail("BOOKING_FREE_MAIL", function (type, mailContent, titleEmail) {
                            $.callAJAX({
                                url: "/calendar/sms/send-booking-email",
                                method: 'POST',
                                data: {
                                    "booking_id": colData.slotData.bookingInfo.booking_id,
                                    "sms_content": mailContent,
                                    "title_email": titleEmail,
                                    "type": type,
                                },
                                callbackSuccess: function (res) {
                                    if (res.success) {
                                        toastr.success(res.message);
                                    } else {
                                        toastr.error(res.message);
                                    }
                                },
                                callbackFail: function (status, message) {
                                    toastr.error(message);
                                }
                            });
                        })
                    } else {
                        SMSService.composerSMS("BOOKING_FREE_SMS", function (type, smsContent) {
                            $.callAJAX({
                                url: "/calendar/sms/send-booking-sms",
                                method: 'POST',
                                data: {
                                    "booking_id": colData.slotData.bookingInfo.booking_id,
                                    "sms_content": smsContent,
                                    "type": type,
                                },
                                callbackSuccess: function (res) {
                                    if (res.success) {
                                        toastr.success(res.message);
                                    } else {
                                        toastr.error(res.message);
                                    }
                                },
                                callbackFail: function (status, message) {
                                    toastr.error(message);
                                }
                            });
                        });
                    }
                },
                updateBookingNote: function (booking_id) {
                    var modalInstance = $uibModal.open({
                        animation: true,
                        ariaLabelledBy: 'modal-title',
                        ariaDescribedBy: 'modal-body',
                        templateUrl: 'modal-booking-note.html',
                        controller: ["$scope", "$uibModalInstance", "SMSService", "booking_id", function ($scope, $uibModalInstance, SMSService, booking_id) {
                            $scope.booking_id = booking_id;
                            $scope.bookingForm = null;
                            $scope.loadBookingInfo = function () { //todo load booking info
                                $.callAJAX({
                                    url: "/calendar/booking/load-booking-info",
                                    method: 'GET',
                                    data: {
                                        id: booking_id,
                                    },
                                    callbackSuccess: function (res) {
                                        if (res.success) {
                                            var data = res.data["booking-info"];
                                            data["bookingHistories"] = res.data["booking-histories"];
                                            $scope.bookingForm = data;
                                            $scope.$apply();
                                            console.log("Booking data", data);
                                        } else {
                                            $uibModalInstance.dismiss('cancel');
                                        }
                                    },
                                    callbackFail: function (status, message) {
                                        toastr.error(message);
                                        $uibModalInstance.dismiss('cancel');
                                    }
                                });
                            };
                            $scope.saveBookingNote = function () {
                                $.callAJAX({
                                    url: "/calendar/booking/update-booking-note?id=" + $scope.booking_id,
                                    method: 'POST',
                                    data: {
                                        note: $scope.bookingForm.note,
                                    },
                                    callbackSuccess: function (res) {
                                        if (res.success) {
                                            toastr.success(res.message);
                                        } else {
                                            toastr.error(res.message);
                                        }
                                    },
                                    callbackFail: function (status, message) {
                                        toastr.error(message);
                                    }
                                });
                            };
                            $scope.closeModal = function () {
                                $uibModalInstance.dismiss('cancel');
                            };
                            $scope.loadBookingInfo();
                        }],
                        size: "md",
                        resolve: {
                            booking_id: function () {
                                return booking_id;
                            }
                        }
                    });
                    modalInstance.result.then(function (data) {
                    }, function () {
                    });
                }
            };

            $scope.onlineBookingManage = {
                openFormModal: function (booking_id) {
                    $.callAJAX({
                        url: "/calendar/online-booking/booking-form",
                        method: 'GET',
                        data: {
                            id: booking_id,
                        },
                        callbackSuccess: function (res) {
                            if (res.success) {
                                //todo open modal
                                var modalInstance = $uibModal.open({
                                    animation: true,
                                    ariaLabelledBy: 'modal-title',
                                    ariaDescribedBy: 'modal-body',
                                    templateUrl: 'modal-online-booking-form.html',
                                    controller: function ($scope, $uibModalInstance, bookingForm) {
                                        $scope.bookingForm = bookingForm;
                                        $scope.bookingCostChange = function () {
                                                $scope.bookingForm.cost = 0;
                                                var key = $scope.bookingForm.course_id;
                                                $scope.bookingForm.cost += parseFloat($scope.bookingForm.coursesInfo[key-1].price);
                                        };
                                        $scope.saveBooking = function () {
                                            $.callAJAX({
                                                url: "/calendar/online-booking/save-booking?id=" + $scope.bookingForm.booking_id,
                                                method: 'POST',
                                                data: {
                                                    start_hour: $scope.bookingForm.start_hour,
                                                    start_minute: $scope.bookingForm.start_minute,
                                                    comment: $scope.bookingForm.comment,
                                                    duration_minute: $scope.bookingForm.duration_minute,
                                                    course_id: $scope.bookingForm.course_id,
                                                    cost: $scope.bookingForm.cost,
                                                },
                                                callbackSuccess: function (res) {
                                                    console.log(res);
                                                    if (res.success) {
                                                        toastr.success(res.message);
                                                        $scope.closeModal();//todo close modal
                                                    } else {
                                                        toastr.error(res.message);
                                                        $scope.bookingForm.error = res.error;
                                                    }
                                                },
                                                callbackFail: function (status, message) {
                                                    toastr.error(message);
                                                }
                                            });
                                        };
                                        $scope.closeModal = function () {
                                            $uibModalInstance.dismiss('cancel');
                                        };
                                    },
                                    size: "md",
                                    resolve: {
                                        bookingForm: function () {
                                            return res.data;
                                        }
                                    }
                                });
                                modalInstance.result.then(function () {
                                }, function () {
                                });
                            } else {
                                toastr.error(res.message);
                            }
                        },
                        callbackFail: function (status, message) {
                            toastr.error(message);
                        }
                    });
                },
            };

            $scope.scheduleSlot = {
                openFormModal: function (colData, callback) {
                    $.callAJAX({
                        url: "/calendar/slot-booking/load-schedule-slot",
                        method: 'GET',
                        data: {
                            worker_id: colData.worker_id,
                            shop_id: colData.shop_id,
                            date: colData.date,
                            start_time: colData.hour + ":" + colData.minute,
                        },
                        callbackSuccess: function (res) {
                            if (res.success) {
                                var modalInstance = $uibModal.open({
                                    animation: true,
                                    ariaLabelledBy: 'modal-title',
                                    ariaDescribedBy: 'modal-body',
                                    templateUrl: 'modal-worker-slot-form.html',
                                    controller: function ($scope, $uibModalInstance, slotForm) {
                                        $scope.slotForm = slotForm;
                                        $scope.saveSlot = function (workerRank) {
                                            $scope.slotForm.error = null;
                                            var data = {
                                                worker_id: $scope.slotForm.worker_id,
                                                shop_id: $scope.slotForm.shop_id,
                                                date: $scope.slotForm.date,
                                                start_time: $scope.slotForm.start_time,
                                                start_hour: $scope.slotForm.start_hour,
                                                start_minute: $scope.slotForm.start_minute,
                                                duration_minute: $scope.slotForm.duration_minute
                                            };
                                            $.callAJAX({
                                                url: "/calendar/slot-booking/save-schedule-slot?worker_rank="+workerRank,
                                                method: 'POST',
                                                data: data,
                                                callbackSuccess: function (res) {
                                                    if (res.success) {
                                                        toastr.success(res.message);
                                                        //todo close modal
                                                        $scope.closeModal();
                                                    } else {
                                                        toastr.error(res.message);
                                                        $scope.slotForm.error = res.error;
                                                    }
                                                },
                                                callbackFail: function (status, message) {
                                                    toastr.error(message);
                                                }
                                            });
                                        };
                                        $scope.closeModal = function () {
                                            $uibModalInstance.dismiss('cancel');
                                        };
                                    },
                                    size: "md",
                                    resolve: {
                                        slotForm: function () {
                                            return res.data;
                                        }
                                    }
                                });
                                modalInstance.result.then(function () {
                                    if (callback) {
                                        callback();
                                    }
                                }, function () {
                                    if (callback) {
                                        callback();
                                    }
                                });
                            } else {
                                toastr.error(res.message);
                                if (callback) {
                                    callback();
                                }
                            }
                        },
                        callbackFail: function (status, message) {
                            toastr.error('chi ri bay');
                            console.log(colData.worker_id);
                            if (callback) {
                                callback();
                            }
                        }
                    });
                },
                deleteSlot: function (colData) {
                    console.log("Delete colData", colData);
                    if (colData.slotData) {
                        if (confirm("Bạn muốn xóa khung làm việc này？")) {
                            $.callAJAX({
                                url: "/calendar/slot-booking/delete-schedule-slot",
                                method: 'GET',
                                data: {
                                    slot_id: colData.slotData.slot_id,
                                },
                                callbackSuccess: function (res) {
                                    if (res.success) {
                                        //$('.minute-col.working-time.col-shop-' + colData.shop_id).attr('style', 'background-color:' + $('#color-change option[value="'+colData.shop_id+'"]').data('color') + "!important");
                                        toastr.success(res.message);
                                    } else {
                                        toastr.error(res.message);
                                    }
                                },
                                callbackFail: function (status, message) {
                                    toastr.error(message);
                                }
                            });
                        }
                    }
                }
            };
            $scope.workerInfo = {
                openModalEditNote: function (worker_id, key, value = "") {
                    console.log("Open modal", worker_id, key, value);
                    var modalInstance = $uibModal.open({
                        animation: true,
                        ariaLabelledBy: 'modal-title',
                        ariaDescribedBy: 'modal-body',
                        templateUrl: 'modal-worker-note.html',
                        controller: function ($scope, $uibModalInstance, worker_id, key, value) {
                            $scope.worker_id = worker_id;
                            $scope.key = key;
                            $scope.value = value;
                            $scope.saveWorkerConfig = function (worker_id, key) {
                                $.callAJAX({
                                    url: "/calendar/worker/save-config?worker_id=" + $scope.worker_id,
                                    method: 'POST',
                                    data: {
                                        key: $scope.key,
                                        value: $scope.value,
                                    },
                                    callbackSuccess: function (res) {
                                        if (res.success) {
                                            $uibModalInstance.close($scope.value);
                                        } else {
                                            toastr.error(res.message);
                                        }
                                    },
                                    callbackFail: function (status, message) {
                                        toastr.error(message);
                                    }
                                }, false);
                            }
                            $scope.closeModal = function () {
                                $uibModalInstance.dismiss('cancel');
                            };
                        },
                        size: "md",
                        resolve: {
                            worker_id: function () {
                                return worker_id;
                            },
                            key: function () {
                                return key;
                            },
                            value: function () {
                                return value;
                            },
                        }
                    });
                    modalInstance.result.then(function (newValue) {
                    }, function () {
                    });
                },
                toWorkBreak: function (worker_id, date) {
                    if (confirm("当欠Emailを送りますか？")) {
                        MailService.composerMail("WORKER_WORK_BREAK", function (type, smsContent, smsTitle) {
                            $.callAJAX({
                                url: "/calendar/worker/work-break?worker_id=" + worker_id,
                                method: 'POST',
                                data: {
                                    type: type,
                                    sms_content: smsContent,
                                    date: date,
                                    mail_title: smsTitle,
                                },
                                callbackSuccess: function (res) {
                                    if (res.success) {
                                        toastr.success(res.message);
                                    } else {
                                        toastr.error(res.message);
                                    }
                                },
                                callbackFail: function (status, message) {
                                    toastr.error(message);
                                }
                            });
                        });
                    }
                },
                toCreateWorkerSlot: function (worker_id, date, workerRank) {
                    $.callAJAX({
                        url: "/calendar/worker/load-slot-form",
                        method: 'GET',
                        data: {
                            worker_id: worker_id,
                            date: date,
                            worker_rank: workerRank
                        },
                        callbackSuccess: function (res) {
                            if (res.success) {
                                var modalInstance = $uibModal.open({
                                    animation: true,
                                    ariaLabelledBy: 'modal-title',
                                    ariaDescribedBy: 'modal-body',
                                    templateUrl: 'modal-create-worker-slot-form.html',
                                    controller: function ($scope, $uibModalInstance, slotForm) {
                                        $scope.slotForm = slotForm;
                                        $scope.createWorkerSlot = function () {
                                            $scope.slotForm.error = null;
                                            var data = {
                                                start_hour: $scope.slotForm.start_hour,
                                                start_minute: $scope.slotForm.start_minute,
                                                duration_minute: $scope.slotForm.duration_minute,
                                                break_duration_minute: $scope.slotForm.break_duration_minute,
                                            };
                                            $.callAJAX({
                                                url: "/calendar/worker/create-worker-slot?worker_id=" + $scope.slotForm.worker_id + "&date=" + $scope.slotForm.date + "&worker_rank=" + workerRank,
                                                method: 'POST',
                                                data: data,
                                                callbackSuccess: function (res) {
                                                    if (res.success) {
                                                        toastr.success(res.message);
                                                        //todo close modal
                                                        $scope.closeModal();
                                                    } else {
                                                        toastr.error(res.message);
                                                        $scope.slotForm.error = res.error;
                                                    }
                                                },
                                                callbackFail: function (status, message) {
                                                    toastr.error(message);
                                                }
                                            });
                                        };
                                        $scope.closeModal = function () {
                                            $uibModalInstance.dismiss('cancel');
                                        };
                                    },
                                    size: "md",
                                    resolve: {
                                        slotForm: function () {
                                            return res.data;
                                        }
                                    }
                                });
                                modalInstance.result.then(function () {
                                }, function () {
                                });
                            } else {
                                toastr.error(res.message);
                            }
                        },
                        callbackFail: function (status, message) {
                            toastr.error(message);
                        }
                    });
                }
            };

            var _gotoNow = function () {
                //todo scroll to current hour
                var now = moment().tz($scope.timezone);
                var date = now.format("YYYY-MM-DD"),
                    hour = parseInt(now.format("H"));
                var $tableEl = $("#table-booking-manage");
                var $hourEl = $tableEl.find("th.hour-col[data-date=" + date + "][data-hour=" + hour + "]");
                if ($hourEl.isExist()) {
                    var scrollTo = $hourEl.position().left;
                    $tableEl.animate({'scrollLeft': scrollTo > 100 ? scrollTo : scrollTo}, 500);
                }
            };

            $scope.divMinute = -1;
            var _removeSlotExpried = function () {
                var minuteNow = moment().format('m');
                if (minuteNow%5 == 0 && minuteNow/5 != $scope.divMinute) {
                    $.ajax({
                        url: '/calendar/booking/remove-slot-expired',
                        type: 'POST'
                    });
                    $scope.divMinute = minuteNow/5;
                }
            };

            var _checkTime = function () {
                var now = moment().tz($scope.timezone);
                var date = now.format("YYYY-MM-DD"),
                    hour = parseInt(now.format("H")),
                    minute = parseInt(now.format("m") / 5) * 5;
                var totalMinuteNow = hour * 60 + minute;

                // var element, scrollTo;
                // element = $("#table-booking-manage");
                // scrollTo = element.find("th.hour-col[data-date=" + date + "][data-hour=" + hour + "]")[0].offsetLeft;
                // if (parseInt(now.format("m")) % 5 === 0) {
                //     element.animate({'scrollLeft': scrollTo}, 500);
                // }

                //todo check timeline header
                $("th.hour-col.now").removeClass("now");
                $("th.hour-col[data-date=" + date + "][data-hour=" + hour + "]").addClass("now");
                $("th.minute-col.now").removeClass("now");
                $("th.minute-col[data-date=" + date + "][data-hour=" + hour + "][data-minute=" + minute + "]").addClass("now");
                //todo check timeline
                $("td.minute-col").not('.past').each(function () {
                    var el = $(this);
                    var elDate = el.data("date"),
                        elHour = el.data("hour"),
                        elMinute = el.data("minute");
                    var totalMinuteEl = elHour * 60 + elMinute;
                    if (elDate == date) {
                        if (totalMinuteEl < totalMinuteNow) {
                            if (el.data("slot-data").bookingInfo && el.find('.count-down').length !== 0) {
                                el.removeClass("now");
                                el.removeClass("future");
                            } else  {
                                el.addClass("past");
                                el.removeClass("now");
                                el.removeClass("future");
                            }
                        } else if (totalMinuteEl == totalMinuteNow) {
                            el.removeClass("past");
                            el.addClass("now");
                            el.removeClass("future");
                        } else {
                            el.removeClass("past");
                            el.removeClass("now");
                            el.addClass("future");
                            //todo check is addable
                            var isWorkingTime = el.data("is-working-time"),
                                slotData = el.data("slot-data");
                            if (isWorkingTime && !slotData) {
                                el.addClass("addable");
                            }
                        }
                    } else {
                        el.removeClass("past");
                        el.removeClass("now");
                        el.addClass("future");
                        //todo check is addable
                        var isWorkingTime = el.data("is-working-time"),
                            slotData = el.data("slot-data");
                        if (isWorkingTime && !slotData) {
                            el.addClass("addable");
                        }
                    }
                });
            };

            var _initListenEvent = function () {
                var _syncCalendarData = function (worker_id, calendarData, callback) {
                    angular.forEach(calendarData, function (timeData, totalMin) {
                        for (var index = 0; index < $scope.calendarData[worker_id].length; index++) {
                            if (
                                $scope.calendarData[worker_id][index].totalMin == parseInt(totalMin) &&
                                $scope.calendarData[worker_id][index].date == timeData.date &&
                                $scope.calendarData[worker_id][index].shop_id == timeData.shop_id
                            ) {
                                $scope.calendarData[worker_id][index] = timeData;
                                break;
                            }
                        }
                    });
                    $scope.$apply();
                    if (callback) {
                        callback();
                    }
                };
                $(document).on("shopCalendarSlotChanged", function (event, data) {
                    //todo update calendar data
                    var worker_id = data.data.worker_id,
                        oldCalendarData = data.data.oldCalendarData,
                        newCalendarData = data.data.newCalendarData;
                    _syncCalendarData(worker_id, oldCalendarData);
                    _syncCalendarData(worker_id, newCalendarData);
                });

                $(document).on("configWorkerTime", function (event, data) {
                    $scope.workers.forEach(function (value, key) {
                        if (
                            value.worker_id == data.data.worker_id &&
                            value.shop_id == data.data.shop_id
                        ) {
                            $scope.workers[key].startTime = data.data.start_time;
                            $scope.workers[key].endTime = data.data.end_time;
                        }
                    });
                    $scope.loadCalendarWorker(data.data.worker_id, function () {
                        _initListenEvent();
                        _checkTime();
                        _gotoNow();
                        setInterval(function () {
                            _checkTime();
                        }, 100);
                    }, true);
                });

                $(document).on("workerConfigChanged", function (event, data) {//todo update worker config data
                    angular.forEach($scope.workers, function (worker, key) {
                        if (worker.worker_id == data.data.worker_id) {
                            worker[data.data.key] = data.data.value;
                            $scope.$apply();
                        }
                    });
                });
            };

            $scope.init = function (formData, timezone) {
                $scope.formData = formData;
                $scope.timezone = timezone;
                //todo load list worker
                $scope.loadListWorker(function () {
                    //todo load worker calendar data
                    $scope.loadCalendarWorker("", function () {
                        console.log(1)
                        _initListenEvent();
                        _checkTime();
                        _gotoNow();
                        _removeSlotExpried();
                        setInterval(function () {
                            _checkTime();
                            _removeSlotExpried();
                        }, 10);
                    }, true);
                }, true);
                // $scope.freeBookingManage.loadBooking();
            };
        }
    ]
);
