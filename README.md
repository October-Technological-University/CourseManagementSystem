# Course Management System

A PHP REST API for managing courses, enrollments, users, and file attachments. Built with a 3-layer architecture (Presentation, Business Logic, Data Access) using vanilla PHP and MySQL.

## Architecture

```
Client -> PL (Controllers) -> BLL (Services/Mappers) -> DAL (Repositories) -> MySQL
```

| Layer | Directory | Responsibility |
|-------|-----------|----------------|
| **PL** (Presentation) | `PL/` | Routing, controllers, middleware, HTTP handling |
| **BLL** (Business Logic) | `BLL/` | Services, business rules, entity-DTO mapping |
| **DAL** (Data Access) | `DAL/` | Repositories, entities, DTOs, database schema |
| **Config** | `config/` | Environment variables, application constants |
| **Utils** | `utils/` | Security, validation, file storage, response helpers |

## Prerequisites

- PHP 8.0+
- MySQL 5.7+ or MariaDB 10.3+
- Apache or any web server with PHP support (or PHP built-in server)
- OpenSSL PHP extension
- MySQLi PHP extension

## Setup

1. **Clone the repository**

   ```bash
   git clone https://github.com/October-Technological-University/CourseManagementSystem.git
   cd CourseManagementSystem
   ```

2. **Create the environment file**

   Copy the example environment file and fill in your values:

   ```bash
   cp config/example.env config/.env
   ```

   Edit `config/.env` with your settings:

   ```ini
   DATABASE_SERVER=localhost
   DATABASE_USERNAME=root
   DATABASE_PASSWORD=your_password
   DATABASE_NAME=CourseManagementSystem

   ENCRYPTION_CIPHER=aes-256-cbc
   ENCRYPTION_KEY=your_random_32_char_hex_key
   ```

   To generate a secure encryption key:

   ```bash
   php -r "echo bin2hex(random_bytes(16));"
   ```

3. **Start the server**

   Using PHP's built-in server:

   ```bash
   php -S localhost:8000 -t PL/public
   ```

   The API will be available at `http://localhost:8000`.

4. **Database setup**

   The database and tables are created automatically on first request. No manual migration is needed.

5. **Database Seeding**

   ``` bash
   php ./DAL/Database/DataSeed.php
   ```

   
## API Endpoints

### Authentication

| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/api/auth/register` | Register a new user |
| POST | `/api/auth/login` | Login and receive auth token |
| POST | `/api/auth/logout` | Logout (requires auth) |
| POST | `/api/auth/changepassword` | Change password (requires auth) |

### Users

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/users` | List all users |
| GET | `/api/users/{id}` | Get user by ID |
| GET | `/api/users/students` | List all students |
| GET | `/api/users/instructors` | List all instructors |
| POST | `/api/users/{id}/profile-picture` | Upload profile picture |
| DELETE | `/api/users/{id}/profile-picture` | Remove profile picture |
| DELETE | `/api/users/delete` | Delete account |

### Courses

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/courses` | List all courses |
| GET | `/api/courses/{id}` | Get course by ID |
| GET | `/api/courses/instructor/{id}` | Get courses by instructor |
| POST | `/api/courses` | Create a course |
| PUT | `/api/courses/{id}` | Update a course |
| DELETE | `/api/courses/{id}` | Delete a course |
| POST | `/api/courses/{id}/course-image` | Upload course cover image |
| DELETE | `/api/courses/{id}/course-image` | Remove course cover image |
| POST | `/api/courses/{id}/generate-code` | Generate enrollment code |

### Enrollments

| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/api/enrollments` | Enroll a student |
| POST | `/api/enrollments/code` | Enroll by course code |
| DELETE | `/api/enrollments/drop` | Drop enrollment |
| GET | `/api/enrollments/course/{id}/students` | List students in a course |
| GET | `/api/enrollments/student/{id}/courses` | List courses for a student |

### Files

| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/api/files/upload/course` | Upload file to a course |
| GET | `/api/files/{id}` | Get file metadata |
| GET | `/api/files/download/{id}` | Download a file |
| GET | `/api/files/serve/{storedName}` | Serve a file |
| GET | `/api/files/course/{courseId}` | List course files |
| GET | `/api/files/course/{courseId}/assignments` | List course assignments |
| GET | `/api/files/course/{courseId}/resources` | List course resources |
| DELETE | `/api/files/{id}` | Delete a file |

## User Roles

| Role | Permissions |
|------|-------------|
| **admin** | Full access to all resources |
| **teacher** | Manage own courses, upload files, view enrolled students |
| **student** | Enroll in courses, download course files, manage own profile |

## Project Structure

```
CourseManagementSystem/
├── config/
│   ├── .env              # Environment variables (gitignored)
│   ├── example.env        # Template for .env
│   └── constants.php      # Application constants (file limits, MIME types)
├── DAL/
│   ├── Database/          # Database connection, schema, seeding
│   ├── DTOs/              # Data Transfer Objects
│   ├── Entities/          # Database entity classes
│   └── Repository/        # Data access repositories
├── BLL/
│   ├── Mappers/           # Entity <-> DTO converters
│   └── Services/          # Business logic services
├── PL/
│   ├── Controllers/       # HTTP request handlers
│   ├── Middleware/         # Auth and file upload middleware
│   └── public/
│       ├── index.php      # Entry point and router
│       └── uploads/       # Uploaded files storage
└── utils/
    ├── Security.php       # Encryption, hashing, token management
    ├── Validator.php       # Input validation
    ├── ResponseHelper.php  # JSON response formatting
    └── FileStorageHelper.php  # File system operations
```

## Environment Variables

| Variable | Description | Required |
|----------|-------------|----------|
| `DATABASE_SERVER` | MySQL host | Yes |
| `DATABASE_USERNAME` | MySQL username | Yes |
| `DATABASE_PASSWORD` | MySQL password | Yes |
| `DATABASE_NAME` | Database name | Yes |
| `ENCRYPTION_CIPHER` | OpenSSL cipher algorithm | No (defaults to `aes-256-cbc`) |
| `ENCRYPTION_KEY` | 32-character hex key for token encryption | Yes |
