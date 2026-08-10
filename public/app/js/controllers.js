angular.module('taskApp')

.controller('MainController', [
    '$scope',
    'AuthService',
    function ($scope, AuthService) {

        $scope.isLoggedIn = function () {
            return AuthService.isLoggedIn();
        };

        $scope.isAdmin = function () {
            return AuthService.isAdmin();
        };

        $scope.logout = function () {
            AuthService.logout();
        };
    }
])


.controller('LoginController', [
    '$scope',
    '$location',
    'AuthService',
    function ($scope, $location, AuthService) {

        $scope.credentials = {
            email: '',
            password: ''
        };

        $scope.errorMessage = '';
        $scope.loading = false;

        $scope.login = function () {

            $scope.errorMessage = '';
            $scope.loading = true;

            AuthService.login($scope.credentials)
                .then(function (response) {

                    console.log('Login response:', response.data);

                    var data = response.data;

                    var token =
                        data.access_token || data.token;

                    var role =
                        data.user && data.user.role
                            ? data.user.role
                            : 'user';

                    if (!token) {
                        $scope.errorMessage = 'Login failed.';
                        return;
                    }

                    AuthService.saveAuthData(token, role);

                    if (role === 'admin') {
                        $location.path('/admin/tasks');
                    } else {
                        $location.path('/tasks');
                    }

                })
                .catch(function (error) {

                    console.error('Login error:', error);

                    $scope.errorMessage =
                        error.data && error.data.message
                            ? error.data.message
                            : 'Invalid email or password.';

                })
                .finally(function () {
                    $scope.loading = false;
                });
        };
    }
])


.controller('RegisterController', [
    '$scope',
    '$location',
    'AuthService',
    function ($scope, $location, AuthService) {

        $scope.user = {
            name: '',
            email: '',
            password: '',
            password_confirmation: ''
        };

        $scope.errorMessage = '';
        $scope.loading = false;

        $scope.register = function () {

            $scope.errorMessage = '';

            if (
                $scope.user.password !==
                $scope.user.password_confirmation
            ) {
                $scope.errorMessage =
                    'Passwords do not match.';
                return;
            }

            $scope.loading = true;

            AuthService.register($scope.user)
                .then(function (response) {

                    var data = response.data;

                    var token =
                        data.access_token || data.token;

                    if (token) {
                        AuthService.saveAuthData(
                            token,
                            'user'
                        );
                    }

                    $location.path('/tasks');

                })
                .catch(function (error) {

                    console.error(
                        'Registration error:',
                        error
                    );

                    $scope.errorMessage =
                        error.data && error.data.message
                            ? error.data.message
                            : 'Registration failed.';

                })
                .finally(function () {
                    $scope.loading = false;
                });
        };
    }
])


.controller('TasksController', [
    '$scope',
    'TaskService',
    'AuthService',
    function ($scope, TaskService, AuthService) {

        $scope.tasks = [];

        $scope.loading = false;
        $scope.errorMessage = '';

        $scope.currentPage = 1;
        $scope.lastPage = 1;
        $scope.total = 0;


        $scope.loadTasks = function (page) {

            page = page || 1;

            $scope.loading = true;
            $scope.errorMessage = '';

            TaskService.getTasks(page)
                .then(function (response) {

                    var result = response.data;
                    console.log(result);
                    $scope.tasks = result.data.data || [];

                    $scope.currentPage =
                        result.pagination.current_page || 1;

                    $scope.lastPage =
                        result.pagination.last_page || 1;

                    $scope.total =
                        result.pagination.total || 0;

                })
                .catch(function (error) {

                    console.error(
                        'Failed to load tasks:',
                        error
                    );

                    if (error.status === 403) {
                        $scope.errorMessage =
                            'You are not allowed to view these tasks.';
                    } else {
                        $scope.errorMessage =
                            'Unable to load tasks.';
                    }

                })
                .finally(function () {
                    $scope.loading = false;
                });
        };


        $scope.nextPage = function () {

            if ($scope.currentPage < $scope.lastPage) {
                $scope.loadTasks(
                    $scope.currentPage + 1
                );
            }
        };


        $scope.previousPage = function () {

            if ($scope.currentPage > 1) {
                $scope.loadTasks(
                    $scope.currentPage - 1
                );
            }
        };


        $scope.logout = function () {
            AuthService.logout();
        };


        $scope.loadTasks(1);
    }
])


