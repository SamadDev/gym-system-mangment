# Quick Start Commands

## Database Management

```bash
# Check migration status
php artisan migrate:status

# Rollback migrations
php artisan migrate:rollback

# Fresh migration (drops all tables)
php artisan migrate:fresh

# Run seeders
php artisan db:seed
php artisan db:seed --class=RoleSeeder
```

## Generate Code

```bash
# Make a model with migration, controller, and resource
php artisan make:model ClassName -mcr

# Make a controller
php artisan make:controller ClassNameController --resource

# Make a request (validation)
php artisan make:request StoreClassNameRequest

# Make a seeder
php artisan make:seeder ClassNameSeeder

# Make a middleware
php artisan make:middleware MiddlewareName
```

## Testing

```bash
# Start development server
php artisan serve

# Clear cache
php artisan cache:clear
php artisan config:clear
php artisan view:clear

# Create storage link
php artisan storage:link
```

## Package Installation

```bash
# PDF Generation
composer require barryvdh/laravel-dompdf

# Excel Export
composer require maatwebsite/laravel-excel

# Image Processing
composer require intervention/image
```

## Database Inspection

```bash
# Open SQLite database
sqlite3 database/database.sqlite

# List tables
.tables

# Describe table structure
.schema members

# Query data
SELECT * FROM members;

# Exit SQLite
.exit
```

## Git Commands

```bash
# Initialize git (if not done)
git init

# Add all files
git add .

# Commit
git commit -m "Initial gym management system setup"

# Add remote
git remote add origin <your-repo-url>

# Push
git push -u origin main
```

## Laravel Useful Commands

```bash
# List all routes
php artisan route:list

# Generate app key
php artisan key:generate

# Run queue worker (if using queues)
php artisan queue:work

# Create symbolic link for storage
php artisan storage:link

# Optimize application
php artisan optimize

# View configuration
php artisan config:show database
```

## Debugging

```bash
# Enable debug mode (already in .env for development)
APP_DEBUG=true

# Check logs
tail -f storage/logs/laravel.log

# Tinker (Laravel REPL)
php artisan tinker

# In tinker, test models:
>>> \App\Models\Member::count()
>>> \App\Models\Member::first()
>>> \App\Models\Role::all()
```

## Production Deployment

```bash
# Optimize for production
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Update composer dependencies
composer install --optimize-autoloader --no-dev

# Set environment to production
APP_ENV=production
APP_DEBUG=false

# Generate production key
php artisan key:generate
```
