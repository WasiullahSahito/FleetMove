# FleetMove — Fleet Management & Booking Platform

<p align="center">
  <strong>A production-ready, enterprise-level transportation and fleet management platform built with Laravel 12</strong>
</p>

<p align="center">
  <a href="#features">Features</a> •
  <a href="#technology-stack">Tech Stack</a> •
  <a href="#system-requirements">Requirements</a> •
  <a href="#installation--setup">Installation</a> •
  <a href="#environment-configuration">Configuration</a> •
  <a href="#running-the-application">Running the App</a> •
  <a href="#module-architecture">Modules</a> •
  <a href="#troubleshooting">Troubleshooting</a>
</p>

---

## Project Overview

**FleetMove** is a comprehensive fleet management and booking platform that enables users to book vehicles, manage trips, process payments, and track real-time operations. It is built with a modular architecture supporting multi-tenant operations, real-time communication, AI-powered insights, and multi-gateway payment processing.

---

## Features

### Booking & Trips
- One-click vehicle booking with driver fare bidding
- Complete trip lifecycle management with real-time GPS tracking
- Parcel delivery service with tracking and refund support
- Driver proximity matching with geospatial zone queries

### Payments
- Multi-gateway support: **Stripe, Razorpay, Xendit, Iyzico, Mercado Pago**
- In-app wallet with top-up and transaction history
- Loyalty points, referral rewards, and promotional coupons

### Real-Time Communication
- WebSocket server via **Laravel Reverb** for live updates
- In-app chat between drivers and riders
- Push notifications via **Firebase Cloud Messaging (FCM)**
- SMS notifications via **Twilio**

### User Management
- Three roles: **Admin**, **Customer**, **Driver**
- OAuth 2.0 authentication via **Laravel Passport**
- Firebase OTP verification and social login
- Driver identity and document verification
- User levels, loyalty points, and referral system

### Admin & Analytics
- Full admin dashboard with activity logs and analytics
- AI-powered insights via **OpenAI** integration
- PDF and Excel report export (DomPDF, mPDF, Fast-Excel)
- Blog and newsletter management
- Zone-based fare rule configuration

---

## Technology Stack

| Layer | Technology |
|---|---|
| Framework | Laravel 12 |
| PHP | ^8.2 |
| Authentication | Laravel Passport (OAuth 2.0), Laravel Sanctum |
| Real-Time | Laravel Reverb (WebSocket), Pusher, Laravel Echo |
| Database | MySQL / PostgreSQL |
| Cache & Queue | Redis / File (configurable) |
| Payments | Stripe, Razorpay, Xendit, Iyzico, Mercado Pago |
| Notifications | Firebase (FCM), Twilio (SMS) |
| File Storage | Local / Amazon S3 |
| AI | OpenAI PHP SDK |
| Geospatial | Laravel Eloquent Spatial |
| Module System | nwidart/laravel-modules |
| Frontend Build | Laravel Mix (Webpack), Axios, Pusher.js |
| PDF Export | DomPDF, mPDF |

---

## System Requirements

Ensure your system has the following before starting:

- **PHP** >= 8.2 with extensions: `curl`, `fileinfo`, `gd`, `openssl`, `dom`, `libxml`, `mbstring`, `pdo_mysql`
- **Composer** >= 2.x
- **Node.js** >= 18.x and **npm** >= 9.x
- **MySQL** >= 8.0 or **PostgreSQL** >= 14
- **Redis** (optional but recommended for queues and caching)
- **Git**

---

## Installation & Setup

### Step 1 — Clone the Repository

```bash
git clone https://github.com/your-org/fleetmove.git
cd fleetmove
```

### Step 2 — Install PHP Dependencies

```bash
composer install
```

### Step 3 — Install Node.js Dependencies

```bash
npm install
```

### Step 4 — Copy Environment File

```bash
cp .env.example .env
```

### Step 5 — Generate Application Key

```bash
php artisan key:generate
```

### Step 6 — Configure Environment

