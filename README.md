## Project Setup

```bash
composer install
php artisan migrate
php artisan db:seed
php artisan key:generate
php artisan passport:install
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
php -S 127.0.0.1:8000 -t public
```

Once the server is running, the API will be accessible at:

```
http://127.0.0.1:8000
```
