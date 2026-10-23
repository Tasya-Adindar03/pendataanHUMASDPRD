# Pendataan HUMAS DPRD

Laravel 9 application for managing companies, media, advertising plans, advertorials, payments, and PDF/Excel exports.

## Requirements

Install Docker Engine/Desktop with Docker Compose v2 or later. Docker builds install PHP 8.2, Composer 2.8, all locked Composer packages (including PHPUnit), required PHP extensions, and frontend assets using Node.js 22 and `npm ci`. PHP, Composer, Node.js, and MySQL do not need to be installed on the host.

The local stack includes MySQL 8.4 and Mailpit. Cache and sessions use files; queues run synchronously, so Redis and a queue worker are not required. This setup uses Laravel's development server and is intended for local development.

## First run

From the repository root:

```sh
cp .env.example .env
docker compose build
docker compose run --rm --no-deps app php artisan key:generate --show
```

Copy the printed `base64:...` key into `APP_KEY=` in `.env`, then start the application:

```sh
docker compose up -d
docker compose exec app php artisan migrate
```

Open <http://localhost:8000>. Mail sent by the application appears in Mailpit at <http://localhost:8025>. To populate the app with sample data, follow the demo data instructions below.

Compose supplies the internal database hostname `db` and mail hostname `mailpit`. The database is not exposed to the host. Set `DB_DATABASE`, `DB_USERNAME` (a non-root user), `DB_PASSWORD`, and `DB_ROOT_PASSWORD` in `.env` before the first startup if you want different credentials. The defaults are for local use. MySQL initializes these credentials only when its data volume is empty.

To change ports, set `APP_PORT` and `MAILPIT_PORT` in `.env`; also update `APP_URL` to match the app port. After changing `.env`, run `docker compose up -d --force-recreate app`.

Uploads and database data persist in named Docker volumes. The entrypoint creates the public storage link. Keep the same application key across restarts.

## Dashboards

- `/dashboard`: compact overview of the six most recent months.
- `/dashboard-2`: monthly analytics with a month picker (`?bulan=2026-09`), nine visualizations, and collection insights. The sidebar includes a separate Dashboard 2 menu.

Dashboard 2 groups advertorials by receipt date. Payment status and overdue balances reflect the current state, rather than a historical month-end snapshot. Current-month comparisons are provisional. Plan fulfillment follows the selected plan month and caps contribution at each plan's quota. Click chart segments or calendar dates to apply linked filters without leaving the dashboard; remove individual focus chips or use Reset. Charts can be expanded and downloaded as PNG. Detail opens a table of up to 50 matching records with CSV export. Fullscreen mode and a collection simulation slider are available; simulations never change transactions. Plan quota metrics follow month/company/media filters and intentionally ignore payment-status/day/aging filters. Charts use the existing Chart.js CDN; summaries and calendar links remain available if it cannot load.

## Demo data (six months)

After migrating, populate the six most recent months (including the current month):

```sh
docker compose exec app php artisan db:seed
```

The local/testing seeder creates 6 demo users, 24 companies, 12 media outlets (6 print and 6 online), 144 monthly advertising plans, and approximately 430 advertorials. All payment statuses, six monthly chart periods, top companies, and current-month plan progress receive data. Demo users have random passwords and are used as transaction authors.

Running it again in the same month updates demo invoices without duplicating them. Existing non-demo transactions are preserved. As the calendar advances, a later run adds the new month and retains older data. `DatabaseSeeder` only loads demo data in `local` or `testing` environments. No database reset is required.

## Development and dependencies

The image contains a snapshot of the source code and built Vite assets. After editing source, dependency manifests, or lockfiles, rebuild:

```sh
docker compose up -d --build app
```

All PHP and JavaScript dependencies are declared in `composer.json` and `package.json`, with exact versions in their lockfiles. The Docker build runs `composer install`, `composer check-platform-reqs`, `npm ci`, and `npm run build`. Commit updated lockfiles when changing dependencies.

Useful commands:

```sh
docker compose logs -f app
docker compose exec app php artisan migrate:status
docker compose exec app php artisan optimize:clear
docker compose exec app composer check-platform-reqs
docker compose down
```

`docker compose down` preserves data. `docker compose down --volumes` deletes the database and uploaded files; use it only when intentionally resetting local data.

## Testing

Build the image and configure `APP_KEY` as above, then run:

```sh
docker compose run --rm --no-deps app php artisan test
docker compose run --rm --no-deps app php artisan test --testsuite=Unit
docker compose run --rm --no-deps app php artisan test --filter=AuthenticationTest
```

`phpunit.xml` forces an in-memory SQLite database for tests, protecting the MySQL development database. The image includes the SQLite PDO extension. Tests using `RefreshDatabase` apply migrations automatically. There is no JavaScript test runner configured; `npm run build` during image creation validates the frontend build.

The repository includes Breeze authentication tests, but `routes/web.php` currently does not load `routes/auth.php`. Those tests can fail with missing routes/404 responses. The existing feature example also expects HTTP 200 at `/`, which currently redirects to `/dashboard`. These application/test mismatches are independent of the container setup.

For a manual smoke test after migrating, visit `/dashboard`, `/companies`, `/media`, `/plans`, and `/advertorials`, then create a company and media entry before adding an advertising plan or advertorial.
