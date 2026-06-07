
# FleetMove

<p align="center">
  <strong>A Modern Fleet Management & Booking Platform</strong>
</p>

<p align="center">
  <a href="#features">Features</a> •
  <a href="#tech-stack">Tech Stack</a> •
  <a href="#installation">Installation</a> •
  <a href="#usage">Usage</a> •
  <a href="#project-structure">Project Structure</a> •
  <a href="#contributing">Contributing</a>
</p>

---

## 📋 Project Overview

**FleetMove** is a comprehensive fleet management and booking platform designed to streamline transportation and logistics operations. The platform enables users to easily book vehicles, manage trips, handle payments, and track real-time operations through an intuitive interface and robust backend API.

With its modular architecture, FleetMove supports multi-tenant operations, advanced user management, real-time communication, and comprehensive business analytics. The platform is built with scalability and performance in mind, catering to enterprises of all sizes in the transportation industry.

### Key Purpose
The **Book Now** feature empowers users to instantly reserve vehicles and initiate trips through a seamless booking experience. Combined with integrated payment processing, real-time tracking, and chat functionality, FleetMove delivers a complete end-to-end transportation solution.

---

## ✨ Features

### Core Booking Features
- **📱 Book Now Functionality** - One-click vehicle booking with real-time availability
- **🗺️ Trip Management** - Complete trip lifecycle management with real-time tracking
- **💳 Integrated Payment Gateway** - Support for multiple payment methods with secure transactions
- **🔍 Vehicle Discovery** - Browse available vehicles with detailed information and ratings

### User Management
- **👥 User Roles & Permissions** - Admin, Driver, Rider role-based access control
- **🔐 Authentication** - OAuth 2.0 with Laravel Passport integration
- **📊 User Profiles** - Comprehensive user profiles with verification and rating systems
- **📧 Email Notifications** - Automated alerts for bookings, trips, and payments

### Advanced Features
- **💬 Real-Time Chat** - WebSocket-based messaging between drivers and riders
- **⭐ Review & Rating System** - User-generated reviews and ratings for transparency
- **🚚 Parcel Management** - Integrated parcel delivery service
- **🎟️ Promotion & Discounts** - Coupon and promotional campaign management
- **📈 AI-Powered Insights** - Intelligent analytics and recommendations
- **🔄 Business Management** - Multi-tenant support with flexible business configurations

### Technical Features
- **📡 Firebase Integration** - Push notifications and real-time updates
- **🔔 Event Broadcasting** - Real-time event notifications via WebSockets
- **📑 Modular Architecture** - Independent modules for scalability
- **🗄️ Admin Dashboard** - Comprehensive admin panel with analytics and controls
- **🌐 Multi-Language Support** - Internationalization-ready architecture
- **✅ API-First Design** - RESTful API with comprehensive documentation

---

## 🛠️ Tech Stack

