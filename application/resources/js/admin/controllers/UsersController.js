'use strict';

angular.module('app').controller('UsersController', ['$scope', '$rootScope', '$state', '$mdDialog', 'utils', 'users','$filter', function($scope, $rootScope, $state, $mdDialog, utils, users,$filter) {
    $scope.users = users;

    //users search query
    $scope.search = { query: '' };

    $scope.selectedItems = [];

    $scope.isItemSelected = function(item) { 
        return $scope.selectedItems.indexOf(item) > -1;
    };

    $scope.select = function(item) {
        var idx = $scope.selectedItems.indexOf(item);
        if (idx > -1) $scope.selectedItems.splice(idx, 1);
        else $scope.selectedItems.push(item);
    };
    $scope.roles = [];
    users.roles().success(function(data) {
        $scope.roles = data;
    });
    
    $scope.selectedRole = { id: 1, name: 'Bob' };


    $scope.locations = [];
    users.locations().success(function(data) {
        $scope.locations = data;
    });
    //$scope.userModel.locations = [{"id":"1","name":"Cream of ALAMEDA","created_at":"-0001-11-30 00:00:00","updated_at":"2016-09-06 10:56:05","user_id":"1","location_id":"1","checked":true,"pivot":{"user_id":"1","location_id":"1"}}];
    $scope.selectedLocations = function () {
        $scope.userModel.locations = $filter('filter')($scope.locations, {checked: true});
    }

    //filter model for checkbox to filter photos attach/not attached to user
    $scope.showNotAttachedPhotosOnly = false;

    $scope.deleteUsers = function() {
        if (utils.isDemo) {
            utils.showToast('Sorry, you can\'t do that on demo site.');
            $scope.selectedItems = [];
            return;
        }

        users.delete($scope.selectedItems).success(function() {
            $scope.selectedItems = [];
            $scope.paginate($scope.params);
        }).error(function(data) {
            utils.showToast(data);
        })
    };

    $scope.toggleAllUsers = function() {

        //all items already selected, deselect all
        if ($scope.selectedItems.length === users.all.length) {
            $scope.selectedItems = [];
        }

        //all items aren't selected, copy all users array to selected items
        else {
            $scope.selectedItems = users.all.slice();
        }
    };

    $scope.showCreateUserModal = function($event) {
        $mdDialog.show({
            templateUrl: 'assets/views/modals/create-user.html?key=1',
            clickOutsideToClose: true,
            controllerAs: 'ctrl',
            controller: function() { this.parent = $scope },
            targetEvent: $event,
        });
    };

    $scope.showUpdateUserModal = function(user, $event) {
        $scope.userModel = angular.copy(user);
        delete $scope.userModel.password;

        if (utils.isDemo) {
            $scope.userModel.email = 'Hidden on demo site';
        }

        for (var key in $scope.locations) {
            if (!$scope.locations.hasOwnProperty(key)) continue;
            var location = $scope.locations[key];
            location.checked = false;
            console.log(location);

            for (var key2 in $scope.userModel.locations) {
                if (!$scope.userModel.locations.hasOwnProperty(key2)) continue;
                var loc = $scope.userModel.locations[key2];
                if(loc.location_id == location.id){
                    console.log(location);
                    location.checked = true;
                }
                
            }
            //console.log($scope.userModel.locations.filter(function (data) { if(data.location_id, location.id) {location.checked = true; return 1;}}))
        }

        //console.log($scope.locations);
        
        // console.log($scope.locations.filter(function (location) { return location.id == "1" }));

       

        // for (var key in $scope.userModel.locations) {
        //     if (!$scope.userModel.locations.hasOwnProperty(key)) continue;

        //         var obj = $scope.userModel.locations[key];
        //         $scope.locations.filter(function (location) { if(location.id == obj.id) { location.checked = true; return 1;}  });
                
                
        // }

        // //console.log($filter('filter')($scope.userModel.locations, {checked: "true"}));

        // //$scope.locations  += $filter('filter')($scope.userModel.locations, {checked: "true"});
        
        // console.log($scope.locations);
        $mdDialog.show({
            templateUrl: 'assets/views/modals/update-user.html?key=1',
            clickOutsideToClose: true,
            controllerAs: 'ctrl',
            controller: function() { this.parent = $scope },
            targetEvent: $event,
        });
    };

    $scope.updateUser = function() {
        if (utils.isDemo) {
            utils.showToast('Sorry, you can\'t do that on demo site.');
            $scope.selectedItems = [];
            return;
        }

        console.log($scope.userModel);

        users.updateAccountSettings($scope.userModel, $scope.userModel.id).success(function() {
            $mdDialog.hide();
            utils.showToast('updatedUserSuccessfully', true);
            $scope.paginate($scope.params);
            $scope.selectedItems = [];
        }).error(function(data) {
            $scope.setErrors(data);
        });
    };

    $scope.createNewUser = function() {
        if (utils.isDemo) {
            utils.showToast('Sorry, you can\'t do that on demo site.');
            $scope.selectedItems = [];
            return;
        }

        users.register($scope.userModel).success(function() {
            $mdDialog.hide();
            utils.showToast('createdUserSuccessfully', true);
            $scope.paginate($scope.params);
            $scope.errors = [];
        }).error(function(data) {
            $scope.setErrors(data);
        });
    };

    $scope.paginate = function(params) {
        if ($scope.usersAjaxInProgress) return;

        $scope.usersAjaxInProgress = true;

        users.paginate(params).success(function(data) {
            $scope.items = data.data;
            $scope.totalItems = data.total;

            $scope.usersAjaxInProgress = false;
        })
    };

    $scope.paginate($scope.params);
}]);
