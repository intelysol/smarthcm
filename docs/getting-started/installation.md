# Installation & Setup Guide

This guide provides step-by-step instructions for deploying and running the **Smart HCM / Flow Enterprise Platform (FEP)** in development, staging, and production environments.

---

## Prerequisites

Before installing, ensure your environment meets the following software requirements:

| Component | Minimum Version | Recommended Version |
| :--- | :--- | :--- |
| **PHP** | 8.2.0 | 8.3.x (with `pdo_mysql`, `mbstring`, `openssl`, `bcmath`, `curl`, `redis`) |
| **Composer** | 2.5.0 | 2.7.x |
| **MySQL / MariaDB** | MySQL 8.0+ / MariaDB 10.5+ | MySQL 8.0.35+ |
| **Redis** | 6.0+ | 7.0+ (Cache, Sessions, Queues) |
| **Node.js & NPM** | Node 18.x LTS | Node 20.x LTS |

---

## Step-by-Step Installation

### 1. Clone the Repository
```bash
git clone https://github.com/intelysol/smarthcm.git
cd smarthcm
```

### 2. Install Dependencies
Install PHP packages via Composer and frontend packages via NPM:
```bash
composer install --no-interaction --prefer-dist --optimize-autoloader
npm install
```

### 3. Environment Configuration
Copy the environment template file:
```bash
cp .env.example .env
```
Open `.env` and configure your database and redis connection:
```ini
APP_NAME="Flow Enterprise Platform"
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=smarthcm
DB_USERNAME=root
DB_PASSWORD=

REDIS_CLIENT=phpredis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

QUEUE_CONNECTION=redis
CACHE_STORE=redis
SESSION_DRIVER=file
```

Generate the unique application encryption key:
```bash
php artisan key:generate
```

### 4. Database Migration & Baseline Data
Run the database migrations:
```bash
php artisan migrate
```

### 5. Compile Frontend Assets
```bash
npm run build
```

### 6. Setup Development & Demo Environment
To immediately bootstrap the 5 pre-configured demo personas, demo tenant, and role assignments:
```bash
php artisan app:setup-demo
```
*Note: To customize the default password for seeded accounts, set `DEMO_USER_PASSWORD` in your `.env` before running the command.*

### 7. Start the Application
Start the PHP development server:
```bash
php artisan serve
```
Visit `http://localhost:8000` in your web browser. You will be greeted with the authentication screen with quick-fill demo login buttons.