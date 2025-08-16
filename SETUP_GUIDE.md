# Ajurnie Full-Stack Setup Guide

This guide covers setting up both the Laravel API backend and React frontend for the Ajurnie fitness platform.

## 🚀 Quick Start

### Prerequisites
- PHP 8.1+ with Composer
- Node.js 18+ with npm
- MySQL/MariaDB database
- Git

## 📁 Project Structure

```
workspace/
├── ajurnie/          # Laravel API Backend
└── ajurnie-react/    # React Frontend
```

## 🔧 Laravel API Setup (ajurnie/)

### 1. Install Dependencies
```bash
cd ajurnie
composer install
```

### 2. Environment Configuration
Create `.env` file:
```bash
cp .env.example .env
```

Configure your `.env` file:
```env
APP_NAME=Ajurnie
APP_ENV=local
APP_KEY=base64:your-key-here
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=ajurnie_fitness
DB_USERNAME=your_username
DB_PASSWORD=your_password

MAIL_MAILER=smtp
MAIL_HOST=mailpit
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS="hello@example.com"
MAIL_FROM_NAME="${APP_NAME}"

ADMIN_EMAIL=admin@ajurnie.com
```

### 3. Generate Application Key
```bash
php artisan key:generate
```

### 4. Run Database Migrations
```bash
php artisan migrate
```

### 5. Clear Cache
```bash
php artisan config:clear
php artisan route:clear
php artisan cache:clear
```

### 6. Start Laravel Server
```bash
php artisan serve
```

The API will be available at `http://localhost:8000`

## ⚛️ React Frontend Setup (ajurnie-react/)

### 1. Install Dependencies
```bash
cd ajurnie-react
npm install
```

### 2. Environment Configuration
Create `.env` file:
```env
VITE_API_URL=http://localhost:8000
```

### 3. Start Development Server
```bash
npm run dev
```

The React app will be available at `http://localhost:3000`

## 🔐 Authentication Flow

### How it Works
1. **Registration:** User registers via React form → Laravel API creates user and returns token
2. **Login:** User logs in via React form → Laravel API validates credentials and returns token
3. **API Calls:** React automatically includes token in Authorization header
4. **CSRF Protection:** React automatically fetches CSRF tokens for stateful requests

### API Endpoints
- `POST /api/register` - User registration
- `POST /api/login` - User login
- `POST /api/logout` - User logout
- `GET /api/user` - Get current user
- `POST /api/store-goal` - Submit fitness goal
- `GET /api/csrf-token` - Get CSRF token

## 🛠️ Development

### Laravel API Development
- **Port:** 8000
- **Hot Reload:** `php artisan serve`
- **Database:** Use Laravel migrations and seeders
- **Testing:** `php artisan test`

### React Frontend Development
- **Port:** 3000
- **Hot Reload:** `npm run dev`
- **Build:** `npm run build`
- **Linting:** `npm run lint`

## 🔧 Configuration Files

### Laravel API Configuration
- **CORS:** `config/cors.php` - Configured for React frontend
- **Sanctum:** `config/sanctum.php` - Token-based authentication
- **Middleware:** `bootstrap/app.php` - CSRF exclusion for API routes

### React Frontend Configuration
- **API:** `src/api/api.ts` - Centralized API configuration
- **Auth:** `src/contexts/AuthContext.tsx` - Authentication state management
- **Routes:** `src/App.tsx` - Protected routes configuration

## 🚀 Production Deployment

### Laravel API Production
1. Set `APP_ENV=production` in `.env`
2. Set `APP_DEBUG=false` in `.env`
3. Configure production database
4. Set up proper mail configuration
5. Configure web server (Apache/Nginx)

### React Frontend Production
1. Build the app: `npm run build`
2. Set `VITE_API_URL` to production API URL
3. Deploy `dist/` folder to web server
4. Configure web server for SPA routing

## 🔍 Troubleshooting

### Common Issues

1. **CORS Errors**
   - Check `config/cors.php` configuration
   - Ensure frontend URL is in `allowed_origins`

2. **CSRF Token Errors**
   - Verify Sanctum configuration
   - Check API routes are properly excluded from CSRF

3. **Authentication Issues**
   - Check token storage in localStorage
   - Verify API endpoints are working
   - Check Sanctum stateful domains configuration

4. **Database Connection**
   - Verify database credentials in `.env`
   - Run migrations: `php artisan migrate`

### Debug Commands
```bash
# Laravel
php artisan route:list
php artisan config:cache
php artisan cache:clear

# React
npm run build
npm run lint
```

## 📚 Additional Resources

- [Laravel Documentation](https://laravel.com/docs)
- [Laravel Sanctum Documentation](https://laravel.com/docs/sanctum)
- [React Documentation](https://react.dev)
- [Vite Documentation](https://vitejs.dev)

## 🤝 Support

For issues or questions:
1. Check the troubleshooting section
2. Review Laravel and React logs
3. Verify environment configuration
4. Test API endpoints independently
