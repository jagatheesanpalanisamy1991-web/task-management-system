angular.module('taskApp', ['ngRoute'])

.config([
    '$routeProvider',
    '$httpProvider',

    function($routeProvider, $httpProvider) {

        $routeProvider

            .when('/login', {
                templateUrl: '/app/views/login.html',
                controller: 'LoginController'
            })

            .when('/register', {
                templateUrl: '/app/views/register.html',
                controller: 'RegisterController'
            })

            // User UI
            .when('/tasks', {
                templateUrl: '/app/views/tasks.html',
                controller: 'TasksController',
                requiresAuth: true,
                role: 'user'
            })

            // Admin UI
            .when('/admin/tasks', {
                templateUrl: '/app/views/admin-tasks.html',
                controller: 'AdminTasksController',
                requiresAuth: true,
                role: 'admin'
            })

            // Fixed missing dot here
            .when('/profile', {
                templateUrl: '/app/views/user-profile.html',
                controller: 'UserProfileController',
                requiresAuth: true
            })
            
            .otherwise({
                redirectTo: '/login'
            });


        // Add Bearer token to API requests (Matched keys to 'auth_token' and 'user_role')
        $httpProvider.interceptors.push([
            '$q',
            '$location',

            function($q, $location) {

                return {

                    request: function(config) {

                        var token =
                            localStorage.getItem('auth_token');

                        if (token) {

                            config.headers =
                                config.headers || {};
                            config.headers['Accept'] = 'application/json';
                            config.headers.Authorization =
                                'Bearer ' + token;
                        }

                        return config;
                    },

                    responseError: function(response) {

                        if (response.status === 401) {

                            localStorage.removeItem('auth_token');
                            localStorage.removeItem('user_role');

                            $location.path('/login');
                        }

                        return $q.reject(response);
                    }
                };
            }
        ]);
    }
])


.run([
    '$rootScope',
    '$location',
    'AuthService',

    function($rootScope, $location, AuthService) {

        $rootScope.$on(
            '$routeChangeStart',
            function(event, next) {

                if (!next) {
                    return;
                }

                // Page requires login
                if (
                    next.requiresAuth &&
                    !AuthService.isLoggedIn()
                ) {

                    event.preventDefault();
                    $location.path('/login');

                    return;
                }


                // User cannot access admin page
                if (
                    next.role === 'admin' &&
                    !AuthService.isAdmin()
                ) {

                    event.preventDefault();
                    $location.path('/tasks');

                    return;
                }


                // Admin cannot access user page
                if (
                    next.role === 'user' &&
                    AuthService.isAdmin()
                ) {

                    event.preventDefault();
                    $location.path('/admin/tasks');

                    return;
                }
            }
        );
    }
]);