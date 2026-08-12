angular.module('taskApp')

.factory('AuthService', [
    '$http',
    '$location',
    function ($http, $location) {
        var TOKEN_KEY = 'auth_token';
        var ROLE_KEY = 'user_role';

        return {
            login: function (credentials) {
                return $http.post('/api/login', credentials);
            },

            register: function (user) {
                return $http.post('/api/register', user);
            },

            saveAuthData: function (token, role) {
                localStorage.setItem(TOKEN_KEY, token);
                localStorage.setItem(ROLE_KEY, role);
            },

            getToken: function () {
                return localStorage.getItem(TOKEN_KEY);
            },

            getRole: function () {
                return localStorage.getItem(ROLE_KEY);
            },

            isLoggedIn: function () {
                return !!this.getToken();
            },

            isAdmin: function () {
                return this.getRole() === 'admin';
            },

            logout: function () {
                localStorage.removeItem(TOKEN_KEY);
                localStorage.removeItem(ROLE_KEY);
                $location.path('/login');
            },

            getProfile: function () {
                return $http.get('/api/user/profile');
            },

            updateProfile: function (profileData) {
                return $http.put('/api/user/profile', profileData);
            }
        };
    }
])


.factory('TaskService', [
    '$http',
    function ($http) {
        return {
            // Get user tasks (for standard users)
            getTasks: function (page) {
                page = page || 1;
                return $http.get('/api/my-eligible-tasks?page=' + page);
            },

            // Get all system tasks (for admin management)
            getAllTasks: function (page) {
                page = page || 1;
                return $http.get('/api/tasks?page=' + page);
            },

            createTask: function (taskData) {
                return $http.post('/api/tasks', taskData);
            },

            updateTask: function (id, taskData) {
                return $http.put('/api/tasks/' + id, taskData);
            },

            deleteTask: function (id) {
                return $http.delete('/api/tasks/' + id);
            },

            getEligibleUsers: function (taskId) {
                return $http.get('/api/tasks/' + taskId + '/eligible-users');
            },

            recomputeEligibility: function (taskIds) {
                var payload = (taskIds && taskIds.length) ? { task_ids: taskIds } : {};
                return $http.post('/api/tasks/recompute-eligibility', payload);
            }


        };
    }
])


.factory('RuleService', [
    '$http',
    function ($http) {
        return {
            // Fetch assignment rules belonging to a specific task
            getRulesForTask: function (taskId) {
                return $http.get('/api/taskAssignmentRules?task_id=' + taskId);
            },

            createRule: function (ruleData) {
                return $http.post('/api/taskAssignmentRules', ruleData);
            },

            updateRule: function (id, ruleData) {
                return $http.put('/api/taskAssignmentRules/' + id, ruleData);
            },

            deleteRule: function (id) {
                return $http.delete('/api/taskAssignmentRules/' + id);
            }
        };
    }
]);