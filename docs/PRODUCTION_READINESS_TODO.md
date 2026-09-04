# 🚀 MEMA ERP — Production Readiness, Architectural Evaluation & Deployment Checklist

This document provides a comprehensive architectural evaluation of web server options (specifically **Nginx: Why and Why Not**), followed by an end-to-end, actionable checklist for deploying, hardening, scaling, and maintaining **MEMA College & University ERP** in a high-availability production environment.

---

## 🏛️ 1. Web Server Evaluation: Why Nginx (And Why Not?)

### 1.1. Executive Verdict
**Nginx + PHP-FPM 8.3** is the **recommended web server tier** for MEMA ERP's production environment. It provides the optimal balance of event-driven concurrency, low RAM consumption, sub-millisecond static file delivery (transcripts, syllabi, stylesheets), and reliable SSL/TLS termination under peak student traffic (e.g., KCSE release days and semester registration cutoffs).

---

### 1.2. Why Use Nginx? (Pros & Architectural Advantages)

| Advantage | Deep Architectural Rationale | Direct Benefit to MEMA ERP |
| :--- | :--- | :--- |
| **Event-Driven, Asynchronous I/O** | Uses `epoll` (Linux) / `kqueue` (BSD) non-blocking worker loops rather than creating a new thread/process per request (unlike Apache MPM prefork). | Handles 10,000+ concurrent applicant requests with minimal CPU and steady ~15-30MB RAM footprint per worker. |
| **Lightning-Fast Static Asset Offload** | Serves static assets (`.css`, `.js`, `.pdf`, `.png`, `.woff2`) directly from disk using kernel-level `sendfile()` without touching PHP runtime. | Dynamic application letter PDFs, fee schedule downloads, and portal UI assets load at line speed without consuming PHP worker threads. |
| **Reverse Proxying & Micro-Caching** | Built-in upstream load balancing (`round-robin`, `least_conn`, `ip_hash`) and FastCGI micro-caching (`fastcgi_cache`). | Public programme catalogue (`/programmes/apply`) and syllabus pages can be cached for 1–5 minutes, reducing database load to near zero. |
| **TLS/SSL Handshake Acceleration** | Native support for TLSv1.3, Session Tickets, OCSP Stapling, and HTTP/2 multiplexing. | Mobile users on 3G/4G Safaricom connections experience rapid HTTPS handshakes and multiplexed CSS/JS bundling. |
| **Granular Rate Limiting & DDoS Defense** | `limit_req_zone` and `limit_conn_zone` modules enable IP-based burst throttling. | Prevents brute-force attacks on applicant OTP endpoints, admission submission endpoints, and student portal logins. |
| **Seamless PHP-FPM Separation** | Clear architectural boundary between the HTTP gateway (Nginx) and application execution layer (PHP-FPM). | PHP memory leaks or worker crashes are isolated; Nginx remains responsive and returns clean 502/503 branded error pages. |

---

### 1.3. Why NOT Nginx? (Limitations, Trade-Offs & Counter-Measures)

| Potential Limitation | Comparison with Alternatives | Counter-Measure in MEMA ERP |
| :--- | :--- | :--- |
| **No `.htaccess` Support** | Unlike Apache, Nginx does not allow per-directory runtime overrides; all rewrite rules must be compiled in the main server block. | All URL rewrites and header security policies are centralized in `/etc/nginx/sites-available/memaerp.conf`. This is faster and more secure. |
| **Not a Long-Running App Server (like FrankenPHP / RoadRunner / Octane)** | Standard Nginx + PHP-FPM terminates and boots the Laravel kernel per request, incurring standard framework bootstrap overhead (~20-40ms). | We utilize OPcache preloading and file caching. For ultra-high throughput endpoints, Laravel Octane with FrankenPHP can be evaluated as an internal upstream behind Nginx. |
| **Separate Process Management** | Requires maintaining both `nginx.service` and `php8.3-fpm.service`, plus tuning `pm.max_children` to prevent process exhaustion. | Provided in this checklist: explicit mathematical sizing formulas for PHP-FPM based on available VM RAM. |
| **WebSockets Require Upstream Proxying** | Native WebSockets or live student chat requires proxy headers (`Upgrade` / `Connection: "upgrade"`). | Handled via configured Nginx reverse proxy directives pointing to Laravel Reverb / Pusher socket ports. |

---

## 📑 2. Production Checklist Summary

