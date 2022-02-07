'use strict';

var ShopApp = angular.module('ShopApp', [
    'ngRoute',          //$routeProvider
    'mgcrea.ngStrap',   //bs-navbar, data-match-route directives
    'controllers',       //Our module frontend/web/js/controllers.js
    'ui.bootstrap',
]);

ShopApp.factory('authInterceptor', function ($q, $window, $location) {
    return {};
});


ShopApp.filter('formatTime', function ($filter) {
    return function (time, format) {
        var date = moment("1990-01-01T" + time + ":00Z").toDate();
        var result = $filter("date")(date, format);
        if (result != "Invalid Date") {
            return result;
        }
        return time;
    };
});

ShopApp.directive('convertToNumber', function () {
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

ShopApp.directive('ngEnter', function () {
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