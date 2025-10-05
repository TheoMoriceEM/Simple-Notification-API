# Simple Notification API

## 🎯 Overview

This project implements a notification system with the following capabilities:
- Create email notifications via REST API
- List all notifications
- Send notifications asynchronously using Symfony Messenger
- Automatic retry mechanism on failure
- Proper error handling and logging

## ✨ Features

- **RESTful API** with JSON responses
- **Asynchronous email sending** via Symfony Messenger
- **Status tracking**: `pending`, `sent`, `failed`
- **Automatic retry** up to 3 attempts
- **Input validation** with detailed error messages
- **OpenAPI/Swagger documentation** at `/api/doc`
- **Comprehensive test suite**
- **Clean architecture** with domain services and DTOs

## 🏗️ Architecture principles

- **Domain-Driven Design**: Business logic split into domain services
- **SOLID principles**: Single responsibility, dependency injection
- **DTO pattern**: Clean separation between API contracts and entities
- **Event-driven architecture**: Asynchronous processing with Symfony Messenger

## 📦 Technical stack

- PHP 8.2+
- Symfony 7.3+
- PostgreSQL 16+
- Docker & Docker Compose (optional)

## 🚀 Installation

### 1. Clone the repository
```bash
git clone https://github.com/TheoMoriceEM/Simple-Notification-API.git
cd Simple-Notification-API
```

### 2. Start Docker containers (optional)
```bash
docker compose up -d
```

### 3. Install dependencies
```bash
docker exec -it simple-notification-api-php-1 composer install
```

### 4. Setup database
```bash
# Create database
docker exec -it simple-notification-api-php-1 php bin/console doctrine:database:create

# Run migrations
docker exec -it simple-notification-api-php-1 php bin/console doctrine:migrations:migrate

# Setup Messenger transport tables
docker exec -it simple-notification-api-php-1 php bin/console messenger:setup-transports
```

### 5. Start the Messenger worker
```bash
docker exec -it simple-notification-api-php-1 php bin/console messenger:consume async
```

The API is now available at http://localhost  
And the Swagger UI at: http://localhost/api/doc

## 🧪 Testing

### Run Tests
```bash
# Setup test database
docker exec -it simple-notification-api-php-1 php bin/console doctrine:database:create --env=test
docker exec -it simple-notification-api-php-1 php bin/console doctrine:schema:create --env=test

# Run all tests
docker exec -it simple-notification-api-php-1 php bin/phpunit
```

## 🤔 Technical Decisions

### 1. Asynchronous Processing with Symfony Messenger

#### Why
Sending emails synchronously can block HTTP requests and degrade the user experience.  

#### Consequences

✅ Fast API responses  
✅ Automatic retry on failure  
✅ Horizontal scalability  
⚠️ Requires running worker process  
⚠️ Data consistency (e.g. status updates are async)

### 2. Domain Service Layer

#### Why
Separate business logic from HTTP concerns.

#### Benefits

✅ Reusable across controllers, CLI commands, and tests  
✅ Easier to test (no HTTP mocking needed)

### 3. DTO Pattern

#### Why

Decouple API contracts from database entities.

#### Implementation

- NotificationValidation for input validation
- NotificationDto for responses
- Static method `create()` for conversion from entity

### 4. Email Service Abstraction

#### Why

Separate email sending logic from message handling.

#### Benefits

✅ Easy to swap email providers  
✅ Testable in isolation  
✅ Reusable across the application  
✅ Single responsibility principle

### 5. OpenAPI Documentation

#### Why
Interactive API documentation for developers.

#### Benefits

✅ Always up-to-date with code  
✅ Interactive testing via Swagger UI  
✅ Clear API contract

## 📝 Notes

### Email Sending Simulation

For this technical test, email sending is simulated with:

- A 3-second delay (mimics usual latency)
- A 10% random failure rate (tests retry mechanism)

⚠️ Beware that due to this delay and the asynchronous processing of notifications, the `/send` API requests will return inconsistent data:
- The `status` will still be `pending` although it will switch either to `sent` or `failed` 3 seconds later.
- The `sentAt` timestamp will be undefined although if the sending succeeds, it will be set 3 seconds later as well.