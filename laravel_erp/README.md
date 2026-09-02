# 🎓 MEMA ERP Application Server (Laravel 12)

[![Laravel 12](https://img.shields.io/badge/Laravel-12.x-FF2D20?style=for-the-badge&logo=laravel)](https://laravel.com)
[![PHP 8.3+](https://img.shields.io/badge/PHP-8.3%2B-777BB4?style=for-the-badge&logo=php)](https://php.net)
[![PostgreSQL](https://img.shields.io/badge/PostgreSQL-15%2B-4169E1?style=for-the-badge&logo=postgresql)](https://postgresql.org)
[![Build & Tests](https://img.shields.io/badge/Tests-126%20Passed-success?style=for-the-badge)](tests)

> **MEMA ERP Backend & Web Application Server**. For full platform documentation, network diagrams, and system design specifications, refer to the [Root README.md](../README.md).

---

## 🚀 Quick Setup Instructions

```bash
# 1. Configure environment
cp .env.example .env

# 2. Install dependencies
composer install
npm install

# 3. Generate key & link storage
php artisan key:generate
php artisan storage:link

# 4. Migrate database & seed data
php artisan migrate --seed

# 5. Build frontend assets
npm run build

# 6. Start server
php artisan serve --port=8000
```

---

## 🧪 Running Automated Tests

```bash
php artisan test
```
