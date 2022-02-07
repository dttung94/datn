window.angularApp.controller('ForumController', ['$scope', '$http', '$window', '$location', '$filter',
    function ($scope, $http, $window, $location, $filter) {
        $scope.forumFormData = {};
        $scope.init = function (data) {
            console.log(data);
            console.log("Forum form data", data);
            $scope.forumFormData = data;
        }
    }]);
