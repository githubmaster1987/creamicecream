'use strict';

angular.module('app').controller('ChatController', ['$scope', '$rootScope', '$state', '$mdDialog', 'utils', 'chat', function($scope, $rootScope, $state, $mdDialog, utils, chat) {
    $scope.chat = chat;

    //chat search query
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

    $scope.users = [];
    chat.users().success(function(data) {
        console.log(data);
        $scope.users = data;
    });

    $scope.messages = [];
    chat.messages().success(function(data) {
        //console.log(data);
        $scope.messages = data;
    });

    //filter model for checkbox to filter photos attach/not attached to chat
    $scope.showNotAttachedPhotosOnly = false;

    $scope.deleteChat = function() {
        if (utils.isDemo) {
            utils.showToast('Sorry, you can\'t do that on demo site.');
            $scope.selectedItems = [];
            return;
        }

        chat.delete($scope.selectedItems).success(function() {
            $scope.selectedItems = [];
            $scope.paginate($scope.params);
        }).error(function(data) {
            utils.showToast(data);
        })
    };

    $scope.toggleAllChat = function() {

        //all items already selected, deselect all
        if ($scope.selectedItems.length === chat.all.length) {
            $scope.selectedItems = [];
        }

        //all items aren't selected, copy all chat array to selected items
        else {
            $scope.selectedItems = chat.all.slice();
        }
    };

    $scope.showCreateChatModal = function($event) {
        $mdDialog.show({
            templateUrl: 'assets/views/modals/create-chat.html?key=1',
            clickOutsideToClose: true,
            controllerAs: 'ctrl',
            controller: function() { this.parent = $scope },
            targetEvent: $event,
        });
    };

    $scope.showUpdateChatModal = function(chat, $event) {
        $scope.chatModel = angular.copy(chat);
        console.log(chat);
        if (utils.isDemo) {
            $scope.chatModel.email = 'Hidden on demo site';
        }
        
        $mdDialog.show({
            templateUrl: 'assets/views/modals/update-chat.html?key=1',
            clickOutsideToClose: true,
            controllerAs: 'ctrl',
            controller: function() { this.parent = $scope },
            targetEvent: $event,
        });
    };

    $scope.updateChat = function() {
        if (utils.isDemo) {
            utils.showToast('Sorry, you can\'t do that on demo site.');
            $scope.selectedItems = [];
            return;
        }

        chat.updateChat($scope.chatModel, $scope.chatModel.id).success(function() {
            $mdDialog.hide();
            utils.showToast('updatedChatSuccessfully', true);
            $scope.paginate($scope.params);
            $scope.selectedItems = [];
        }).error(function(data) {
            $scope.setErrors(data);
        });
    };

    $scope.createNewChat = function() {
        if (utils.isDemo) {
            utils.showToast('Sorry, you can\'t do that on demo site.');
            $scope.selectedItems = [];
            return;
        }
        
        chat.save($scope.chatModel).success(function() {
            $mdDialog.hide();
            utils.showToast('Chat Created Sucessfully', true);
            $scope.paginate($scope.params);
            $scope.errors = [];
        }).error(function(data) {
            $scope.setErrors(data);
        });
    };

    $scope.paginate = function(params) {
        if ($scope.chatAjaxInProgress) return;

        $scope.chatAjaxInProgress = true;

        chat.paginate(params).success(function(data) {
            $scope.items = data.data;
            $scope.totalItems = data.total;

            $scope.chatAjaxInProgress = false;
        })
    };

    $scope.paginate($scope.params);
}]);