- [ ] **Section 3: Infrastructure, Nginx & TLS Automation**
- [ ] **Section 4: PHP 8.3 & OPcache Preloading Configuration**
- [ ] **Section 5: PostgreSQL Database Tuning & Backup Vaulting**
- [ ] **Section 6: Background Workers & Master Scheduler (Supervisor)**
- [ ] **Section 7: Security Hardening & Environment Policies**
- [ ] **Section 8: External Integrations (M-Pesa Daraja, SMTP, KUCCPS)**
- [ ] **Section 9: Document Storage, Symlinks & PDF Engines**
- [ ] **Section 10: CI/CD & Automated Git Push Workflows**
- [ ] **Section 11: Monitoring, Healthchecks & Observability**
- [ ] **Section 12: Production Deployment Runbook & Rollback Protocol**

---

## 🌐 3. Infrastructure, Nginx & TLS Automation

### 3.1. Production Nginx Virtual Host Configuration
Save the following configuration to `/etc/nginx/sites-available/memaerp.conf`:

```nginx
# HTTP - Redirect all traffic to HTTPS
server {
    listen 80;
    listen [::]:80;
    server_name erp.mema.ac.ke admissions.mema.ac.ke portal.mema.ac.ke;
    return 301 https://$host$request_uri;
}

# Rate limiting zones
limit_req_zone $binary_remote_addr zone=admissions_limit:10m rate=10r/s;
limit_req_zone $binary_remote_addr zone=api_limit:10m rate=30r/s;

# HTTPS - Main Production Block
server {
    listen 443 ssl http2;
    listen [::]:443 ssl http2;
    server_name erp.mema.ac.ke admissions.mema.ac.ke portal.mema.ac.ke;

    root /var/www/memaerp/laravel_erp/public;
    index index.php index.html;

    # TLS Certificates (Let's Encrypt / Institutional)
    ssl_certificate /etc/letsencrypt/live/erp.mema.ac.ke/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/erp.mema.ac.ke/privkey.pem;
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_prefer_server_ciphers on;
    ssl_ciphers ECDHE-ECDSA-AES128-GCM-SHA256:ECDHE-RSA-AES128-GCM-SHA256:ECDHE-ECDSA-AES256-GCM-SHA384:ECDHE-RSA-AES256-GCM-SHA384;
    ssl_session_cache shared:SSL:10m;
    ssl_session_timeout 1d;
    ssl_session_tickets off;
    ssl_stapling on;
    ssl_stapling_verify on;

    # Security Headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header Referrer-Policy "strict-origin-when-cross-origin" always;
    add_header Strict-Transport-Security "max-age=31536000; includeSubDomains; preload" always;
    add_header Permissions-Policy "camera=(), microphone=(), geolocation=()" always;

    # Response Compression
    gzip on;
    gzip_vary on;
    gzip_proxied any;
    gzip_comp_level 6;
    gzip_types text/plain text/css application/json application/javascript text/xml application/xml application/xml+rss text/javascript image/svg+xml;
    gzip_min_length 256;

    # Document Upload Limits (Transcripts, Certificates, P9, Passport photos)
    client_max_body_size 25M;
    client_body_buffer_size 128k;

    # Static Asset Delivery & Long-Term Caching
    location ~* \.(jpg|jpeg|png|gif|ico|css|js|woff2|woff|ttf|svg|pdf|webp)$ {
        expires 30d;
        add_header Cache-Control "public, no-transform, immutable";
        access_log off;
        try_files $uri =404;
    }

    # Public Admissions & Application Rate-Limited Location
    location /programmes/apply {
        limit_req zone=admissions_limit burst=20 nodelay;
        try_files $uri $uri/ /index.php?$query_string;
    }

    # API Endpoints (M-Pesa Callbacks, KUCCPS webhooks)
    location /api/ {
        limit_req zone=api_limit burst=50 nodelay;
        try_files $uri $uri/ /index.php?$query_string;
    }

    # Laravel Front Controller
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    # PHP-FPM Handler
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
        fastcgi_buffer_size 128k;
        fastcgi_buffers 4 256k;
        fastcgi_busy_buffers_size 256k;
        fastcgi_read_timeout 120;
    }

    # Block access to hidden dotfiles, git, and env
    location ~ /\.(?!well-known).* {
        deny all;
        access_log off;
        log_not_found off;
    }

    # Custom Error Pages
    error_page 404 /index.php;
    error_page 500 502 503 504 /50x.html;
    location = /50x.html {
        root /var/www/memaerp/laravel_erp/public;
    }
}
```

