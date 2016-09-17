angular.module('app').factory('chat', ['$http', '$rootScope', '$state', '$mdDialog', 'utils', 'files', function($http, $rootScope, $state, $mdDialog, utils, files) {

    var chat = {

        /**
         * Currently logged in user.
         */
        current: false,

        /**
         * Account settings model for currently logged in user.
         */
        accountSettings: {},

        /**
         * Model for password change form.
         */
        changePasswordModel: {
            oldPassword: '',
        },

        /**
         * All registered chat. If current user is admin.
         */
        all: [],

        /**
         * Paginate all existing chat.
         *
         * @returns {promise}
         */
         paginate: function(params) {
            return $http.get('chat', {params: params});
        },

        users : function() {
        
            return $http.get('users/lists');
        },

        messages : function() {
        
            return $http.get('message/lists');
        },

        /**
         * Login in user matching given credentials.
         *
         * @param {object} credentials
         * @returns {promise}
         */
        login: function(credentials) {
            return $http.post('auth/login', credentials).success(function(data) {
                chat.assignCurrentUser(data);
            })
        },

        /**
         * Register a new user with given credentials.
         *
         * @param {object} credentials
         * @returns {promise}
         */
        save: function(data) {
            return $http.post('chat', data).success(function(data) {
               if ( ! chat.current) {
                   chat.assignCurrentUser(data);
               }
            })
        },

        /**
         * Set given user as currently logged in user.
         *
         * @param {object} user
         */
        assignCurrentUser: function(user) {
            if (!user) return;

            if (utils.isDemo) {
                user.isAdmin = true;
            }

            this.current = user;
            this.accountSettings.username = this.getUsernameForCurrentUser();
            this.accountSettings.first_name = user.first_name;
            this.accountSettings.last_name = user.last_name;
            this.accountSettings.gender = user.gender;
        },

        users : function() {
        
            return $http.get('users/lists');
        },

        /**
         * Delete given user from database.
         *
         * @param {array|object} values
         * @returns {*|void}
         */
        delete: function(values) {
            if (angular.isArray(values)) {
                var promise = $http.post('delete/chat', {chat:values})
            } else {
                var promise = $http.delete('chat/'+values.id);
                values = [values];
            }

            return promise.success(function(data) {
                chat.all = chat.all.filter(function(user) {
                    return values.indexOf(chat) === -1;
                });
                utils.showToast(data);
            });
        },

        /**
         * Change currently logged in chat password.
         */
        changePassword: function() {
            $http.post($rootScope.baseUrl+'password/change', this.changePasswordModel).success(function(data) {
                utils.showToast(data);
                chat.closeModal();
            });
        },

        /**
         * Logout current logged in user.
         *
         * @returns {promise}
         */
        logout: function() {
            return $http.post('auth/logout').success(function() {
                chat.current = false;
                $state.go('login');
                $rootScope.$emit('user.loggedOut');
                utils.showToast('logOutSuccess', true);
            });
        },

        /**
         * Return username if set otherwise first part of email.
         *
         * @returns {string|undefined}
         */
        getUsernameForCurrentUser: function() {
            if ( ! this.current || ! this.current.email) return;

            if (this.current.username) {
                return this.current.username;
            }

            return this.current.email.split('@')[0];
        },

        /**
         * Return chat avatar url or url for a default avatar.
         *
         * @returns {string}
         */
        getAvatar: function(user) {
            if ( ! user) user = this.current;

            if (user.avatar_url) {
                return user.avatar_url;
            }

            if (user.gender === 'male' || ! user.gender) {
                return  $rootScope.baseUrl+'assets/images/avatars/male.png';
            } else {
                return $rootScope.baseUrl+'assets/images/avatars/female.png'
            }
        },

        /**
         * Update account settings for currently logged in user.
         */
        updateChat: function(data, id) {
            var data = data,
                chatId  = id;

            return $http.post($rootScope.baseUrl+'chat/'+chatId, data).success(function(data) {

                //user is updating his open profile, we can show a confirmation message here.
                
                    utils.showToast('profileUpdateSuccess', true);
                    chat.closeModal();
                    chat.current = data;
                
            })
        },

        /**
         * Remove currently logged in chat custom avatar.
         *
         * @returns {promise}
         */
        removeAvatar: function() {
            return $http.delete($rootScope.baseUrl + 'chat/'+this.current.id+'/avatar').success(function(data) {
                chat.current.avatar_url = '';
                utils.showToast(data);
            })
        },

        showAccountSettingsModal: function($event, fieldToFocus) {
            var options = {
                templateUrl: 'assets/views/modals/account-settings.html',
                targetEvent: $event,
                locals: { activeTab:'settings' },
                clickOutsideToClose: true,
                controller: ['$scope', '$upload', 'chat', 'activeTab', function($scope, $upload, chat, activeTab) {
                    $scope.chat = chat;
                    $scope.activeTab = activeTab;

                    $scope.upload = function(files) {
                        if (!files.length) return;
                        var file = files[0];

                        $upload.upload({
                            url: $rootScope.baseUrl + 'chat/'+chat.current.id+'/avatar',
                            file: file
                        }).success(function (data) {
                            chat.current.avatar_url = data;
                        }).error(function(data, code) {
                            if (code === 422) {
                                utils.showToast(data.file[0]);
                            }
                        })
                    }
                }]
            };

            //start with avatar tab opened
            if (fieldToFocus === 'avatar') {
                options.locals.activeTab = 'avatar';

            //focus passed in field in the modal
            } else if (fieldToFocus) {
                options.onComplete = function() {
                    $('.account-settings-modal .'+fieldToFocus).focus();
                }
            }

            $mdDialog.show(options);
        },

        closeModal: function() {
            $mdDialog.hide();
            this.changePasswordModel = {};
        }
    };

    return chat;
}]);
