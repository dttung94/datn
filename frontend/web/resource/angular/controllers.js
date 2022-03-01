'use strict';

var controllers = angular.module('controllers', []);

controllers.controller('GuestController', ['$scope', '$location', '$window',
    function ($scope, $location, $window) {
        $scope.init = function () {
        };
    }]
);

controllers.controller('LoginController', ['$scope', '$location', '$window',
    function ($scope, $location, $window) {
        $scope.formData = {};
        $scope.toLogin = function () {
            $scope.formData.error = null;
            var formData = {
                "username": $scope.formData.username,
                "password": $scope.formData.password,
                "rememberMe": $scope.formData.rememberMe,
            };
            $.callAJAX({
                url: '/site/login',
                method: 'POST',
                data: formData,
                callbackSuccess: function (res) {
                    if (res.success) {
                        toastr.success(res.message);
                    } else {
                        $scope.formData.error = res.error;
                        $scope.$apply();
                    }
                },
                callbackFail: function (res, message) {
                }
            });
        };
        $scope.init = function (formData) {
            $scope.formData = formData;
        };
    }]
);

controllers.controller('ForgotPasswordController', ['$scope', '$location', '$window',
    function ($scope, $location, $window) {
        $scope.formData = {};
        $scope.toForgotPassword = function () {
            $scope.formData.error = null;
            var formData = {
                "phone_number": $scope.formData.phone_number,
            };
            $.callAJAX({
                url: '/site/forgot-password',
                method: 'POST',
                data: formData,
                callbackSuccess: function (res) {
                    if (res.success) {
                        toastr.success(res.message);
                        // setTimeout(function () {
                        //     window.location.href = res.login_url;
                        // }, 10000);
                    } else {
                        toastr.error(res.message);
                        $scope.formData.error = res.error;
                        $scope.$apply();
                    }
                },
                callbackFail: function (res, message) {
                }
            });
        };
        $scope.init = function () {
        };
    }]
);

controllers.controller('ResendVerifyController', ['$scope', '$location', '$window',
    function ($scope, $location, $window) {
        $scope.formData = {};
        $scope.toResendVerify = function () {
            $scope.formData.error = null;
            var formData = {
                "phone_number": $scope.formData.phone_number,
            };
            $.callAJAX({
                url: '/site/resend-verify',
                method: 'POST',
                data: formData,
                callbackSuccess: function (res) {
                    if (res.success) {
                        toastr.success(res.message);
                        setTimeout(function () {
                            window.location.href = res.login_url;
                        }, 5000);
                    } else {
                        toastr.error(res.message);
                        $scope.formData.error = res.error;
                        $scope.$apply();
                    }
                },
                callbackFail: function (res, message) {
                }
            });
        };
        $scope.init = function () {
        };
    }]
);

controllers.controller('SignUpController', ['$scope', '$location', '$window',
    function ($scope, $location, $window) {
        $scope.signUpForm = {};
        $scope.getHobbySelected = function () {
            var hobbies = [];
            for (var id in $scope.signUpForm.hobbies) {
                if ($scope.signUpForm.hobbies[id]) {
                    hobbies.push(id);
                }
            }
            return hobbies;
        };
        $scope.getShopSelected = function () {
            var shops = [];
            for (var id in $scope.signUpForm.used_service_shop) {
                if ($scope.signUpForm.used_service_shop[id]) {
                    shops.push(id);
                }
            }
            return shops;
        };
        $scope.toSignUp = function () {
            $scope.signUpForm.error = null;
            var formData = {
                // "country_code": $scope.signUpForm.country_code,
                "phone_number": $scope.signUpForm.phone_number,
                "email": $scope.signUpForm.email,
                "full_name": $scope.signUpForm.full_name,
                "password": $scope.signUpForm.password,
                "re_password": $scope.signUpForm.re_password,
                // "used_service_shop": $scope.getShopSelected(),
                // "hobbies": $scope.getHobbySelected(),
                // "invite_code": $scope.signUpForm.invite_code,
            };
            $.callAJAX({
                url: '/site/sign-up',
                method: 'POST',
                data: formData,
                callbackSuccess: function (res) {
                    if (res.success) {
                        console.log(1);
                        toastr.success(res.message);
                    } else {
                        console.log(res);
                        $scope.signUpForm.error = res.error;
                        $scope.$apply();
                    }
                },
                callbackFail: function (res, status) {
                    console.log(3);
                }
            });
        };

        $scope.init = function (formData) {
            $scope.signUpForm = formData;
        }
    }]
);

