mobileapp.controller('UsersLoginController', function($scope, $api) {

	$scope.auth = {};

	$scope.login = function(){
		$api('login', $scope.auth, function(e, r){
			if (e) {
				// Login failed
			} else {
				window.location.reload();
			}
		});
	};
	
});