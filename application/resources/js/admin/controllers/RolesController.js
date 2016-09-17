'use strict';

angular.module('app').controller('RolesController', ['$scope', '$rootScope', '$state', '$mdDialog', 'utils', 'roles', function($scope, $rootScope, $state, $mdDialog, utils, roles) {
    $scope.roles = roles;

    //roles search query
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

    //filter model for checkbox to filter photos attach/not attached to role
    $scope.showNotAttachedPhotosOnly = false;

    $scope.deleteRoles = function() {
        if (utils.isDemo) {
            utils.showToast('Sorry, you can\'t do that on demo site.');
            $scope.selectedItems = [];
            return;
        }

        roles.delete($scope.selectedItems).success(function() {
            $scope.selectedItems = [];
            $scope.paginate($scope.params);
        }).error(function(data) {
            utils.showToast(data);
        })
    };

    $scope.toggleAllRoles = function() {

        //all items already selected, deselect all
        if ($scope.selectedItems.length === roles.all.length) {
            $scope.selectedItems = [];
        }

        //all items aren't selected, copy all roles array to selected items
        else {
            $scope.selectedItems = roles.all.slice();
        }
    };

    $scope.showCreateRoleModal = function($event) {
        $mdDialog.show({
            templateUrl: 'assets/views/modals/create-role.html?key=1',
            clickOutsideToClose: true,
            controllerAs: 'ctrl',
            controller: function() { this.parent = $scope },
            targetEvent: $event,
        });
    };

    $scope.showUpdateRoleModal = function(role, $event) {
        $scope.roleModel = angular.copy(role);

        if (utils.isDemo) {
            $scope.roleModel.email = 'Hidden on demo site';
        }
        
        $mdDialog.show({
            templateUrl: 'assets/views/modals/update-role.html?key=1',
            clickOutsideToClose: true,
            controllerAs: 'ctrl',
            controller: function() { this.parent = $scope },
            targetEvent: $event,
        });
    };

    $scope.updateRole = function() {
        if (utils.isDemo) {
            utils.showToast('Sorry, you can\'t do that on demo site.');
            $scope.selectedItems = [];
            return;
        }

        roles.updateRole($scope.roleModel, $scope.roleModel.id).success(function() {
            $mdDialog.hide();
            utils.showToast('updatedRoleSuccessfully', true);
            $scope.paginate($scope.params);
            $scope.selectedItems = [];
        }).error(function(data) {
            $scope.setErrors(data);
        });
    };

    $scope.createNewRole = function() {
        if (utils.isDemo) {
            utils.showToast('Sorry, you can\'t do that on demo site.');
            $scope.selectedItems = [];
            return;
        }
        
        roles.save($scope.roleModel).success(function() {
            $mdDialog.hide();
            utils.showToast('Role Created Sucessfully', true);
            $scope.paginate($scope.params);
            $scope.errors = [];
        }).error(function(data) {
            $scope.setErrors(data);
        });
    };

    $scope.paginate = function(params) {
        if ($scope.rolesAjaxInProgress) return;

        $scope.rolesAjaxInProgress = true;

        roles.paginate(params).success(function(data) {
            $scope.items = data.data;
            $scope.totalItems = data.total;

            $scope.rolesAjaxInProgress = false;
        })
    };

    $scope.paginate($scope.params);
}]);
