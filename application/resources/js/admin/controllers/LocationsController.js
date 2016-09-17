'use strict';

angular.module('app').controller('LocationsController', ['$scope', '$rootScope', '$state', '$mdDialog', 'utils', 'locations','$filter', function($scope, $rootScope, $state, $mdDialog, utils, locations,$filter) {
    $scope.locations = locations;

    //locations search query
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


    //filter model for checkbox to filter photos attach/not attached to location
    $scope.showNotAttachedPhotosOnly = false;

    $scope.deleteLocations = function() {
        if (utils.isDemo) {
            utils.showToast('Sorry, you can\'t do that on demo site.');
            $scope.selectedItems = [];
            return;
        }

        locations.delete($scope.selectedItems).success(function() {
            $scope.selectedItems = [];
            $scope.paginate($scope.params);
        }).error(function(data) {
            utils.showToast(data);
        })
    };

    $scope.showCreateLocationModal = function($event) {
        $mdDialog.show({
            templateUrl: 'assets/views/modals/create-location.html?key=1',
            clickOutsideToClose: true,
            controllerAs: 'ctrl',
            controller: function() { this.parent = $scope },
            targetEvent: $event,
        });
    };

    $scope.showUpdateLocationModal = function(location, $event) {
        $scope.locationModel = angular.copy(location);
        delete $scope.locationModel.password;

        if (utils.isDemo) {
            $scope.locationModel.email = 'Hidden on demo site';
        }
        
        $mdDialog.show({
            templateUrl: 'assets/views/modals/update-location.html?key=1',
            clickOutsideToClose: true,
            controllerAs: 'ctrl',
            controller: function() { this.parent = $scope },
            targetEvent: $event,
        });
    };

    $scope.updateLocations = function() {
        if (utils.isDemo) {
            utils.showToast('Sorry, you can\'t do that on demo site.');
            $scope.selectedItems = [];
            return;
        }

        locations.updateLocation($scope.locationModel, $scope.locationModel.id).success(function() {
            $mdDialog.hide();
            utils.showToast('updatedLocationSuccessfully', true);
            $scope.paginate($scope.params);
            $scope.selectedItems = [];
        }).error(function(data) {
            $scope.setErrors(data);
        });
    };

    $scope.createNewLocation = function() {
        if (utils.isDemo) {
            utils.showToast('Sorry, you can\'t do that on demo site.');
            $scope.selectedItems = [];
            return;
        }

        locations.save($scope.locationModel).success(function() {
            $mdDialog.hide();
            utils.showToast('createdLocationSuccessfully', true);
            $scope.paginate($scope.params);
            $scope.errors = [];
        }).error(function(data) {
            $scope.setErrors(data);
        });
    };

    $scope.paginate = function(params) {
        if ($scope.locationsAjaxInProgress) return;

        $scope.locationsAjaxInProgress = true;

        locations.paginate(params).success(function(data) {
            $scope.items = data.data;
            $scope.totalItems = data.total;

            $scope.locationsAjaxInProgress = false;
        })
    };

    $scope.paginate($scope.params);
}]);
