# Task Management System

## Overview

This project is being developed as part of a Senior Software Development Engineer technical assignment.

Laravel-based task management system with rule-based automatic task assignment. Tasks are assigned to eligible users based on configurable rules (department, location, experience, active workload), with automatic re-evaluation whenever rules or user profiles change.

## Technology Stack

- Laravel 11
- PHP 8.3
- AngularJS
- MySQL 8
- Redis
- Docker
- Docker Compose

## Project Setup Instructions
1. Docker and Docker compose
2. Git

## Steps

# 1.Git Clone
git clone https://github.com/jagatheesanpalanisamy1991-web/task-management-system.git

cd task-management-system

# 2. Copy Environmental File
cp .env.example .env

# 3. Configure .env for Docker networking

DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=task_management
DB_USERNAME=root
DB_PASSWORD=secret

CACHE_DRIVER=redis
QUEUE_CONNECTION=redis
REDIS_HOST=redis
REDIS_PORT=6379

# 4.Build and start containers
docker compose up -d --build

# 5. Install PHP dependencies
docker exec -it task-management-system-app composer install

# 6.Generate App Key
docker exec -it task-management-system-app php artisan key:generate

# 7. Run migrations
docker exec -it task-management-system-app php artisan migrate

# 8. (Optional) Seed sample data
docker exec -it task-management-system-app php artisan db:seed

# 9. Run the app
App runs at http://localhost:8080.

## Services & Ports

Service                     Containers                          Port
App (PHP-FPM)       task-management-system-app              8081 → 8080
Nginx	            task-management-nginx	                8080 → 80
MySQL	            task-management-mysql	                3306
Redis	            task-management-redis	                6379

## Verifying the queue worker is running
docker logs -f task-management-queue

## 2. Architecture Decisions
# Role-based route groups — admin and user middleware groups for seperate task management routes

# Service-layer isolation (RuleEngineService) - all rule-matching and assignment logic lives in a single service class,

# Event-driven re-evaluation over polling - rather than a scheduled sweep that periodically re-checks every task, Eloquent observers on Task and TaskAssignmentRule react immediately

# Queued jobs, not synchronous re-evaluation - rule changes dispatch AssignEligibleUsersJob via DB::afterCommit()

# Read-through caching via Laravel's Cache facade - Redis is configured as the driver in .env, but business logic (RuleEngineService) never hard-codes a dependency on Redis specifically.


## 3.Database Design
users — id, name, email, role, department, location, years_experience
tasks — id, title, due_date, priority, status, assigned_to (FK → users), assignment_pending, completed
task_assignment_rules — id, task_id (FK → tasks), rule_attribute, rule_operator, rule_value
Rules are stored as flexible (attribute, operator, value) rows rather than fixed columns, so new rule types don't need a migration.

## 4.ER Diagram
users (1) ────< tasks (assigned_to)
tasks (1) ────< task_assignment_rules (task_id)

## 5.API Design

# Auth API
POST /register, POST /login, POST /logout, GET /profile

# Admin
GET/POST /tasks, GET/PUT/DELETE /tasks/{id}
GET /tasks/{id}/eligible-users — users currently matching the task's rules
POST /tasks/recompute-eligibility — re-run the rule engine
/taskAssignmentRules

# User
GET /my-eligible-tasks, GET/PUT /user/profile


## 6.Queue Implementation
Rule create/update/delete → observer fires → job dispatched after DB commit → picked up by a dedicated queue worker container → job re-fetches the task, clears its cache, and calls evaluateTask().
Keeps rule-saving fast for the admin, since re-evaluation happens in the background instead of inside the HTTP request.

## 7. RuleEngineService
RuleEngineService has two entry points:
1.evaluateTask($task) — checks if the current assignee still matches the task's rules; if not, searches for a new eligible user.

2.evaluateUser($user) — when a user's profile changes, checks their current tasks (unassign if no longer eligible) and any pending unassigned tasks (assign if now eligible).
maximum_active_tasks is computed (a live count of the user's workload) and checked separately in PHP.

## 8. Cachinfg Strategy
User's active task count - 60s - TaskObserver when assignment changes

## licence

This project mainly created for technical evolution process