controllers.controller('MemberController', ['$scope', '$location', '$window', '$uibModal',
    function ($scope, $location, $window, $uibModal) {
        $scope.openBookingHistory = function () {
            var modalWriteSMSInstance = $uibModal.open({
                animation: true,
                ariaLabelledBy: 'modal-title',
                ariaDescribedBy: 'modal-body',
                templateUrl: 'modal-booking-history.html',
                controller: function ($scope, $uibModalInstance) {
                    $scope.bookingHistories = [];
                    $scope.loadBookingHistory = function (isShowProgress = true) {
                        $.callAJAX({
                            url: '/profile/load-booking-history',
                            method: 'GET',
                            data: {},
                            callbackSuccess: function (res) {
                                if (res.success) {
                                    $scope.bookingHistories = res.data;
                                    $scope.$apply();
                                } else {
                                    toastr.error(res.message);
                                }
                            },
                            callbackFail: function (res, status) {
                                toastr.error(status);
                            }
                        }, isShowProgress);
                    };
                    $scope.toEditBooking = function (booking) {
                        $.callAJAX({
                            url: '/booking/load-booked-booking',
                            method: 'GET',
                            data: {
                                booking_id: booking.booking_id,
                            },
                            callbackSuccess: function (res) {
                                if (res.success) {
                                    var modalBookingOnlineEdit = $uibModal.open({
                                        animation: true,
                                        ariaLabelledBy: 'modal-title',
                                        ariaDescribedBy: 'modal-body',
                                        templateUrl: 'modal-booking-online-edit.html',
                                        size: 'md',
                                        controller: function ($scope, $uibModalInstance, booking, formData) {
                                            $scope.booking = booking;
                                            console.log(booking)
                                            $scope.editBookingForm = formData;
                                            $scope.editBookingForm.course_id = formData.courseInfo.course_id;
                                            $scope.courses = {};
                                            $scope.duration_minute = booking.slotInfo.duration_minute + " phút";
                                            var courseId, durationMinute, response = [], minutes = {};
                                            $scope.getSelectedCourse = function () {
                                                if ($scope.editBookingForm != undefined && $scope.editBookingForm.coursesInfo != undefined) {
                                                    for (var index = 0; index < $scope.editBookingForm.coursesInfo.length; index++) {
                                                        if (
                                                            $scope.editBookingForm.coursesInfo[index].course_id == $scope.editBookingForm.course_id
                                                        ) {
                                                            return $scope.editBookingForm.coursesInfo[index];
                                                        }
                                                    }
                                                }
                                                return null;
                                            };
                                            $scope.toCalCost = function () {
                                                var course = $scope.getSelectedCourse();
                                                if (course) {
                                                    var cost = course.price;
                                                    return cost > 0 ? cost : 0;
                                                }
                                                return 0;
                                            }

                                            $scope.changeCourseIdBookingOnline = function () {
                                                var flag = false, durationMinute = 0;
                                                $scope.durationMinutes = minutes[$scope.editBookingForm.course_id];
                                                if ($scope.durationMinutes != undefined) {
                                                    $scope.durationMinutes.forEach(function (val) {
                                                        if (val.id == $scope.editBookingForm.slotInfo.duration_minute) {
                                                            flag = true;
                                                            return;
                                                        }
                                                    });
                                                    durationMinute = $scope.durationMinutes[0].id;
                                                }
                                                $scope.editBookingForm.duration_minute = flag ?
                                                    $scope.editBookingForm.slotInfo.duration_minute :
                                                    durationMinute;
                                                $scope.toCalCost();
                                            };

                                            $scope.changeCourseIdBookingOnline();

                                            $scope.saveEditBooking = function () {
                                                $scope.editBookingForm.error = null;
                                                var data = {
                                                    course_id: formData.course_id,
                                                    comment: booking.comment,
                                                };
                                                $.callAJAX({
                                                    url: '/booking/update-online-booking?booking_id=' + booking.booking_id,
                                                    method: 'POST',
                                                    data: data,
                                                    callbackSuccess: function (res) {
                                                        if (res.success) {
                                                            toastr.success(res.message);
                                                            $uibModalInstance.close(res);
                                                        } else {
                                                            toastr.error(res.message);
                                                            $scope.editBookingForm.error = res.error;
                                                            $scope.$apply();
                                                        }
                                                    },
                                                });
                                            };
                                            $scope.closeModal = function () {
                                                $uibModalInstance.dismiss('cancel');
                                            };
                                        },
                                        resolve: {
                                            booking: function () {
                                                return booking;
                                            },
                                            formData: function () {
                                                return res.data;
                                            }
                                        }
                                    });
                                    modalBookingOnlineEdit.result.then(function () {
                                    }, function () {
                                    });
                                }
                            }
                        });
                    }
                    $scope.toCancelBooking = function (booking) {
                        console.log("To cancel booking", booking);
                        if (booking.isCancelableForUser == false || booking.isCancelableForUser == undefined) {
                            return false;
                        }
                        if (confirm("Bạn muốn hủy lượt đặt này chứ？")) {
                            $.callAJAX({
                                url: '/profile/cancel-booking',
                                method: 'GET',
                                data: {
                                    id: booking.id,
                                    shop_id: booking.shop_id
                                },
                                callbackSuccess: function (res) {
                                    if (res.success) {
                                        toastr.success(res.message);
                                        $scope.loadBookingHistory();
                                    } else {
                                        toastr.error(res.message);
                                    }
                                },
                                callbackFail: function (res, status) {
                                    toastr.error(status);
                                }
                            });
                        }
                    };
                    $scope.closeModal = function () {
                        $uibModalInstance.dismiss('cancel');
                    };
                    $scope.loadBookingHistory();
                    $(document).on("bookingCanceled", function (event, data) {
                        if (data.data.shop_id == $scope.shop_id) {
                            console.log("bookingCanceled", data);
                            $scope.loadBookingHistory(false);
                        }
                    });
                    // $(document).on("shopCalendarSlotChanged", function (event, data) {
                    //     if (data.data.shop_id == $scope.shop_id) {
                    //         $scope.loadBookingHistory(false);
                    //     }
                    // });
                    $(document).on("shopCalendarSlotChanged", function (event, data) {
                        $scope.loadBookingHistory(false);
                    });
                },
                size: "md",
                resolve: {}
            });
            modalWriteSMSInstance.result.then(function () {
            }, function () {
            });
        };

        $scope.openHobbyManage = function () {
            var modalWriteSMSInstance = $uibModal.open({
                animation: true,
                ariaLabelledBy: 'modal-title',
                ariaDescribedBy: 'modal-body',
                templateUrl: 'modal-favorite-list.html',
                controller: function ($scope, $uibModalInstance) {
                    $scope.hobbies = [];
                    $scope.openHobbyFormModal = function () {
                        var modalWriteSMSInstance = $uibModal.open({
                            animation: true,
                            ariaLabelledBy: 'modal-title',
                            ariaDescribedBy: 'modal-body',
                            templateUrl: 'modal-favorite-form.html',
                            controller: function ($scope, $uibModalInstance, hobbies) {
                                $scope.hobbies = hobbies;
                                $scope.listHobby = null;
                                $scope.loadListHobby = function () {
                                    $.callAJAX({
                                        url: '/profile/load-form-hobbies',
                                        method: 'GET',
                                        data: {},
                                        callbackSuccess: function (res) {
                                            if (res.success) {
                                                $scope.listHobby = res.data;
                                                $scope.$apply();
                                            } else {
                                                toastr.error(res.message);
                                            }
                                        },
                                        callbackFail: function (res, status) {
                                            toastr.error(status);
                                        }
                                    });
                                };
                                $scope.saveHobby = function () {
                                    var ids = [];
                                    for (var i = 0; i < $scope.listHobby.length; i++) {
                                        if ($scope.listHobby[i].isSelected) {
                                            ids.push($scope.listHobby[i].data_id);
                                        }
                                    }
                                    $.callAJAX({
                                        url: '/profile/save-hobbies',
                                        method: 'POST',
                                        data: {
                                            ids: ids
                                        },
                                        callbackSuccess: function (res) {
                                            if (res.success) {
                                                toastr.success(res.message);
                                                $scope.closeModal();
                                            } else {
                                                toastr.error(res.message);
                                            }
                                        },
                                        callbackFail: function (res, message) {
                                            toastr.error(message);
                                        }
                                    });
                                };
                                $scope.closeModal = function () {
                                    $uibModalInstance.dismiss('cancel');
                                };
                                $scope.loadListHobby();
                            },
                            size: "md",
                            resolve: {
                                hobbies: function () {
                                    return $scope.hobbies;
                                }
                            }
                        });
                        modalWriteSMSInstance.result.then(function () {
                            $scope.loadMemberHobby();
                        }, function () {
                            $scope.loadMemberHobby();
                        });
                    };
                    $scope.loadMemberHobby = function () {
                        $.callAJAX({
                            url: '/profile/load-member-hobbies',
                            method: 'GET',
                            data: {},
                            callbackSuccess: function (res) {
                                if (res.success) {
                                    $scope.hobbies = res.data;
                                    $scope.$apply();
                                } else {
                                    toastr.error(res.message);
                                }
                            },
                            callbackFail: function (res, status) {
                                toastr.error(status);
                            }
                        });
                    };
                    $scope.closeModal = function () {
                        $uibModalInstance.dismiss('cancel');
                    };
                    $scope.loadMemberHobby();
                },
                size: "md",
                resolve: {}
            });
            modalWriteSMSInstance.result.then(function () {
            }, function () {
            });
        };
    }
]);