.controller('AdminTasksController', [
    '$scope',
    'TaskService',
    'RuleService',
    'AuthService',
    '$q',
    function ($scope, TaskService, RuleService, AuthService, $q) {

        $scope.tasks = [];

        $scope.newTask = {
            title: '',
            due_date: null,
            priority: 'medium'
        };

        $scope.loading = false;
        $scope.errorMessage = '';

        $scope.currentPage = 1;
        $scope.lastPage = 1;
        $scope.total = 0;


        $scope.loadTasks = function (page) {

            page = page || 1;

            $scope.loading = true;
            $scope.errorMessage = '';

            TaskService.getAllTasks(page)
                .then(function (response) {

                    var result = response.data;
                    console.log('API Response:',response);
                    console.log(result.pagination);
                    $scope.tasks = result.data.data || [];
                    
                    $scope.currentPage =
                        result.pagination.current_page || 1;

                    $scope.lastPage =
                        result.pagination.last_page || 1;

                    $scope.total =
                        result.pagination.total || 0;

                })
                .catch(function (error) {

                    console.error(
                        'Failed to load admin tasks:',
                        error
                    );

                    if (error.status === 403) {
                        $scope.errorMessage =
                            'You are not authorized to manage tasks.';
                    } else {
                        $scope.errorMessage =
                            'Unable to load tasks.';
                    }

                })
                .finally(function () {
                    $scope.loading = false;
                });
        };


        $scope.addTask = function () {

            var title = ($scope.newTask.title || '').trim();

            if (!title) {
                return;
            }

            $scope.errorMessage = '';

            var formattedDate = $scope.newTask.due_date 
                ? new Date($scope.newTask.due_date).toISOString().split('T')[0] 
                : null;

            TaskService.createTask({
                title: title,
                due_date: formattedDate,
                priority: $scope.newTask.priority || 'medium'
            })
                .then(function () {

                    $scope.newTask = {
                        title: '',
                        due_date: null,
                        priority: 'medium'
                    };

                    $scope.loadTasks(1);

                })
                .catch(function (error) {

                    console.error(
                        'Create task error:',
                        error
                    );

                    $scope.errorMessage =
                        error.data && error.data.message
                            ? error.data.message
                            : 'Unable to create task.';
                });
        };


        $scope.startEdit = function (task) {

            task.isEditing = true;
            task.editTitle = task.title;
            task.editDueDate = task.due_date ? new Date(task.due_date) : null;
            task.editPriority = task.priority || 'medium';
        };


        $scope.saveEdit = function (task) {

            var title = (task.editTitle || '').trim();

            if (!title) {
                task.isEditing = false;
                return;
            }

            var formattedDate = task.editDueDate 
                ? new Date(task.editDueDate).toISOString().split('T')[0] 
                : null;

            TaskService.updateTask(
                task.id,
                {
                    title: title,
                    due_date: formattedDate,
                    priority: task.editPriority
                }
            )
                .then(function (response) {

                    var updatedTask =
                        response.data.data ||
                        response.data;

                    task.title = updatedTask.title || title;
                    task.due_date = updatedTask.due_date || formattedDate;
                    task.priority = updatedTask.priority || task.editPriority;

                    task.isEditing = false;

                })
                .catch(function (error) {

                    console.error(
                        'Update task error:',
                        error
                    );

                    $scope.errorMessage =
                        error.data && error.data.message
                            ? error.data.message
                            : 'Unable to update task.';
                });
        };


        $scope.toggleTask = function (task) {

            var completed = !task.completed;

            TaskService.updateTask(
                task.id,
                {
                    title: task.title,
                    due_date: task.due_date,
                    priority: task.priority,
                    completed: completed
                }
            )
                .then(function () {

                    task.completed = completed;

                })
                .catch(function (error) {

                    console.error(
                        'Toggle task error:',
                        error
                    );

                    $scope.errorMessage =
                        'Unable to update task.';
                });
        };


        $scope.deleteTask = function (id, index) {

            if (!confirm('Are you sure you want to delete this task?')) {
                return;
            }

            TaskService.deleteTask(id)
                .then(function () {

                    $scope.tasks.splice(index, 1);

                    if (
                        $scope.tasks.length === 0 &&
                        $scope.currentPage > 1
                    ) {
                        $scope.loadTasks(
                            $scope.currentPage - 1
                        );
                    }

                })
                .catch(function (error) {

                    console.error(
                        'Delete task error:',
                        error
                    );

                    $scope.errorMessage =
                        error.data && error.data.message
                            ? error.data.message
                            : 'Unable to delete task.';
                });
        };


        // --- Assignment Rules Logic ---

        $scope.openAssignRules = function (task) {
            task.showRulesPanel = !task.showRulesPanel;

            if (task.showRulesPanel) {
                task.tempRule = {
                    rule_attribute: 'department',
                    rule_operator: '=',
                    rule_value: ''
                };

                if (!task.rules) {
                    task.rules = [];
                    RuleService.getRulesForTask(task.id)
                        .then(function (response) {
                            var fetchedRules = response.data.data || response.data || [];
                            task.rules = fetchedRules.map(function(r) {
                                r.is_existing = true;
                                return r;
                            });
                        })
                        .catch(function (error) {
                            console.error('Failed to load rules for task', error);
                        });
                }
            }
        };

        $scope.onAttributeChange = function (task) {
            if (task.tempRule.rule_attribute === 'department') {
                task.tempRule.rule_operator = '=';
            } else if (task.tempRule.rule_attribute === 'minimum_experience') {
                task.tempRule.rule_operator = '>=';
            } else if (task.tempRule.rule_attribute === 'location') {
                task.tempRule.rule_operator = '=';
            }
            task.tempRule.rule_value = '';
        };

        $scope.addRuleToList = function (task) {
            var attr = (task.tempRule.rule_attribute || '').trim();
            var op = task.tempRule.rule_operator;
            //var val = (task.tempRule.rule_value || '').trim();
            var val = (task.tempRule.rule_value || '');

            if (!attr || !op || !val) {
                alert('Please fill out all rule fields.');
                return;
            }

            if (attr === 'department') {
                val = val.toLowerCase();
            }

            var isDuplicate = task.rules.some(function (r) {
                return r.rule_attribute.toLowerCase() === attr.toLowerCase();
            });

            if (isDuplicate) {
                alert('The rule attribute "' + attr + '" is already defined for this task. Attributes must be unique.');
                return;
            }

            task.rules.push({
                task_id: task.id,
                rule_attribute: attr,
                rule_operator: op,
                rule_value: val,
                is_new: true
            });

            task.tempRule.rule_value = '';
        };

        $scope.removeRule = function (task, index, rule) {
            if (rule.is_existing && rule.id) {
                if (!confirm('Are you sure you want to delete this existing rule?')) {
                    return;
                }

                RuleService.deleteRule(rule.id)
                    .then(function () {
                        task.rules.splice(index, 1);
                    })
                    .catch(function (error) {
                        console.error('Failed to delete rule', error);
                        alert('Unable to delete rule.');
                    });
            } else {
                task.rules.splice(index, 1);
            }
        };

        $scope.saveRules = function (task) {
            var newRules = task.rules.filter(function (r) {
                return r.is_new;
            });

            if (newRules.length === 0) {
                alert('No new rules to save.');
                return;
            }

            var promises = newRules.map(function (rule) {
                return RuleService.createRule({
                    task_id: task.id,
                    rule_attribute: rule.rule_attribute,
                    rule_operator: rule.rule_operator,
                    rule_value: rule.rule_value
                });
            });

            $q.all(promises)
                .then(function (responses) {
                    responses.forEach(function (res, index) {
                        var savedRule = res.data.data || res.data;
                        var target = newRules[index];
                        target.id = savedRule.id;
                        target.is_existing = true;
                        delete target.is_new;
                    });
                    alert('Rules saved successfully!');
                })
                .catch(function (error) {
                    console.error('Failed to save rules:', error);
                    alert('An error occurred while saving rules.');
                });
        };


        $scope.nextPage = function () {

            if ($scope.currentPage < $scope.lastPage) {
                $scope.loadTasks(
                    $scope.currentPage + 1
                );
            }
        };


        $scope.previousPage = function () {

            if ($scope.currentPage > 1) {
                $scope.loadTasks(
                    $scope.currentPage - 1
                );
            }
        };


        $scope.logout = function () {
            AuthService.logout();
        };


        $scope.loadTasks(1);
    }
])


