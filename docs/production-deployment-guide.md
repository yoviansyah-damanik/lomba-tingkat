# Production Deployment & Operation Guide: LT III Kwarcab Padangsidimpuan 2026

## 1. System Requirements
- **PHP**: 8.4+ with extensions: `bcmath`, `ctype`, `curl`, `dom`, `fileinfo`, `filter`, `gd` / `imagick`, `hash`, `mbstring`, `openssl`, `pcre`, `pdo`, `pdo_mysql`, `session`, `tokenizer`, `xml`, `zip`.
- **Database**: MariaDB 11.4+ / 11.5+ or MySQL 8.0+ (utf8mb4_unicode_ci collation, InnoDB engine).
- **Cache & Queue**: Redis 7.0+ for high-throughput session, cache, and WebSocket queue handling.
- **Web Server**: NGINX with HTTP/2 and SSL/TLS certificate (Let's Encrypt / Cloudflare).
- **Node.js**: Node 20 LTS or higher for asset compilation.

## 2. Environment Configuration (`.env`)
```env
APP_NAME="LT III Kwarcab Padangsidimpuan"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://lt3padangsidimpuan.id
APP_TIMEZONE=Asia/Jakarta

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=lomba_tingkat
DB_USERNAME=lt3_user
DB_PASSWORD=YOUR_STRONG_PASSWORD

CACHE_STORE=redis
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis

BROADCAST_CONNECTION=reverb
REVERB_APP_ID=lt3_production_app
REVERB_APP_KEY=YOUR_REVERB_KEY
REVERB_APP_SECRET=YOUR_REVERB_SECRET
REVERB_HOST=lt3padangsidimpuan.id
REVERB_PORT=443
REVERB_SCHEME=https

VAPID_PUBLIC_KEY=YOUR_VAPID_PUBLIC_KEY
VAPID_PRIVATE_KEY=YOUR_VAPID_PRIVATE_KEY
```

## 3. Step-by-Step Deployment Routine
1. **Clone & Dependencies**:
   ```bash
   git clone https://github.com/kwarcab-padangsidimpuan/lomba-tingkat.git /var/www/lomba-tingkat
   cd /var/www/lomba-tingkat
   composer install --no-dev --optimize-autoloader
   npm ci && npm run build
   ```

2. **Storage Symlink & File Permissions**:
   ```bash
   php artisan storage:link
   chown -R www-data:www-data storage bootstrap/cache
   chmod -R 775 storage bootstrap/cache
   ```

3. **Database Migration & Seed Master Roles**:
   ```bash
   php artisan migrate --force
   php artisan db:seed --class=RoleSeeder --force
   ```

4. **Framework Optimization**:
   ```bash
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   php artisan event:cache
   ```

## 4. Background Services (Supervisor)
Create `/etc/supervisor/conf.d/lt3.conf`:
```ini
[program:lt3-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/lomba-tingkat/artisan queue:work redis --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/log/supervisor/lt3-worker.log

[program:lt3-reverb]
command=php /var/www/lomba-tingkat/artisan reverb:start
autostart=true
autorestart=true
user=www-data
redirect_stderr=true
stdout_logfile=/var/log/supervisor/lt3-reverb.log
```
Reload supervisor:
```bash
supervisorctl reread
supervisorctl update
supervisorctl start all
```

## 5. Cron Job Scheduling
Add to crontab (`crontab -e -u www-data`):
```cron
* * * * * cd /var/www/lomba-tingkat && php artisan schedule:run >> /dev/null 2>&1
```

## 6. Pre-Event Sanity Checks
- Verify `Asia/Jakarta` WIB time on server: `date`
- Test QR scanner with mobile camera over HTTPS (WebRTC requires HTTPS)
- Verify PWA install prompt and Service Worker registration on mobile browser
- Confirm score access rules: only assigned judges can input scores
