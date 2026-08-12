<!DOCTYPE html>
<html lang="en" ng-app="taskApp">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Task Management System</title>

  <!-- Bootstrap 5 & Icons -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
  
  <!-- Custom Application Styles -->
  {{-- <link rel="stylesheet" href="{{ asset('app/css/style.css') }}"> --}}
</head>
<body ng-controller="MainController">

  <!-- Universal Header Navigation -->
  <nav class="navbar navbar-expand-lg navbar-dark bg-dark px-4 shadow-sm">
    <a class="navbar-brand fw-bold" href="#!/tasks">
      <i class="bi bi-check2-square me-2"></i>Task Management System
    </a>

    <div class="ms-auto d-flex align-items-center">
      <!-- Unauthenticated Navigation Links -->
      <div ng-if="!isLoggedIn()">
        <a class="btn btn-outline-light btn-sm me-2" href="#!/login">Login</a>
        <a class="btn btn-primary btn-sm" href="#!/register">Register</a>
      </div>

      <!-- Authenticated Navigation Actions -->
      <div ng-if="isLoggedIn()" class="d-flex align-items-center">
        <!-- Role-based Task Links -->
        <a ng-if="!isAdmin()" href="#!/tasks" class="btn btn-outline-light btn-sm me-2">
          <i class="bi bi-list-task me-1"></i>My Tasks
        </a>
        <a ng-if="isAdmin()" href="#!/admin/tasks" class="btn btn-outline-light btn-sm me-2">
          <i class="bi bi-shield-check me-1"></i>Admin Tasks
        </a>

        <!-- Common Profile Link -->
        <a ng-if="!isAdmin()" href="#!/profile" class="btn btn-outline-light btn-sm me-2">
          <i class="bi bi-person-circle me-1"></i>My Profile
        </a>
        <button class="btn btn-outline-danger btn-sm" ng-click="logout()">
          <i class="bi bi-box-arrow-right me-1"></i>Logout
        </button>
      </div>
    </div>
  </nav>

  <!-- Dynamic View Container (AngularJS Routes render here) -->
  <main class="container py-4">
    <div ng-view></div>
  </main>

  <!-- CDN Dependencies -->
  <script src="https://ajax.googleapis.com/ajax/libs/angularjs/1.8.2/angular.min.js"></script>
  <script src="https://ajax.googleapis.com/ajax/libs/angularjs/1.8.2/angular-route.min.js"></script>

  <!-- AngularJS Application Modules -->
  <script src="{{ asset('app/js/app.js') }}"></script>
  <script src="{{ asset('app/js/services.js') }}"></script>
  @vite('resources/js/app.js')
  <script src="{{ asset('app/js/controllers.js') }}"></script>

</body>
</html>