Edit `.env` with your local settings. See [Environment Configuration](#environment-configuration) for all options.

### Step 7 — Run Database Migrations

```bash
php artisan migrate
```

To seed the database with initial data:

```bash
php artisan migrate --seed
```

### Step 8 — Install Laravel Passport (OAuth Keys)

```bash
php artisan passport:install
```

### Step 9 — Create Storage Symlink

```bash
php artisan storage:link
```

### Step 10 — Build Frontend Assets

For development:

```bash
npm run dev
```

For production:

```bash
npm run prod
```

---

## Environment Configuration

### Application

```env
APP_NAME=FleetMove
APP_ENV=local          # local | production
APP_DEBUG=true         # Set to false in production
APP_URL=http://localhost
APP_MODE=live
```

### Database

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=fleetmove
DB_USERNAME=root
DB_PASSWORD=
```

### Broadcasting (WebSocket / Real-Time)

```env
BROADCAST_DRIVER=reverb

REVERB_APP_ID=fleetmove
REVERB_APP_KEY=fleetmove
REVERB_APP_SECRET=fleetmove
REVERB_HOST="${APP_URL}"
REVERB_PORT=6015
REVERB_SCHEME=http

PUSHER_APP_ID=fleetmove
PUSHER_APP_KEY=fleetmove
PUSHER_APP_SECRET=fleetmove
PUSHER_APP_CLUSTER=mt1
PUSHER_HOST="${APP_URL}"
PUSHER_PORT=6015
```

### Cache & Queue

```env
CACHE_DRIVER=file       # file | redis | database
QUEUE_CONNECTION=sync   # sync | database | redis
SESSION_DRIVER=file
```

### Mail

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=587
MAIL_USERNAME=your_username
MAIL_PASSWORD=your_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@fleetmove.com
MAIL_FROM_NAME="${APP_NAME}"
```

### Redis (recommended)

```env
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
```

### Amazon S3 (optional)

```env
AWS_ACCESS_KEY_ID=your_key
AWS_SECRET_ACCESS_KEY=your_secret
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=your_bucket
FILESYSTEM_DISK=public   # Change to 's3' to use Amazon S3
```

### Payment Gateways

```env
STRIPE_KEY=your_stripe_publishable_key
STRIPE_SECRET=your_stripe_secret_key

RAZORPAY_KEY=your_razorpay_key
RAZORPAY_SECRET=your_razorpay_secret
```

Xendit, Iyzico, and Mercado Pago credentials are configured through the **Admin Panel → Gateway Settings**.

---

## Running the Application

### Development Server

```bash
php artisan serve
```

The application will be available at [http://localhost:8000](http://localhost:8000).

### Custom Host and Port

```bash
php artisan serve --host=0.0.0.0 --port=8080
```

### Production (Nginx)

Point the web server document root to the `public/` directory.

```nginx
server {
    listen 80;
    server_name yourdomain.com;
    root /var/www/fleetmove/public;

    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

---

## Real-Time WebSocket Server

FleetMove uses **Laravel Reverb** for real-time features including live trip tracking, driver location updates, and in-app chat.

### Start the Reverb Server

```bash
php artisan reverb:start
```

Default port is `6015`. To specify host and port:

```bash
php artisan reverb:start --host=0.0.0.0 --port=6015
```

### Keep Reverb Running in Production (Supervisor)

Create `/etc/supervisor/conf.d/reverb.conf`:

```ini
[program:reverb]
command=php /var/www/fleetmove/artisan reverb:start
autostart=true
autorestart=true
user=www-data
redirect_stderr=true
stdout_logfile=/var/log/reverb.log
```

Then reload Supervisor:

```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start reverb
```

---

## Queue Worker

Background jobs handle notifications, emails, payment webhooks, and other async tasks.

### Start the Queue Worker

```bash
php artisan queue:work
```

### Production (Supervisor)

Create `/etc/supervisor/conf.d/fleetmove-worker.conf`:

```ini
[program:fleetmove-worker]
command=php /var/www/fleetmove/artisan queue:work --tries=3 --timeout=90
autostart=true
autorestart=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/log/fleetmove-worker.log
```

### Managing Failed Jobs

```bash
php artisan queue:failed          # List failed jobs
php artisan queue:retry all       # Retry all failed jobs
php artisan queue:flush           # Delete all failed jobs
```

---

## Frontend Assets

### Development Build (single pass)

```bash
npm run dev
```

### Watch Mode (auto-rebuild on file change)

```bash
npm run watch
```

### Hot Module Replacement

```bash
npm run hot
```

### Production Build (minified)

```bash
npm run prod
```

Compiled assets are output to `public/js/` and `public/css/`.

---

## Module Architecture

FleetMove uses [nwidart/laravel-modules](https://nwidart.com/laravel-modules/v6/introduction) providing 16 self-contained feature modules. Each module has its own controllers, models, routes, migrations, and API resources.

| Module | Responsibility |
|---|---|
| `AdminModule` | Admin panel, activity logs, admin notifications |
| `AiModule` | OpenAI-powered analytics and insights |
| `AuthManagement` | Registration, login, OTP, social auth, password reset |
| `BlogManagement` | Blog posts and category management |
| `BusinessManagement` | Multi-tenant business configuration and settings |
| `ChattingManagement` | Real-time driver–rider in-app chat |
| `FareManagement` | Fare calculation, driver bidding, and pricing rules |
| `Gateways` | Payment gateway integrations (Stripe, Razorpay, etc.) |
| `ParcelManagement` | Parcel delivery, tracking, and refund handling |
| `PromotionManagement` | Coupons, discounts, and referral campaigns |
| `ReviewModule` | User reviews and driver rating system |
| `TransactionManagement` | Wallet, transactions, and financial records |
| `TripManagement` | Trip lifecycle, driver bidding, safety alerts |
| `UserManagement` | Customer/Driver profiles, levels, loyalty points |
| `VehicleManagement` | Vehicle registration, categories, brands, models |
| `ZoneManagement` | Geographic zones and zone-based fare rules |

### Useful Module Commands

```bash
# List all registered modules
php artisan module:list

# Run migrations for all modules
php artisan module:migrate

# Run migrations for a specific module
php artisan module:migrate TripManagement

# Seed a specific module
php artisan module:seed UserManagement
```

---

## API Overview

The API is organized by user role and prefixed with `/api/v1/`.

### Authentication

```
POST   /api/v1/auth/customer/register       Customer registration
POST   /api/v1/auth/driver/register         Driver registration
POST   /api/v1/auth/customer/login          Customer login
POST   /api/v1/auth/driver/login            Driver login
POST   /api/v1/auth/otp/verify              OTP verification
POST   /api/v1/auth/password/forgot         Password reset
```

### Customer Endpoints (requires auth token)

```
GET    /api/v1/customer/profile             Profile details
POST   /api/v1/customer/trip/request        Create trip request
GET    /api/v1/customer/trip/list           Trip history
POST   /api/v1/customer/parcel/store        Create parcel delivery
GET    /api/v1/customer/wallet              Wallet balance
```

### Driver Endpoints (requires auth token)

```
GET    /api/v1/driver/profile               Driver profile
GET    /api/v1/driver/trip/requests         Available trip requests
POST   /api/v1/driver/bid                   Submit fare bid
PUT    /api/v1/driver/location              Update real-time location
```

### Real-Time WebSocket Subscription Example

```javascript
// resources/js/bootstrap.js is pre-configured with Echo + Pusher
let channel = Echo.channel('trip.' + tripId);

channel.listen('TripStatusUpdated', (e) => {
    console.log('Trip status:', e.status);
    console.log('Driver location:', e.location);
});
```

---

## Project Structure

```
fleetmove/
├── app/
│   ├── Http/Controllers/       # Core application controllers
│   ├── Models/                 # Core Eloquent models (User, etc.)
│   ├── Lib/                    # Helpers, constants, API response formatting
│   ├── Library/                # Business logic (wallet, trips, payments)
│   └── Traits/                 # Reusable traits (HasUuid, etc.)
├── Modules/                    # 16 feature modules (nwidart/laravel-modules)
│   ├── AdminModule/
│   ├── AiModule/
│   ├── AuthManagement/
│   ├── BlogManagement/
│   ├── BusinessManagement/
│   ├── ChattingManagement/
│   ├── FareManagement/
│   ├── Gateways/
│   ├── ParcelManagement/
│   ├── PromotionManagement/
│   ├── ReviewModule/
│   ├── TransactionManagement/
│   ├── TripManagement/
│   ├── UserManagement/
│   ├── VehicleManagement/
│   └── ZoneManagement/
├── routes/
│   ├── web.php                 # Public web routes (landing, blog, payments)
│   ├── api.php                 # Base API routes
│   └── channels.php            # WebSocket broadcast channel definitions
├── database/
│   ├── migrations/             # Core database migrations
│   ├── factories/              # Model factories for testing
│   └── seeders/                # Database seeders
├── resources/
│   ├── views/                  # Blade templates
│   ├── js/                     # JavaScript (app.js, bootstrap.js)
│   └── css/                    # Stylesheets
├── public/                     # Web server document root (compiled assets)
├── storage/                    # Logs, cache, uploaded files
├── config/                     # Application configuration files
├── tests/                      # PHPUnit test suite
├── composer.json               # PHP dependency manifest
├── package.json                # Node.js dependency manifest
├── webpack.mix.js              # Laravel Mix asset compilation config
└── .env.example                # Environment variable template
```

---

## Testing

```bash
# Run all tests
php artisan test

# Run a specific test file
php artisan test tests/Feature/TripTest.php

# Run tests with code coverage
php artisan test --coverage
```

---

## Cache Management

```bash
# Clear everything
php artisan optimize:clear

# Clear individual caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

---

## Troubleshooting

### Composer Autoload Issues

```bash
composer dump-autoload
```

### Storage Permission Issues (Linux/macOS)

```bash
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

### Passport OAuth Keys Missing

```bash
php artisan passport:keys
```

### WebSocket Not Connecting

1. Confirm `BROADCAST_DRIVER=reverb` in `.env`
2. Verify Reverb is running: `php artisan reverb:start`
3. Ensure `REVERB_HOST`, `REVERB_PORT`, and `REVERB_APP_KEY` in `.env` match the frontend config
4. Confirm port `6015` is open in your firewall

### Module Migrations Not Running

```bash
# Migrate all modules
php artisan module:migrate

# Migrate a specific module
php artisan module:migrate TripManagement
```

### Refresh Database (Development Only)

```bash
php artisan migrate:refresh --seed
```

---

## License

This project is proprietary software. All rights reserved.
