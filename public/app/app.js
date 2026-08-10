var app = angular.module('taskApp', ['ngRoute']);

app.constant('API_BASE', '/api'); 

app.config(function ($routeProvider) {
    $routeProvider
        .when('/login', { templateUrl: 'views/login.html', controller: 'LoginController' })
        .when('/tasks', { templateUrl: 'views/tasks.html', controller: 'TaskListController' })
        .when('/all-tasks', { templateUrl: 'views/all-tasks.html', controller: 'AllTasksController' })
        .when('/create-task', { templateUrl: 'views/create-task.html', controller: 'CreateTaskController' })
        .when('/edit-task/:id', { templateUrl: 'views/edit-task.html', controller: 'EditTaskController' })
        .otherwise({ redirectTo: '/login' });
});

// Route guard: redirect to login if no token exists yet.
app.run(function ($rootScope, $location) {
    $rootScope.$on('$routeChangeStart', function (event, next) {
        var isLoginRoute = $location.path() === '/login';
        var hasToken = !!localStorage.getItem('token');
        if (!hasToken && !isLoginRoute) {
            $location.path('/login');
        }
    });

    $rootScope.isLoggedIn = function () {
        return !!localStorage.getItem('token');
    };

    $rootScope.isAdminOrManager = function () {
        var role = localStorage.getItem('role');
        return role === 'admin' || role === 'manager';
    };

    $rootScope.logout = function () {
        localStorage.removeItem('token');
        localStorage.removeItem('role');
        $location.path('/login');
    };
});