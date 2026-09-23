# Installation Guide

This guide covers a clean local installation and the main production preparation steps for the Real Estate Corporate Website & CMS Listing System.

## Requirements

- PHP 8.2 or later
- Composer 2.5 or later
- Node.js 20.19+ or 22.12+ and npm 9+
- MySQL 8, MariaDB 10.6+, or PostgreSQL 15+
- PHP extensions required by Laravel, including Ctype, cURL, DOM, Fileinfo, Filter, Hash, Mbstring, OpenSSL, PCRE, PDO, Session, Tokenizer, and XML
- A web server such as Nginx or Apache for production

Optional integrations may require their own API credentials. The base website does not require an AI API or Google Maps key.

## 1. Download the project

```bash
git clone https://github.com/TheOrient/laravel-real-estate-cms.git
cd laravel-real-estate-cms
```

For a stable production deployment, check out a tagged release when one is available instead of tracking the development branch.

## 2. Install dependencies

```bash
composer install
npm install
npm run build
```

For production, install PHP packages without development dependencies:

```bash
composer install --no-dev --prefer-dist --optimize-autoloader
```

## 3. Create the environment file

```bash
cp .env.example .env
php artisan key:generate
```

Set the application URL and database connection in `.env`:

```env
APP_NAME="Real Estate CMS"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=real_estate_cms
DB_USERNAME=real_estate_user
DB_PASSWORD=use-a-strong-database-password
```

PostgreSQL is also supported:

```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=real_estate_cms
DB_USERNAME=real_estate_user
DB_PASSWORD=use-a-strong-database-password
```

Do not reuse the example passwords in a real environment.

## 4. Configure the initial administrator

Add unique credentials before running the seeders:

```env
ADMIN_NAME="Site Administrator"
ADMIN_EMAIL=admin@example.test
ADMIN_PASSWORD=replace-with-a-long-unique-password
```

The initial administrator account is created only during the fresh-install seeding process.

## 5. Create the database

Create an empty database and grant the configured database user access to it. Use a dedicated user with only the permissions the application needs.

Example for MySQL:

```sql
CREATE DATABASE real_estate_cms
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

CREATE USER 'real_estate_user'@'localhost'
  IDENTIFIED BY 'replace-with-a-strong-password';

GRANT ALL PRIVILEGES ON real_estate_cms.*
  TO 'real_estate_user'@'localhost';

FLUSH PRIVILEGES;
```

Make sure the password matches the value in `.env`.

## 6. Run migrations and install demo data

```bash
php artisan migrate
php artisan db:seed
```

The seeders provide fictional property listings, blog posts, pages, settings, and an administrator account so the interface can be evaluated immediately.

> Only run the full seed process on a new, empty database. Do not run it against an existing production database unless you have reviewed every seeder and have a verified backup.

## 7. Create the public storage link

```bash
php artisan storage:link
```

The web-server user must be able to write to `storage` and `bootstrap/cache`.

## 8. Start the local server

```bash
php artisan serve
```

Open:

- Website: `http://localhost:8000`
- Administration sign-in: `http://localhost:8000/login`

Use the administrator credentials configured in `.env`.

## Optional services

### AI translation and visitor chatbot

```env
GROQ_API_KEY=your-api-key
GROQ_MODEL=your-compatible-model
```

Without these values, the application continues to work and displays source-language content. AI-backed features remain disabled or use their built-in fallback behavior.

### Google Maps

```env
MAPS_PROVIDER=google
GOOGLE_MAPS_API_KEY=your-google-maps-key
```

If these values are omitted, the default Leaflet and OpenStreetMap-compatible setup is used.

### Mail delivery

Configure a production mail provider for password reset and other application messages:

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.example.test
MAIL_PORT=587
MAIL_USERNAME=your-smtp-user
MAIL_PASSWORD=your-smtp-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=no-reply@example.test
MAIL_FROM_NAME="${APP_NAME}"
```

## Production deployment

### Environment

Use production-safe values:

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://properties.example.com
LOG_LEVEL=warning
```

Keep `.env` outside public downloads, restrict its filesystem permissions, and never commit it to Git.

### Nginx example

Point the document root to the Laravel `public` directory:

```nginx
server {
    listen 80;
    server_name properties.example.com;
    root /var/www/real-estate-cms/public;

    index index.php;
    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    location ~ \.php$ {
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        fastcgi_param DOCUMENT_ROOT $realpath_root;
        fastcgi_pass unix:/run/php/php8.2-fpm.sock;
    }

    location ~ /\.ht {
        deny all;
    }
}
```

Adjust the PHP-FPM socket and project path for your server. Add HTTPS with your hosting provider or certificate manager before accepting real traffic.

### Permissions

Grant write access only where Laravel needs it:

```bash
chown -R www-data:www-data storage bootstrap/cache
chmod -R ug+rwX storage bootstrap/cache
```

The exact web-server user may differ by operating system.

### Cache and optimization

After production environment variables are final:

```bash
php artisan optimize
php artisan view:cache
```

After changing configuration or routes:

```bash
php artisan optimize:clear
php artisan optimize
```

### Queues

If queue-backed features are enabled, run a supervised worker:

```bash
php artisan queue:work --sleep=3 --tries=3 --timeout=120
```

Use systemd, Supervisor, or the process manager provided by your hosting platform. Restart workers after every deployment:

```bash
php artisan queue:restart
```

### Scheduler

Add Laravel's scheduler to cron:

```cron
* * * * * cd /var/www/real-estate-cms && php artisan schedule:run >> /dev/null 2>&1
```

### Final launch checklist

- Replace every fictional listing, image, article, page, address, and contact detail.
- Change the seeded administrator password and remove unused accounts.
- Confirm `APP_DEBUG=false`.
- Configure HTTPS and secure cookies.
- Configure email delivery and test password resets.
- Review upload size limits in PHP and the web server.
- Confirm that `storage` is writable but source files are not publicly editable.
- Verify backups and perform a restore test.
- Review portal feeds before sharing their URLs with external services.
- Run automated tests and dependency security checks.

## Testing

```bash
php artisan test
npm audit
composer audit
```

The GitHub Actions workflow runs the main application checks for pushed commits and pull requests.

## Updating an installation

Back up the database, environment file, and uploaded media before every update. Then review the release notes and run:

```bash
git pull --ff-only
composer install --no-dev --prefer-dist --optimize-autoloader
npm install
npm run build
php artisan migrate --force
php artisan optimize:clear
php artisan optimize
php artisan queue:restart
```

Never run `db:seed` as a routine update step.

## Backups

A useful backup includes:

- A database dump
- The `.env` file, stored securely
- `storage/app/public` and any other uploaded media
- Any deployment-specific configuration

Encrypt off-server backups and periodically verify that they can be restored.

## Troubleshooting

### Application key error

```bash
php artisan key:generate
```

### Database connection error

Check `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, and `DB_PASSWORD`. Confirm that the database service is running and the configured user has access.

### Images or uploads are missing

```bash
php artisan storage:link
```

Also verify read/write permissions for `storage` and confirm that the web root points to `public`.

### Cached values do not reflect changes

```bash
php artisan optimize:clear
```

### Frontend assets are missing

```bash
npm install
npm run build
```

### Permission errors

Ensure the web-server user can write to `storage` and `bootstrap/cache`, but avoid broad world-writable permissions.

For security issues, follow the private reporting process in [SECURITY.md](SECURITY.md).
