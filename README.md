# Task Management System

## Overview

This project is being developed as part of a Senior Software Development Engineer technical assignment.

The application is a Task Management System that automatically assigns tasks to eligible users based on configurable business rules.

## Technology Stack

- Laravel 11
- PHP 8.3
- AngularJS
- MySQL 8
- Redis
- Docker
- Docker Compose

## Project Status

Initial setup in progress

## Docker Infrastructure

This project environment is configured with the Docker and Docker Composer Insfrastructe,
The following services are included,
 PHP 8.3 with PHP-FPM for running the Laravel application
 Nginx as the web server
 MySQL 8 for database storage
 Redis for caching and queue processing
 Queue worker for handling background jobs

## Project Status

Completed.

Initial Project setup,
Git Repository configuration,
Docker Infrastructure setup,
PHP Docker Image configuration,
Nginx Configuration,
Mysql and Redis service configuration,
Docker containers successfully started.
Laravel 11 Installation completed
Configured database and Redis connection
Laravel environment configuration completed
- User registration and login
- Laravel Sanctum authentication
- Logout and profile API
- Role-based middleware
- Admin user seeder
- User profile attributes
- Task migration
- Task assignment rules migration

Next Steps:

- Task CRUD
- Task assignment rules API
- Dynamic eligibility engine
- Background queue processing
- Assignment result handling
- Testing
- AngularJS frontend

## Docker Infrastructure

The project uses Docker Compose with the following services:

- PHP 8.3 / PHP-FPM
- Nginx
- MySQL 8
- Redis
- Queue worker

## Authentication

Laravel Sanctum is used for API authentication.

Available authentication APIs:

POST /api/register

POST /api/login

POST /api/logout

GET /api/profile

## Authorization

Role-based middleware is implemented for restricting admin-only operations.

Current roles:

- admin
- manager
- user

Example:

```php
Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
    // Admin-only routes
});

## Runing the Project

Start Docker containers:

docker compose up -d 

Check the running containers

docker ps

Stop the Docker containers

docker composer down

## licence

This project mainly created for technical evolution process