### 3.2. Activation Steps
- [ ] Symlink: `sudo ln -sf /etc/nginx/sites-available/memaerp.conf /etc/nginx/sites-enabled/`
- [ ] Remove default site: `sudo rm -f /etc/nginx/sites-enabled/default`
- [ ] Test syntax: `sudo nginx -t`
- [ ] Reload service: `sudo systemctl reload nginx`
- [ ] Setup automated SSL via Certbot:
  ```bash
  sudo apt install -y certbot python3-certbot-nginx
  sudo certbot --nginx -d erp.mema.ac.ke -d admissions.mema.ac.ke -d portal.mema.ac.ke
  sudo certbot renew --dry-run
  ```

---

## ⚡ 4. PHP 8.3 & OPcache Preloading Configuration

### 4.1. PHP-FPM Process Pool (`/etc/php/8.3/fpm/pool.d/www.conf`)
Calculate `pm.max_children` using: `(Total RAM - OS/DB RAM) / Average PHP Process Size (60MB)`.  
*For an 8GB RAM Dedicated Web Server with 2GB reserved for OS/PostgreSQL:*

```ini
[www]
user = www-data
group = www-data
listen = /var/run/php/php8.3-fpm.sock
listen.owner = www-data
listen.group = www-data
listen.mode = 0660

pm = dynamic
pm.max_children = 60
pm.start_servers = 15
pm.min_spare_servers = 10
pm.max_spare_servers = 30
pm.max_requests = 1000
pm.process_idle_timeout = 10s

; Telemetry and monitoring
pm.status_path = /fpm-status
ping.path = /fpm-ping

catch_workers_output = yes
decorate_workers_output = no
```

### 4.2. OPcache & Memory Settings (`/etc/php/8.3/fpm/php.ini`)
```ini
[PHP]
memory_limit = 512M
max_execution_time = 120
max_input_time = 60
upload_max_filesize = 25M
post_max_size = 25M
expose_php = Off

[opcache]
opcache.enable = 1
opcache.enable_cli = 1
opcache.memory_consumption = 256
opcache.interned_strings_buffer = 16
opcache.max_accelerated_files = 30000
opcache.validate_timestamps = 0         ; Set to 0 in production; invalidate on deploy
opcache.revalidate_freq = 0
opcache.save_comments = 1
opcache.fast_shutdown = 1
```

---

## 🐘 5. PostgreSQL Database Tuning & Backup Vaulting

### 5.1. PostgreSQL 15/16 Tuning (`/etc/postgresql/15/main/postgresql.conf`)
*Optimized for 8GB dedicated DB server or shared application VM:*

```ini
shared_buffers = 2GB                   # 25% of total server RAM
effective_cache_size = 6GB             # 75% of total server RAM
work_mem = 32MB                        # For complex grading aggregations
maintenance_work_mem = 256MB
min_wal_size = 1GB
max_wal_size = 4GB
checkpoint_completion_target = 0.9
wal_buffers = 16MB
default_statistics_target = 100
random_page_cost = 1.1                 # NVMe/SSD storage
effective_io_concurrency = 200
max_connections = 200
```

### 5.2. PgBouncer Connection Pooler (Recommended for >2,000 Concurrent Students)
- [ ] Install: `sudo apt install -y pgbouncer`
- [ ] Configure `pool_mode = transaction`, `max_client_conn = 1000`, `default_pool_size = 40` on port `6432`.
- [ ] Update `.env`: `DB_PORT=6432`.

### 5.3. Automated Offsite Backup Vault Script
Create `/usr/local/bin/memaerp-backup.sh`:

```bash
#!/usr/bin/env bash
set -euo pipefail

BACKUP_DIR="/var/backups/memaerp"
DATE=$(date +"%Y%m%d_%H%M%S")
FILENAME="${BACKUP_DIR}/mema_db_${DATE}.sql.gz"

mkdir -p "${BACKUP_DIR}"

# Export PostgreSQL dump
sudo -u postgres pg_dump -Fc mema_erp_laravel | gzip > "${FILENAME}"
chmod 600 "${FILENAME}"

# Optional: Sync to Amazon S3 / Cloud Storage
# aws s3 cp "${FILENAME}" s3://memaerp-backups/database/

# Retain local backups for 30 days
find "${BACKUP_DIR}" -type f -name "mema_db_*.sql.gz" -mtime +30 -delete

echo "[$(date)] Backup completed successfully: ${FILENAME}"
```
- [ ] Schedule in crontab: `0 2 * * * /usr/local/bin/memaerp-backup.sh >> /var/log/memaerp_backup.log 2>&1`
- [ ] Test restore procedure into a staging sandbox quarterly.