### Backend
- **Framework**: [Laravel 10+](https://laravel.com) - PHP web application framework
- **PHP**: 8.1+ - Modern PHP runtime
- **API Authentication**: [Laravel Passport](https://laravel.com/docs/passport) - OAuth 2.0 implementation
- **Real-Time Communication**: [Laravel Reverb](https://laravel.com/docs/reverb) - WebSocket server
- **Database**: MySQL/PostgreSQL with Eloquent ORM
- **Queue System**: Redis-backed job processing
- **Caching**: Redis for high-performance caching

### Frontend
- **JavaScript/TypeScript** - Client-side logic
- **Vue.js** or **React** - UI component framework
- **Webpack** - Module bundler and asset compilation
- **CSS Preprocessor**: SASS/SCSS

### Infrastructure & Services
- **Firebase**: Cloud messaging and notifications
- **Payment Gateways**: Multiple payment processor integration
- **Storage**: Amazon S3 for file management
- **Logging**: Structured logging with DebugBar support
- **Testing**: PHPUnit for backend testing

### Development Tools
- **Package Manager**: Composer (PHP), npm/yarn (JavaScript)
- **Version Control**: Git
- **Environment Management**: Laravel .env configuration
- **Testing**: PHPUnit, Feature & Unit tests

---

## 🚀 Installation

### Prerequisites
- PHP 8.1 or higher
- Composer
- Node.js 16+ and npm/yarn
- MySQL 8.0+ or PostgreSQL 12+
- Redis
- Git

### Step 1: Clone the Repository

```bash
git clone https://github.com/yourusername/fleetmove.git
cd fleetmove
```

### Step 2: Install PHP Dependencies

```bash
composer install
```

### Step 3: Setup Environment Configuration

```bash
cp .env.example .env
php artisan key:generate
```

Edit `.env` file with your database and service credentials:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=fleetmove
DB_USERNAME=root
DB_PASSWORD=

FIREBASE_PROJECT_ID=your_project_id
FIREBASE_API_KEY=your_api_key

STRIPE_PUBLIC_KEY=your_stripe_public_key
STRIPE_SECRET_KEY=your_stripe_secret_key
```

### Step 4: Database Setup

```bash
php artisan migrate
php artisan db:seed
```

### Step 5: Install JavaScript Dependencies

```bash
npm install
# or
yarn install
```

### Step 6: Compile Assets

```bash
npm run dev
# For production
npm run production
```

### Step 7: Generate Passport Keys

```bash
php artisan passport:install
```

### Step 8: Start Development Server

```bash
php artisan serve
```

The application will be available at `http://localhost:8000`

---

## 📖 Usage

### API Endpoints

#### Authentication
```bash
POST   /api/auth/register          # User registration
POST   /api/auth/login             # User login
POST   /api/auth/logout            # User logout
POST   /api/auth/refresh           # Refresh token
```

#### Book Now Feature
```bash
GET    /api/vehicles               # List available vehicles
GET    /api/vehicles/{id}          # Get vehicle details
POST   /api/bookings               # Create new booking
GET    /api/bookings/{id}          # Get booking details
PATCH  /api/bookings/{id}          # Update booking status
DELETE /api/bookings/{id}          # Cancel booking
```

#### Trip Management
```bash
GET    /api/trips                  # List user trips
POST   /api/trips                  # Create new trip
GET    /api/trips/{id}             # Get trip details
PATCH  /api/trips/{id}             # Update trip status
GET    /api/trips/{id}/location    # Get real-time location
```

#### User Management
```bash
GET    /api/users/profile          # Get user profile
PATCH  /api/users/profile          # Update profile
GET    /api/users/{id}/ratings     # Get user ratings
POST   /api/users/{id}/reviews     # Submit review
```

### Making a Booking

1. **Browse Vehicles**
   ```javascript
   GET /api/vehicles?pickup=location&date=2026-05-30
   ```

2. **Get Vehicle Details**
   ```javascript
   GET /api/vehicles/123
   ```

3. **Create Booking**
   ```javascript
   POST /api/bookings
   {
     "vehicle_id": 123,
     "pickup_location": "123 Main St",
     "dropoff_location": "456 Oak Ave",
     "pickup_time": "2026-05-30 14:00:00",
     "passengers": 2
   }
   ```

4. **Process Payment**
   ```javascript
   POST /api/bookings/123/payment
   {
     "payment_method": "card",
     "amount": 45.50
   }
   ```

### Real-Time Updates

Subscribe to booking status changes via WebSocket:

```javascript
// Connect to WebSocket
let channel = Echo.channel('booking.' + bookingId);

channel.listen('BookingStatusChanged', (e) => {
  console.log('Booking status:', e.status);
});
```

---

## 📁 Project Structure

```
├── app/
│   ├── Http/              # HTTP Controllers & Requests
│   ├── Models/            # Eloquent Models
│   ├── Services/          # Business Logic Layer
│   ├── Repositories/      # Data Access Layer
│   ├── Jobs/              # Queue Jobs
│   ├── Events/            # Application Events
│   ├── Listeners/         # Event Listeners
│   └── Traits/            # Reusable Trait Classes
├── Modules/               # Feature Modules
│   ├── BookingManagement/ # Booking Feature
│   ├── TripManagement/    # Trip Management
│   ├── UserManagement/    # User Management
│   ├── PaymentGateway/    # Payment Processing
│   ├── ChattingManagement/# Real-time Chat
│   └── VehicleManagement/ # Vehicle Management
├── routes/
│   ├── api.php            # API Routes
│   ├── web.php            # Web Routes
│   └── channels.php       # WebSocket Channels
├── config/                # Configuration Files
├── database/
│   ├── migrations/        # Database Migrations
│   └── seeders/           # Database Seeders
├── resources/
│   ├── views/             # Blade Templates
│   ├── js/                # JavaScript/Vue Components
│   └── css/               # Stylesheets
├── tests/                 # Test Suite
├── public/                # Public Assets
└── storage/               # Application Storage

```

---

## 🔄 Development Workflow

### Running Tests

```bash
# Run all tests
php artisan test

# Run specific test file
php artisan test tests/Feature/BookingTest.php

# Run tests with coverage
php artisan test --coverage
```

### Running Queue Jobs

```bash
# Start queue worker
php artisan queue:work

# Start queue worker with daemon mode
php artisan queue:work --daemon
```

### Database Migrations

```bash
# Run migrations
php artisan migrate

# Rollback last migration
php artisan migrate:rollback

# Refresh database
php artisan migrate:refresh --seed
```

### Cache Management

```bash
# Clear all caches
php artisan cache:clear

# Clear config cache
php artisan config:clear

# Clear view cache
php artisan view:clear
```

---

## 🔮 Future Improvements

### Planned Features
- **🤖 AI-Powered Pricing** - Dynamic pricing engine using machine learning
- **🌍 Multi-Currency Support** - Global payment processing with currency conversion
- **📊 Advanced Analytics Dashboard** - Detailed business metrics and KPIs
- **🗺️ Route Optimization** - Intelligent route planning and optimization
- **📱 Mobile App Expansion** - Native iOS and Android applications
- **🚁 Drone Delivery Integration** - Support for alternative delivery methods
- **♿ Accessibility Enhancements** - WCAG 2.1 AA compliance
- **🔐 Enhanced Security** - Advanced fraud detection and prevention

### Performance Improvements
- Caching optimization for high-traffic scenarios
- Database query optimization and indexing
- API response time reduction
- Frontend bundle optimization
- CDN integration for static assets

### Developer Experience
- Comprehensive API documentation with Swagger/OpenAPI
- Development environment containerization with Docker
- Enhanced logging and monitoring
- Automated code quality checks
- CI/CD pipeline improvements

---

## 📝 Contributing

We welcome contributions from the community! To contribute:

1. **Fork the repository**
   ```bash
   git clone https://github.com/yourusername/fleetmove.git
   ```

2. **Create a feature branch**
   ```bash
   git checkout -b feature/amazing-feature
   ```

3. **Make your changes**
   - Follow Laravel coding standards
   - Add tests for new functionality
   - Update documentation as needed

4. **Commit your changes**
   ```bash
   git commit -m "Add amazing feature"
   ```

5. **Push to the branch**
   ```bash
   git push origin feature/amazing-feature
   ```

6. **Open a Pull Request**
   - Describe your changes in detail
   - Reference any related issues
   - Ensure all tests pass

### Coding Standards
- Follow [PSR-12](https://www.php-fig.org/psr/psr-12/) coding standards
- Use meaningful variable and function names
- Add docblocks to classes and public methods
- Keep methods focused and single-purpose
- Write unit tests for new functionality

---

## 📄 License

This project is licensed under the MIT License. See the [LICENSE](LICENSE) file for details.

---

## 🤝 Support

For support and questions:
- 📧 Email: support@fleetmove.app
- 💬 Chat: [Community Discord](https://discord.gg/fleetmove)
- 📚 Documentation: [FleetMove Docs](https://docs.fleetmove.app)
- 🐛 Issues: [GitHub Issues](https://github.com/yourusername/fleetmove/issues)

---

## 👨‍💻 Team

**FleetMove** is maintained by a dedicated team of developers passionate about transportation technology and made with Digitize LLC.

---

## 🙏 Acknowledgments

- Built with [Laravel](https://laravel.com) framework
- Icons from [Font Awesome](https://fontawesome.com)
- Community contributions and feedback

---

<p align="center">
  Made by Wasiullah with Digitize LLC
</p>

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

