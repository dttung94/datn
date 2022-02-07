window.angularApp.controller('ShopController', ['$scope', '$http', '$window', '$location', '$filter',
    function ($scope, $http, $window, $location, $filter) {
        $scope.shopFormData = {};
        $scope.init = function (data) {
            console.log(data);
            console.log("Shop form data", data);
            $scope.shopFormData = data;
        }
    }]);