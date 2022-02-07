window.angularApp.controller('PriceController', ['$scope', '$http', '$window', '$location', '$filter', '$uibModal',
    function ($scope, $http, $window, $location, $filter, $uibModal) {
        $scope.data = {};
        $scope.course = {
            toLoadCourseData: function () {
                $.callAJAX({
                    url: '/data/course/load-course-data',
                    method: 'GET',
                    data: {},
                    callbackSuccess: function (res) {
                        if (res.success) {
                            $scope.data = res.data;
                            $scope.$apply();
                        }
                    },
                    callbackFail: function (res, status) {
                    }
                });
            },
            toOpenCourseModal: function (course_id = null) {
                $.callAJAX({
                    url: "/data/course/load-course-form?course_id=" + course_id,
                    method: 'POST',
                    data: angular.copy($scope.bookingForm),
                    callbackSuccess: function (res) {
                        if (res.success) {
                            var modalInstance = $uibModal.open({
                                animation: true,
                                ariaLabelledBy: 'modal-title',
                                ariaDescribedBy: 'modal-body',
                                templateUrl: 'modal-course-form.html',
                                controller: function ($scope, $uibModalInstance, courseForm, course_id) {
                                    $scope.course_id = course_id;
                                    $scope.courseForm = courseForm;
                                    $scope.saveCourse = function () {
                                        $.callAJAX({
                                            url: "/data/course/save-course-info?course_id=" + course_id,
                                            method: 'POST',
                                            data: angular.copy($scope.courseForm),
                                            callbackSuccess: function (res) {
                                                if (res.success) {
                                                    toastr.success(res.message);
                                                    //todo close modal
                                                    $scope.closeModal();
                                                } else {
                                                    toastr.error(res.message);
                                                    $scope.courseForm.error = res.error;
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
                                    $scope.closeModal = function () {
                                        $uibModalInstance.dismiss('cancel');
                                    };
                                },
                                size: "sm",
                                resolve: {
                                    courseForm: function () {
                                        return res.data;
                                    },
                                    course_id: function () {
                                        return course_id;
                                    }
                                }
                            });
                            modalInstance.result.then(function () {
                                $scope.course.toLoadCourseData();
                            }, function () {
                                $scope.course.toLoadCourseData();
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
            toDeleteCourse: function (course_id) {
                if (confirm("Bạn muốn xóa bỏ chứ?")) {
                    $.callAJAX({
                        url: "/data/course/delete-course-info?course_id=" + course_id,
                        method: 'POST',
                        data: {},
                        callbackSuccess: function (res) {
                            if (res.success) {
                                toastr.success(res.message);
                                $scope.course.toLoadCourseData();
                            } else {
                                toastr.error(res.message);
                            }
                        },
                        callbackFail: function (status, message) {
                            if (status.status == 403) {
                                message = "このアクションの実行は許可されていません。"
                            }
                            toastr.error(message);
                        }
                    });
                }
            }
        };
        $scope.coursePrice = {
            toSaveCoursePrice: function (item) {
                $.callAJAX({
                    url: '/data/price/save-course-price',
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
            },
            toDeleteCoursePrice: function (items) {
                if (confirm("Bạn muốn xóa bỏ chứ ?")) {
                    $.callAJAX({
                        url: "/data/price/delete-course-price?course_id",
                        method: 'POST',
                        data: {
                            data: items
                        },
                        callbackSuccess: function (res) {
                            if (res.success) {
                                toastr.success(res.message);
                                $scope.course.toLoadCourseData();
                            } else {
                                toastr.error(res.message);
                            }
                        },
                        callbackFail: function (status, message) {
                            if (status.status == 403) {
                                message = "このアクションの実行は許可されていません。"
                            }
                            toastr.error(message);
                        }
                    });
                }
            },
            toOpenModalAddCoursePrice: function (course_id) {
                var modalInstance = $uibModal.open({
                    animation: true,
                    ariaLabelledBy: 'modal-title',
                    ariaDescribedBy: 'modal-body',
                    templateUrl: 'modal-course-price-form.html',
                    controller: function ($scope, $uibModalInstance, course_id) {
                        $scope.course_id = course_id;
                        $scope.formData = {};
                        $scope.saveCoursePrice = function () {
                            $.callAJAX({
                                url: "/data/price/create-course-price?course_id=" + course_id,
                                method: 'POST',
                                data: angular.copy($scope.formData),
                                callbackSuccess: function (res) {
                                    if (res.success) {
                                        toastr.success(res.message);
                                        //todo close modal
                                        $scope.closeModal();
                                    } else {
                                        toastr.error(res.message);
                                        $scope.formData.error = res.error;
                                    }
                                },
                                callbackFail: function (status, message) {
                                    if (status.status == 403) {
                                        message = "このアクションの実行は許可されていません"
                                    }
                                    toastr.error(message);
                                }
                            });
                        };
                        $scope.closeModal = function () {
                            $uibModalInstance.dismiss('cancel');
                        };
                    },
                    size: "sm",
                    resolve: {
                        course_id: function () {
                            return course_id;
                        }
                    }
                });
                modalInstance.result.then(function () {
                    $scope.course.toLoadCourseData();
                }, function () {
                    $scope.course.toLoadCourseData();
                });
            },
        };

        $scope.init = function () {
            $scope.course.toLoadCourseData();
        }
    }]
);