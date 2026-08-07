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

Next Steps:

Implement authentication and authorization
Develop Task Management module
Build dynamic rule-based task assignment engine
Configure queue processing
Develop AngularJS frontend

## Runing the Project

Start Docker containers:

docker compose up -d 

Check the running containers

docker ps

Stop the Docker containers

docker composer down

## licence

This project mainly created for technical evolution process