---

## ⚙️ 6. Background Workers & Master Scheduler (Supervisor)

### 6.1. Supervisor Worker Pool (`/etc/supervisor/conf.d/memaerp-workers.conf`)
```ini
[program:memaerp-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/memaerp/laravel_erp/artisan queue:work database --sleep=3 --tries=3 --max-time=3600 --timeout=90 --backoff=10,60,300
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=4
redirect_stderr=true
stdout_logfile=/var/www/memaerp/laravel_erp/storage/logs/worker.log
stdout_logfile_maxbytes=20MB
stdout_logfile_backups=5
stopwaitsecs=3600
```

### 6.2. Master System Cron Scheduler (`/etc/cron.d/memaerp-scheduler`)
```cron
# Laravel schedule runner executes every minute
* * * * * www-data cd /var/www/memaerp/laravel_erp && php artisan schedule:run >> /dev/null 2>&1
```

---

## 🔒 7. Security Hardening & Environment Policies

### 7.1. Critical `.env` Settings for Production
- [ ] `APP_ENV=production`
- [ ] `APP_DEBUG=false`
- [ ] `APP_URL=https://erp.mema.ac.ke`
- [ ] `APP_KEY` generated: `php artisan key:generate`
- [ ] `SESSION_DRIVER=database`
- [ ] `SESSION_SECURE_COOKIE=true`
- [ ] `SESSION_HTTP_ONLY=true`
- [ ] `SESSION_SAME_SITE=lax`
- [ ] `SESSION_LIFETIME=120`

### 7.2. Linux Permissions Matrix
```bash
sudo chown -R www-data:www-data /var/www/memaerp/laravel_erp
sudo find /var/www/memaerp/laravel_erp -type f -exec chmod 644 {} \;
sudo find /var/www/memaerp/laravel_erp -type d -exec chmod 755 {} \;
sudo chmod -R 775 /var/www/memaerp/laravel_erp/storage
sudo chmod -R 775 /var/www/memaerp/laravel_erp/bootstrap/cache
```

