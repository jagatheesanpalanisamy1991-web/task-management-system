# Task Management System

## Overview

This project was built as part of a Senior Software Development Engineer technical assignment for Indus Action, targeting the technology stack used on the Odisha RTE platform.

It's a Task Management System where tasks are never manually assigned. Instead, an admin defines eligibility rules on a task (department, experience, location, workload), and a background rule engine automatically finds and assigns the best-matching user.

## Technology Stack

- Laravel 11 (PHP 8.3)
- AngularJS
- MySQL 8
- Redis
- Docker & Docker Compose

## Project Status

**Completed:**

- Docker infrastructure — PHP-FPM, Nginx, MySQL, Redis, a Supervisor-managed queue worker, and a scheduler, all as separate containers
- Laravel 11 installed and configured, database and Redis connections working
- Authentication — registration, login, logout, profile — via Laravel Sanctum
- Role-based authorization middleware (admin / manager / user)
- User profile attributes (department, years of experience, location, active task count)
- Task CRUD (create, view, update, delete)
- Assignment rules, managed separately from tasks — a task can have multiple rule conditions (department, minimum experience, location, maximum active tasks)
- The rule engine itself — matches eligible users against a task's rules, with a fewest-active-tasks tie-break, and a deterministic fallback if that ties too
- Background processing — rule evaluation runs asynchronously on a dedicated queue, with retry and backoff on failure
- Automatic re-evaluation when an admin edits a task's rules, and when a user's profile changes in a way that could affect eligibility
- A scheduled retry job for tasks that had no eligible user, with backoff so it doesn't hammer permanently-stuck tasks forever
- AngularJS frontend — login, my tasks, all tasks (admin), create task, edit task
- Rule engine tested manually against 10,000+ seeded records

**Still in progress / not yet fully verified:**

- Redis caching on the `/my-eligible-tasks` endpoint
- Automated test suite (some tests written, not all passing yet)
- API documentation
- Final README sections (architecture write-up, ER diagram, assumptions)

## Running the Project

Start the containers:
```bash
docker compose up -d
```

Check what's running:
```bash
docker ps
```

Run migrations and seed demo data:
```bash
docker compose exec app php artisan migrate:fresh --seed
```

Stop everything:
```bash
docker compose down
```

## Authentication

Laravel Sanctum handles API authentication (token-based, not session cookies).

```
POST /api/register
POST /api/login
POST /api/logout
GET  /api/profile
```

## Authorization

Role-based middleware restricts admin/manager-only actions. Three roles: `admin`, `manager`, `user`.

```php
Route::middleware(['auth:sanctum', 'role:admin,manager'])->group(function () {
    // admin/manager-only routes
});
```

## Task Management

```
POST   /api/tasks
GET    /api/tasks
GET    /api/tasks/{id}
PUT    /api/tasks/{id}
DELETE /api/tasks/{id}
```

Tasks and their eligibility rules are managed separately — creating or editing a task doesn't touch its rules, and vice versa:

```
POST   /api/task-assignment-rules
PUT    /api/task-assignment-rules/{taskAssignmentRule}
DELETE /api/task-assignment-rules/{taskAssignmentRule}
```

## Assignment Engine

```
GET  /api/my-eligible-tasks
```

Tasks are assigned automatically, in the background, whenever a rule is added or changed, or when a user's profile changes in a way relevant to some pending task's rules. There's no manual "assign this task to this user" endpoint anywhere in the system, by design.

## License

Built for technical evaluation purposes.