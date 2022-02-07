window.angularApp.controller('MainController', ['$scope', '$http', '$window',
    function ($scope, $http, $window) {
        $scope.totalMemberWaiting = 0;
        $(document).on("newMemberSignUp", function (event, data) {
            $scope.totalMemberWaiting = data.data.totalMemberWaiting;
            $scope.$apply();
        });
    }
]);

function getParamFromUrl(url, param) {
    return new URL(url).searchParams.get(param);
}

// dungnv2 add js remove character special e,+ from input number
$('.input-number-remove-special').keypress(function(evt) {
    if (evt.which != 8 && evt.which != 0 && evt.which < 48 || evt.which > 57)
    {
        evt.preventDefault();
    }
})