### 7.3. Kenyan Data Protection Act 2019 (DPA) Compliance
- [ ] Soft delete audit trails verified in [`RecycleBinService`](file:///Users/wabwire/Dev/memaerp/laravel_erp/app/Services/RecycleBinService.php).
- [ ] All student national IDs, medical records, and payment logs protected by role-based permissions (`spatie/laravel-permission`).
- [ ] Password policies enforced (minimum 8 characters, mixed case, symbols).

---

## 🔌 8. External Integrations (M-Pesa Daraja, SMTP, KUCCPS)

### 8.1. Safaricom M-Pesa Daraja 2.0 Live Integration
- [ ] Register URLs on Daraja Developer Portal:
  - **Validation URL**: `https://erp.mema.ac.ke/api/v1/admissions/payments/mpesa/c2b/validation`
  - **Confirmation URL**: `https://erp.mema.ac.ke/api/v1/admissions/payments/mpesa/c2b/confirmation`
- [ ] Configure live keys in `.env`:
  ```ini
  MPESA_ENV=production
  MPESA_SHORTCODE=XXXXXX
  MPESA_PASSKEY=your_live_passkey
  MPESA_CONSUMER_KEY=your_live_key
  MPESA_CONSUMER_SECRET=your_live_secret
  ```

### 8.2. Transactional Mail Service (SMTP / SES / Mailgun)
- [ ] Configure institutional DNS records: **SPF (`v=spf1 ...`)**, **DKIM**, and **DMARC (`p=reject`)**.
- [ ] Test student offer letter dispatch and invoice notifications.

---

## 📁 9. Document Storage, Symlinks & PDF Engines

- [ ] Execute `php artisan storage:link`.
- [ ] Verify writable storage directories:
  - `storage/app/public/applicant_documents`
  - `storage/app/public/templates`
  - `storage/app/public/exports`
- [ ] Ensure PDF generation engine (`barryvdh/laravel-dompdf` / `mpdf`) renders high-resolution institutional crests, barcodes, and QR verification codes:
  - Official Admission Offer Letter
  - Medical Examination Form
  - Student Matriculation & Bond Agreement
  - Examination Transcripts

---

## 🤖 10. CI/CD & Automated Git Push Workflows

### 10.1. Automated Git Sync Confirmation
- [ ] Remote repository is linked and tracking `origin main` (`https://github.com/Dev-Ouma/memaerp.git`).
- [ ] Clean working tree with automated pre-commit linting via Laravel Pint.

### 10.2. Production Deployment GitHub Actions Workflow (`.github/workflows/deploy.yml`)
```yaml
name: Deploy MEMA ERP to Production

on:
  push:
    branches: [ main ]

jobs:
  test:
    name: Run Test Suite & Style Linter
    runs-on: ubuntu-latest
    services:
      postgres:
        image: postgres:15
        env:
          POSTGRES_DB: mema_erp_testing
          POSTGRES_USER: postgres
          POSTGRES_PASSWORD: password
        ports:
          - 5432:5432
        options: --health-cmd pg_isready --health-interval 10s --health-timeout 5s --health-retries 5
    steps:
      - uses: actions/checkout@v4
      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.3'
          extensions: mbstring, xml, ctype, iconv, intl, pdo_pgsql, bcmath, gd
      - name: Install Composer Dependencies
        working-directory: ./laravel_erp
        run: composer install --prefer-dist --no-progress
      - name: Code Style Check
        working-directory: ./laravel_erp
        run: ./vendor/bin/pint --test
      - name: Execute Tests
        working-directory: ./laravel_erp
        env:
          DB_CONNECTION: pgsql
          DB_HOST: 127.0.0.1
          DB_PORT: 5432
          DB_DATABASE: mema_erp_testing
          DB_USERNAME: postgres
          DB_PASSWORD: password
        run: php artisan test

  deploy:
    name: Zero-Downtime Server Release
    needs: test
    runs-on: ubuntu-latest
    if: github.ref == 'refs/heads/main'
    steps:
      - name: SSH and Deploy
        uses: appleboy/ssh-action@v1.0.3
        with:
          host: ${{ secrets.PROD_SERVER_HOST }}
          username: ${{ secrets.PROD_SERVER_USER }}
          key: ${{ secrets.PROD_SSH_PRIVATE_KEY }}
          script: |
            cd /var/www/memaerp/laravel_erp
            php artisan down --secret="${{ secrets.MAINTENANCE_BYPASS_SECRET }}"
            git pull origin main
            composer install --no-dev --optimize-autoloader --no-interaction
            npm ci
            npm run build
            php artisan migrate --force
            php artisan optimize:clear
            php artisan config:cache
            php artisan route:cache
            php artisan view:cache
            php artisan event:cache
            sudo supervisorctl restart memaerp-worker:*
            sudo systemctl reload php8.3-fpm
            sudo systemctl reload nginx
            php artisan up
            echo "Production Deployment Completed Successfully."
```

---

## 📊 11. Monitoring, Healthchecks & Observability

### 11.1. Healthcheck Endpoints
- [ ] Healthcheck URL: `GET /health` or `GET /up` (HTTP 200).
- [ ] OpsCenter dashboard: `/admin-setups/system-maintenance`.
- [ ] Load balancer telemetry: `/admin-setups/load-balancer`.

### 11.2. Log Rotation (`/etc/logrotate.d/memaerp`)
```text
/var/www/memaerp/laravel_erp/storage/logs/*.log {
    daily
    missingok
    rotate 14
    compress
    delaycompress
    notifempty
    create 0664 www-data www-data
    sharedscripts
    postrotate
        [ -f /var/run/php/php8.3-fpm.pid ] && kill -USR1 `cat /var/run/php/php8.3-fpm.pid`
    endscript
}
```

---

## 🚀 12. Production Deployment Runbook & Rollback Protocol

### 12.1. Standard Release Execution
```bash
# 1. Pull latest release code
git pull origin main

# 2. Production dependencies
composer install --no-dev --optimize-autoloader
npm ci && npm run build

# 3. Database migrations (transactional)
php artisan migrate --force

# 4. Framework caches
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# 5. Worker & Web Server Reloads
sudo supervisorctl restart memaerp-worker:*
sudo systemctl reload php8.3-fpm
sudo systemctl reload nginx
```

### 12.2. Emergency Rollback Protocol
```bash
# If a critical defect is discovered:
php artisan down --message="Undergoing emergency maintenance. Back shortly."

# Roll back git commit
git revert HEAD --no-edit
# Or checkout last known stable tag: git checkout tags/v1.0.X

# Roll back migrations if necessary
php artisan migrate:rollback --step=1

# Clear and rebuild cache
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Reload services
sudo supervisorctl restart memaerp-worker:*
sudo systemctl reload php8.3-fpm

# Bring application back up
php artisan up
```

---

*Document Version: 1.1.0 (Production Release & Architecture Spec)*  
*Maintained by: MEMA University College ICT & Systems Engineering Team*