.controller('UserProfileController', [
    '$scope',
    '$location',
    'AuthService',
    function ($scope, $location, AuthService) {
        $scope.user = {};
        $scope.loading = true;
        $scope.updating = false;
        $scope.successMessage = '';
        $scope.errorMessage = '';

        $scope.loadProfile = function () {
            $scope.loading = true;
            AuthService.getProfile()
                .then(function (response) {
                    $scope.user = response.data.data || response.data;
                })
                .catch(function (error) {
                    console.error('Failed to load profile', error);
                    $scope.errorMessage = 'Unable to load profile details.';
                })
                .finally(function () {
                    $scope.loading = false;
                });
        };

        $scope.updateProfile = function () {
            $scope.successMessage = '';
            $scope.errorMessage = '';
            $scope.updating = true;

            AuthService.updateProfile({
                department: $scope.user.department,
                location: $scope.user.location,
                years_experience: $scope.user.years_experience
            })
            .then(function (response) {
                $scope.successMessage = response.data.message || 'Profile updated successfully.';
                $scope.user = response.data.data || $scope.user;
            })
            .catch(function (error) {
                console.error('Profile update error:', error);
                $scope.errorMessage = error.data && error.data.message
                    ? error.data.message
                    : 'Validation error occurred.';
            })
            .finally(function () {
                $scope.updating = false;
            });
        };

        $scope.goBack = function () {
            if (AuthService.isAdmin()) {
                $location.path('/admin/tasks');
            } else {
                $location.path('/tasks');
            }
        };

        $scope.loadProfile();
    }
]);