controllers.controller('ShopController', ['$scope', '$filter', '$location', '$window', '$uibModal',
    function ($scope, $filter, $location, $window, $uibModal) {
        $scope.shop_id = null;
        $scope.worker_id = null;
        $scope.shopInfo = null;
        $scope.showBookedSlot = true;

        $scope.toScrollToTomorrowBooking = function () {
            // if($([document.documentElement, document.body]).is('[disabled=disabled]') == false) {
            //     $(this).attr('disabled', true);
            //     $(this).attr('title', 'ダウンロード中からお待ちください。');
            //     $("body").css("cursor", "progress");
                $([document.documentElement, document.body]).animate({
                    scrollTop: $("#tomorrow-booking-area").offset().top
                }, 2000);
            // }
        };

        $scope.loadShopInfo = function () {
            $.callAJAX({
                url: '/shop/load-info?shop_id=' + $scope.shop_id,
                method: 'GET',
                data: {},
                callbackSuccess: function (res) {
                    if (res.success) {
                        $scope.shopInfo = res.data;
                        $scope.$apply();
                    } else {
                        toastr.error(res.message);
                    }
                },
                callbackFail: function (res, status) {
                    toastr.error(status);
                }
            }, false);
        };

        $scope.openOnlineBookingModal = function (slot) {
            if (slot.isCanBooking) {
                $.callAJAX({
                    url: '/booking/load-online-booking',
                    method: 'GET',
                    data: {
                        slot_id: slot.slot_id
                    },
                    callbackSuccess: function (res) {
                        if (res.success) {
                            var durationMinute = res.data.durationMinute;
                            var modalBookingOnline = $uibModal.open({
                                animation: true,
                                ariaLabelledBy: 'modal-title',
                                ariaDescribedBy: 'modal-body',
                                templateUrl: 'modal-booking-online.html',
                                controller: function ($scope, $uibModalInstance, slot, formData) {
                                    $scope.durationMinute = durationMinute;
                                    $scope.slot = slot;
                                    $scope.onlineBookingForm = formData;
                                    $scope.courses = {};
                                    var minutes = {};
                                    $.each($scope.onlineBookingForm.courses, function (key, value) {

                                        if ($scope.onlineBookingForm.isBookingSortTime == 0) {
                                                $scope.courses[value.course_id] = {
                                                    'course_id': value.course_id,
                                                    'course_name': value.course_name,
                                                };
                                            }
                                    });

                                    $scope.getSelectedCourse = function () {
                                        if ($scope.onlineBookingForm != undefined && $scope.onlineBookingForm.courses != undefined) {
                                            for (var index = 0; index < $scope.onlineBookingForm.courses.length; index++) {
                                                if (
                                                    $scope.onlineBookingForm.courses[index].course_id == $scope.onlineBookingForm.course_id
                                                ) {
                                                    return $scope.onlineBookingForm.courses[index];
                                                }
                                            }
                                        }
                                        return null;
                                    };
                                    $scope.toCalCost = function () {
                                        var course = $scope.getSelectedCourse();
                                        if (course) {
                                            var cost = course.price;
                                            return cost > 0 ? cost : 0;
                                        }
                                        return 0;
                                    }

                                    $scope.changeCourseIdBookingOnline = function () {
                                        var flag = false, durationMinute = 0;
                                        $scope.durationMinutes = minutes[$scope.onlineBookingForm.course_id];
                                        if ($scope.durationMinutes != undefined) {
                                            $scope.durationMinutes.forEach(function (val) {
                                                if (val.id == $scope.onlineBookingForm.slotInfo.duration_minute) {
                                                    flag = true;
                                                    return;
                                                }
                                            });
                                            durationMinute = $scope.durationMinutes[0].id;
                                        }
                                        $scope.onlineBookingForm.duration_minute = flag ?
                                            $scope.onlineBookingForm.slotInfo.duration_minute :
                                            durationMinute;
                                        $scope.toCalCost();
                                    };

                                    $scope.changeCourseIdBookingOnline();

                                    $scope.closeModal = function () {
                                        $uibModalInstance.dismiss('cancel');
                                    };

                                    $scope.openBookingOnlineConfirmModal = function () {
                                        var durationMinute = $scope.durationMinute;
                                        var modalBookingOnlineConfirm = $uibModal.open({
                                            animation: true,
                                            ariaLabelledBy: 'modal-title',
                                            ariaDescribedBy: 'modal-body',
                                            templateUrl: 'modal-booking-online-confirm.html',
                                            controller: function ($scope, $uibModalInstance, slot, onlineBookingForm, totalCost) {
                                                $scope.durationMinute = durationMinute;
                                                console.log($scope.durationMinute);
                                                $scope.slot = slot;
                                                $scope.totalCost = totalCost;
                                                $scope.onlineBookingForm = onlineBookingForm;
                                                $scope.closeModal = function () {
                                                    $uibModalInstance.dismiss('cancel');
                                                };
                                                $scope.slot.end_time = moment($scope.slot.start_time, "HH:mm")
                                                    .add($scope.durationMinute, 'minutes')
                                                    .format('HH:mm');
                                                $scope.saveOnlineBooking = function () {
                                                    $scope.onlineBookingForm.error = null;
                                                    var coupon_codes = [], isChangeDurationMinute;
                                                    angular.forEach($scope.onlineBookingForm.selectedCoupons, function (selectedCoupon) {
                                                        coupon_codes.push(selectedCoupon.coupon_code);
                                                    });
                                                    console.log(coupon_codes)
                                                    isChangeDurationMinute = $scope.slot.duration_minute != $scope.onlineBookingForm.duration_minute ? 1 : 0;
                                                    var data = {
                                                        after_minute: $scope.onlineBookingForm.after_minute,
                                                        course_id: $scope.onlineBookingForm.course_id,
                                                        coupon_codes: coupon_codes,
                                                        comment: $scope.onlineBookingForm.comment,
                                                        duration_minute: $scope.onlineBookingForm.duration_minute,
                                                        end_time: $scope.slot.end_time,
                                                        is_change_duration_minute: isChangeDurationMinute
                                                    };
                                                    $.callAJAX({
                                                        url: '/booking/save-online-booking?slot_id=' + slot.slot_id,
                                                        method: 'POST',
                                                        data: data,
                                                        callbackSuccess: function (res) {
                                                            if (res.success) {
                                                                toastr.success(res.message);
                                                                $uibModalInstance.close(res);
                                                            } else {
                                                                toastr.error(res.message);
                                                                $scope.onlineBookingForm.error = res.error;
                                                                $scope.$apply();
                                                            }
                                                        },
                                                        callbackFail: function (res, message) {
                                                            toastr.error(message);
                                                        }
                                                    });
                                                };
                                            },
                                            size: "md",
                                            resolve: {
                                                slot: function () {
                                                    return $scope.slot;
                                                },
                                                onlineBookingForm: function () {
                                                    return $scope.onlineBookingForm;
                                                },
                                                totalCost: function () {
                                                    return $scope.toCalCost();
                                                },
                                            }
                                        });
                                        modalBookingOnlineConfirm.result.then(function () {
                                            $scope.closeModal();
                                        }, function () {
                                        });
                                    };
                                },
                                size: "md",
                                resolve: {
                                    slot: function () {
                                        return slot;
                                    },
                                    formData: function () {
                                        return res.data;
                                    }
                                }
                            });
                            modalBookingOnline.result.then(function () {
                            }, function () {
                            });
                        } else {
                            toastr.error(res.message);
                        }
                    },
                    callbackFail: function (res, message) {
                        toastr.error(message);
                    }
                });
            }
        };

        $scope.slotManager = {
            slotsData: {},
            loadSlot: $.throttle(100, function (shop_id, date) {
                $.callAJAX({
                    url: '/booking/load-shop-slot',
                    method: 'GET',
                    data: {
                        shop_id: shop_id,
                        date: date,
                    },
                    callbackSuccess: function (res) {
                        if (res.success) {
                            var today = moment().format('YYYY-MM-DD');
                            var tomorrow = moment().add(1, 'days').format('YYYY-MM-DD');
                            $scope.slotManager.checkNewWorker(res.data, today);
                            $scope.slotManager.checkNewWorker(res.data, tomorrow);

                            if (Object.keys($scope.slotManager.slotsData).length === 0 && $scope.worker_id) {
                                var _date = today;
                                if ($scope.arraySome(res.data[tomorrow].workers, function (worker) {
                                    return worker.worker_id.toString() === $scope.worker_id
                                })) {
                                    _date = tomorrow;
                                }

                                if (_date === tomorrow && $scope.checkBookingTomorrow()) {
                                    $scope.showTomorrowSlot = true;
                                    $scope.$apply();
                                }
                                angular.element(document).ready(function () {
                                    $([document.documentElement, document.body]).animate({
                                        scrollTop: $("#worker_" + _date + "_" + $scope.worker_id).offset().top - 100,
                                    }, 1000);
                                });
                            }
                            $scope.slotManager.slotsData = res.data;
                            console.log($scope.slotManager.slotsData)
                            $scope.$apply();
                        } else {
                            toastr.error(res.message);
                        }
                    },
                    callbackFail: function (res, message) {
                        toastr.error(message);
                    }
                }, false);
            }),
            filterCanBooking: function (slot) {
                return !$scope.showBookedSlot || slot.isCanBooking;
            },
            checkNewWorker: function (newData, date) {
                if (!$scope.slotManager.slotsData[date] || !$scope.slotManager.slotsData[date].workers) {
                    return;
                }
                var oldWorkerIds = $scope.slotManager.slotsData[date].workers.map(v => v.worker_id);
                var newWorkerIds = newData[date].workers.map(v => v.worker_id);
                if (oldWorkerIds.length === newWorkerIds.length) {
                    return;
                }
                var newWorkerIndex = newWorkerIds.findIndex(id => !oldWorkerIds.includes(id));
                toastr.info(newData[date].workers[newWorkerIndex].worker_name + "vừa có lịch làm việc, vui lòng tải lại trang");
            },
            isEmpty: function () {
                var isEmpty = true;
                for (var user_id in $scope.slotManager.slotsData) {
                    if ($scope.slotManager.slotsData.hasOwnProperty(user_id)) {
                        if (typeof $scope.slotManager.slotsData[user_id] !== "function") {
                            if ($filter('filter')($scope.slotManager.slotsData[user_id], $scope.slotManager.filterCanBooking).length > 0) {
                                isEmpty = false;
                                break;
                            }
                        }
                    }
                }
                return isEmpty;
            }
        };

        $scope.arraySome = function (array, func) {
            for (var index = 0; index < array.length; index++) {
                if (func(array[index])) {
                    return true;
                }
            }
            return false;
        };

        $scope.convertTime = function (data) {
            var times = data.start_time.split(':');
            times[0] = parseInt(times[0]) < 10 ? '0' + parseInt(times[0]) : times[0];
            times[1] = parseInt(times[1]) < 10 ? '0' + parseInt(times[1]) : times[1];
            data.start_time = String(times[0] + ':' + times[1]);
            return data;
        };

        $scope.checkBookingTomorrow = function () {
            if ($scope.bookingTomorrowAt.length > 0) {
                var now = moment().tz($scope.timezone);
                var nowDate = now.format("YYYY-MM-DD"),
                    nowHour = parseInt(now.format("H")),
                    nowMinute = parseInt(now.format("m") / 5) * 5;
                var result = $scope.bookingTomorrowAt.split(":");
                var hour = parseInt(result[0]),
                    minute = parseInt(result[1]);
                // console.log("now date", nowDate, nowHour, nowMinute, hour, minute);
                if ((nowHour * 60 + nowMinute) >= (hour * 60 + minute)) {
                    return true;
                }
            }
            return false;
        }

        $scope.init = function (shop_id, worker_id, date, bookingTomorrowAt, timezone) {
            $scope.shop_id = shop_id;
            $scope.worker_id = worker_id;
            $scope.date = date;
            $scope.bookingTomorrowAt = bookingTomorrowAt;
            $scope.timezone = timezone;
            $scope.showTomorrowSlot = false;
            $scope.loadShopInfo();
            $scope.slotManager.loadSlot(shop_id, date);

            setInterval(function () {
                if ($scope.checkBookingTomorrow()) {
                    $scope.showTomorrowSlot = true;
                    $scope.$apply();
                } else {
                    $scope.showTomorrowSlot = false;
                    $scope.$apply();
                }
            }, 100);
            $(document).on("shopCalendarSlotChanged", function (event, data) {
                if (data.data.shop_id == shop_id) {
                    $scope.slotManager.loadSlot(shop_id, date);
                }
            });
            $(document).on("shopConfigChanged", function (event, data) {
                $scope.loadShopInfo();
            });
        };
    }
]);
