window.angularApp = angular.module('BackendApp', [
    'mgcrea.ngStrap',
    'ngSanitize',
    "ds.clock",
    'ngDragDrop',
    'ui.bootstrap',
    'ui.select2',
    '720kb.tooltips',
]);
window.angularApp.factory('SMSService', function ($uibModal) {
    return {
        composerSMS: function (type, callbackOK, callbackCancel) {
            var modalWriteSMSInstance = $uibModal.open({
                animation: true,
                ariaLabelledBy: 'modal-title',
                ariaDescribedBy: 'modal-body',
                templateUrl: 'modal-write-sms-message.html',
                controller: function ($scope, $uibModalInstance, type) {
                    $scope.type = type;
                    $scope.smsForm = null;
                    $scope.init = function () {
                        $.callAJAX({
                            url: "/calendar/sms/load-template",
                            method: 'GET',
                            data: {
                                type: type,
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
                    };
                    $scope.sendSMS = function () {
                        $uibModalInstance.close($scope.smsForm.content);
                    };
                    $scope.cancel = function () {
                        $uibModalInstance.dismiss('cancel');
                    };
                    $scope.init();
                },
                size: "md",
                resolve: {
                    type: function () {
                        return type;
                    }
                }
            });
            modalWriteSMSInstance.result.then(function (smsContent) {
                if (callbackOK) {
                    callbackOK(type, smsContent);
                }
            }, function () {
                if (callbackCancel) {
                    callbackCancel(type);
                }
            });
        }
    };
});

window.angularApp.factory('MailService', function ($uibModal) {
    let titleEmail = '';
    return {
        composerMail: function (type, callbackOK, callbackCancel, callbackTitle) {
            var modalWriteMailInstance = $uibModal.open({
                animation: true,
                ariaLabelledBy: 'modal-title',
                ariaDescribedBy: 'modal-body',
                templateUrl: 'modal-write-email-message.html',
                controller: function ($scope, $uibModalInstance, type) {
                    $scope.type = type;
                    $scope.smsForm = null;
                    $scope.init = function () {
                        $.callAJAX({
                            url: "/calendar/sms/load-mail-template",
                            method: 'GET',
                            data: {
                                type: type,
                            },
                            callbackSuccess: function (res) {
                                console.log(res.data);
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
                    };
                    $scope.sendSMS = function () {
                        $uibModalInstance.close($scope.smsForm.content);
                        titleEmail = $scope.smsForm.title;
                    };
                    $scope.cancel = function () {
                        $uibModalInstance.dismiss('cancel');
                    };
                    $scope.init();
                },
                size: "md",
                resolve: {
                    type: function () {
                        return type;
                    }
                }
            });
            modalWriteMailInstance.result.then(function (smsContent) {
                if (callbackOK) {
                    callbackOK(type, smsContent, titleEmail);
                }
            }, function () {
                if (callbackCancel) {
                    callbackCancel(type);
                }
            });
        }
    };
});

window.angularApp.directive('countDown', ['$interval', function ($interval) {
    return {
        restrict: "E",
        scope: {
            fromNumber: '=',
            callbackDone: '&',
        },
        link: function (scope, element, attrs) {
            let fromNumber = scope.fromNumber;
            element.text(fromNumber > 9 ? fromNumber : "0" + fromNumber);
            var countDown = $interval(function () {
                fromNumber = fromNumber - 1;
                var elementCountDown =  document.getElementById(attrs.id);
                if (typeof(elementCountDown) == 'undefined' || elementCountDown == null) {
                    $interval.cancel(countDown);
                }
                if (fromNumber < 0) {
                    $interval.cancel(countDown);
                    if (scope.callbackDone) {
                        scope.callbackDone();
                    }
                } else {
                    element.text(fromNumber > 9 ? fromNumber : "0" + fromNumber);
                }
            }, 1000);
            return;
        }
    };
}]);

window.angularApp.directive('copyClipboard', ['$interval', function ($interval) {
    return {
        restrict: "A",
        scope: {},
        link: function (scope, element, attrs) {
            new Clipboard("[copy-clipboard='copy']");
            $(element).on("click", function () {
                toastr.info("Đã sao chép [" + $(this).data('clipboard-text') + "]");
            });
        }
    };
}]);

window.angularApp.directive('convertToNumber', function () {
    return {
        require: 'ngModel',
        link: function (scope, element, attrs, ngModel) {
            ngModel.$parsers.push(function (val) {
                return val != null ? parseInt(val, 10) : null;
            });
            ngModel.$formatters.push(function (val) {
                return val != null ? '' + val : null;
            });
        }
    };
});

window.angularApp.directive('ngEnter', function () {
    return function (scope, element, attrs) {
        element.bind("keydown keypress", function (event) {
            if (event.which === 13) {
                scope.$apply(function () {
                    scope.$eval(attrs.ngEnter);
                });
                event.preventDefault();
            }
        });
    };
});
