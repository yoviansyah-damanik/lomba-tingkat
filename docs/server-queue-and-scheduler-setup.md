# Server Setup Guide: Queue Worker, Scheduler & Push Reminder (for Server AI / Operator)

Follow this runbook on the production server so the automatic Web Push reminder
("Lomba dimulai 30 menit lagi") works. Execute the steps in order and verify each one.

## 1. How the feature works
```
cron (every minute) -> php artisan schedule:run
  -> schedules:notify-upcoming   (app/Console/Commands/NotifyUpcomingSchedules.php)
     finds published schedules starting within 30 minutes with reminder_sent_at = NULL,
     sets reminder_sent_at, dispatches SendScheduleReminderPush   (app/Jobs/SendScheduleReminderPush.php)
  -> queue "notifications" -> Supervisor worker -> WebPushService::broadcastNotification()
```
- Both parts are required: **cron** (scheduler) AND **Supervisor** (queue worker). Without the worker, jobs only pile up in the queue.
- Changing a schedule's `start_time` resets `reminder_sent_at`, so the reminder is sent again for the new time.
- Cancelled/completed or unpublished schedules are skipped.

## 2. Prerequisites checklist
Run from the project root (example: `/var/www/lomba-tingkat`):
```bash
php -v                                  # PHP 8.4+
php -m | grep -E "openssl|curl|mbstring|gmp|bcmath"
date                                    # must be WIB (Asia/Jakarta)
grep -E "^(APP_TIMEZONE|QUEUE_CONNECTION|VAPID_)" .env
```
Required `.env` values:
```env
APP_TIMEZONE=Asia/Jakarta
QUEUE_CONNECTION=redis          # or "database" if Redis is not installed
VAPID_SUBJECT=mailto:panitia@domain.com
VAPID_PUBLIC_KEY=<same pair as other environments>
VAPID_PRIVATE_KEY=<same pair as other environments>
```
- Never generate a new VAPID pair if devices are already subscribed (all subscriptions would become invalid).
- If `QUEUE_CONNECTION=database`, the `jobs` and `failed_jobs` tables must exist (`php artisan migrate --force`).
- The `competition_schedules.reminder_sent_at` column lives in the original create migration
  (`2026_10_18_000004_create_competitions_and_schedules_tables.php`). On an existing database add it manually
  instead of re-running migrations:
  ```sql
  ALTER TABLE competition_schedules ADD COLUMN reminder_sent_at TIMESTAMP NULL AFTER is_published;
  ```

## 3. Supervisor
Install and create the config (adjust the path and user to the server):
```bash
sudo apt-get install -y supervisor
sudo tee /etc/supervisor/conf.d/lt3-worker.conf > /dev/null <<'CONF'
[program:lt3-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/lomba-tingkat/artisan queue:work --queue=notifications,default --sleep=3 --tries=3 --timeout=300 --max-time=3600
directory=/var/www/lomba-tingkat
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
stopwaitsecs=330
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/log/supervisor/lt3-worker.log
CONF

sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl status
```
Notes:
- `--queue=notifications,default` is mandatory; the reminder job uses the `notifications` queue.
- `--max-time=3600` makes the worker restart hourly, so it never runs stale code or config for long.
- `stopwaitsecs` must be greater than `--timeout` so in-flight pushes are not killed on restart.
- Do not run `queue:work` from the same Supervisor config as `reverb:start`; keep programs separate (see production deployment guide).

## 4. Cron (scheduler)
```bash
sudo crontab -u www-data -l 2>/dev/null | grep -q "schedule:run" || \
  (sudo crontab -u www-data -l 2>/dev/null; echo "* * * * * cd /var/www/lomba-tingkat && php artisan schedule:run >> /dev/null 2>&1") | sudo crontab -u www-data -
sudo crontab -u www-data -l
```

## 5. After every deployment
```bash
php artisan migrate --force
php artisan config:cache && php artisan route:cache && php artisan view:cache
php artisan queue:restart        # makes workers reload the new code
sudo supervisorctl status
```
`queue:restart` is mandatory after each deploy, otherwise workers keep running the old code.

## 6. Verification (do all of these)
1. Scheduler registered:
   ```bash
   php artisan schedule:list      # must show schedules:notify-upcoming every minute
   ```
2. Worker running: `sudo supervisorctl status` shows `lt3-worker:*` as `RUNNING`.
3. End-to-end test: create (or edit) a published schedule whose `start_time` is about 20 minutes from now, wait up to one minute, then:
   ```bash
   php artisan tinker --execute="echo App\Models\CompetitionSchedule::whereNotNull('reminder_sent_at')->count();"
   tail -n 50 /var/log/supervisor/lt3-worker.log
   tail -n 50 storage/logs/laravel.log | grep -i webpush
   ```
   A subscribed device should receive "Lomba dimulai 30 menit lagi". An audit log with action `push_broadcast_sent` is also recorded.
4. Failed jobs: `php artisan queue:failed` must be empty. Retry with `php artisan queue:retry all`.

## 7. Troubleshooting
| Symptom | Check |
|---|---|
| `reminder_sent_at` stays NULL | Cron not running: `grep CRON /var/log/syslog`, `php artisan schedule:list`, server timezone |
| `reminder_sent_at` filled but nothing delivered | Worker not running or not listening to `notifications`; `supervisorctl status`; `queue:failed` |
| Job runs but `sent = 0` | No subscribers, or VAPID keys empty (`php artisan config:clear` after editing `.env`) |
| `Kunci VAPID belum dikonfigurasi` on mobile | `curl https://<domain>/api/push/vapid-public-key` returns empty `publicKey`; fix `.env` and clear config cache |
| `WebPush dispatch failed` in log | Outbound HTTPS (443) blocked to `fcm.googleapis.com`, `updates.push.services.mozilla.com`, `*.push.apple.com` |
| Reminder sent twice | Two schedulers running (cron plus `schedule:work`); keep only one |
| Code changes not applied | Run `php artisan queue:restart` |

## 8. Rules for the AI agent running this guide
- Do not modify application code; only server configuration (`.env`, Supervisor, cron, web server).
- Do not print or log VAPID private keys; do not commit `.env`.
- Do not touch the `vendor` and `node_modules` folders.
- Report back: output of `schedule:list`, `supervisorctl status`, and the result of the end-to-end test.

## 9. Verification status (update after running on the server)
| Item | Status | Notes |
|---|---|---|
| Command, job, and scheduler logic | Verified (2026-10-20) | `tests/Feature/ScheduleReminderPushTest.php` (4 tests: queued once, reschedule reset, cancelled skipped, job executes and writes `push_broadcast_sent` audit log) |
| `php artisan schedule:list` | Verified locally | Shows `schedules:notify-upcoming` every minute |
| `queue:work --queue=notifications,default` | Verified locally | Processes the queue and exits cleanly, no failed jobs |
| Supervisor `lt3-worker` RUNNING | **Pending on server** | Supervisor is not available on the Windows dev machine; run section 3 and 6 on the server |
| Cron `schedule:run` | **Pending on server** | Run section 4 on the server |
| Real push delivered to a device | **Pending on server** | Requires HTTPS, VAPID keys, and a subscribed device |
