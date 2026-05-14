## Project Setup

```bash
composer install
php artisan migrate
php artisan db:seed
php artisan key:generate
php artisan passport:keys
```

> say **NO** to pending migrations

```bash
php artisan passport:client --personal
```

> press **ENTER** and continue

```bash
php artisan storage:link
```

---

## Running the API Locally

You can start the API server using PHP’s built-in development server.

```bash
php artisan optimize:clear
php artisan config:clear
php artisan config:cache

php -S 127.0.0.1:8000 -t public
```

Once the server is running, the API will be accessible at:

```
http://127.0.0.1:8000
```

<!-- For Cron job -->

```bash
php artisan pharmacy:expiry-reminder
php artisan check:inactive-users
php artisan wcbp:nonconformance-highrisk
php artisan paf:daily-alert
php artisan paf:request-reminder
php artisan app:check-paf-action-required

```

<!-- For email queue job -->

```bash
php artisan queue:work

```
