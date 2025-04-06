# Laravel Chat Application

A real-time chat application backend built with Laravel 12 using **Laravel Reverb** for WebSockets and **JWT Authentication** via `php-open-source-saver/jwt-auth`.

## Features

- JWT-based API authentication
- Private and Group chat support
- Real-time messaging with Laravel Reverb (WebSockets)
- Message storage and retrieval from MySQL
- Unread message count, last message info, and pagination
- Clean architecture using Repositories and Services

---

## Requirements

- PHP >= 8.2
- Laravel >= 12.x
- Composer
- Node.js & npm
- Redis (for Laravel Reverb broadcasting)
- MySQL or compatible database
- Laravel Reverb server (optional: can be hosted separately)

---

## Installation & Setup

### 1. Clone the repository

```bash
git https://github.com/irah1999/Chat-app-api.git
cd Chat-app-api
```

### 2. Install dependencies

```bash
composer install

```

### 3. Create a .env file

Copy the .env.example file to .env and configure your database connection details.

```bash
cp .env.example .env
```

### 4. Generate an API key

```bash
php artisan key:generate
```

### 5. Generate an JWT Secret key

```bash
php artisan jwt:secret
```

### 6. Create the database tables

```bash
php artisan migrate
```

### 7.To generate new Reverb credentials:

```bash
php artisan reverb:install
```

### Ensure the following configurations are correct:

APP_NAME="Laravel Chat App"
APP_URL=http://localhost

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_database
DB_USERNAME=your_username
DB_PASSWORD=your_password

BROADCAST_CONNECTION=reverb
FILESYSTEM_DISK=local
QUEUE_CONNECTION=database
BROADCAST_DRIVER=reverb

REVERB_APP_ID=your-app-id
REVERB_APP_KEY=your-app-key
REVERB_APP_SECRET=your-app-secret
REVERB_HOST=127.0.0.1
REVERB_PORT=6001

JWT_SECRET=your-generated-jwt-secret


### 8.Then Start the reverb

```bash
php artisan reverb:start
```

### 9. Serve the Laravel application:

```bash
php artisan serve
```

### 10. Then Start the Queue Worker:

```bash
php artisan queue:work
```

### 11. Finally, you can access the API at http://localhost:8000/api.

### 12. You can access the API at 