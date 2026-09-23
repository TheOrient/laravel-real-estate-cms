# Real Estate Corporate Website & CMS Listing System

An open-source, multilingual real estate website and listing management system built with Laravel 12. It is designed for a single agency that needs a polished public website, a practical administration panel, property listings, a blog, maps, SEO tools, and optional AI-assisted features.

[![Application checks](https://github.com/TheOrient/laravel-real-estate-cms/actions/workflows/checks.yml/badge.svg)](https://github.com/TheOrient/laravel-real-estate-cms/actions/workflows/checks.yml)
[![License: MIT](https://img.shields.io/badge/License-MIT-green.svg)](LICENSE)
[![Laravel 12](https://img.shields.io/badge/Laravel-12-ff2d20.svg)](https://laravel.com)

> All properties, prices, names, company details, addresses, and contact information included in the repository are fictional demo content. The project contains no real customer, agency, or property portfolio data.

## Highlights

| Area | Included functionality |
|---|---|
| Property listings | Image galleries, category and subcategory taxonomy, price/location/room filters, and map coordinates |
| Multilingual content | Turkish and English interface, cookie-based locale selection, translated content support, and stable source-language slugs |
| AI assistance | Optional cached translation and visitor chatbot through a configurable Groq-compatible API |
| Maps | Leaflet with OpenStreetMap-compatible tiles by default, plus optional Google Maps support |
| Blog | Blog index, featured articles, slug-based detail pages, cover images, and translated content |
| SEO | Dynamic metadata, Open Graph and Twitter cards, sitemap, robots.txt, and structured blog data |
| Administration | AdminLTE-based dashboard for listings, blog posts, pages, settings, and media |
| White-label settings | Database-backed site identity, contact details, social links, footer content, and hero image |
| Agency workflow | A single-agency publishing model in which listings are managed by authorized administrators |

## Technology

- PHP 8.2 or later and Laravel 12
- Blade templates, Tailwind CSS, and vanilla JavaScript
- MySQL 8, MariaDB 10.6+, or PostgreSQL 15+
- Leaflet and OpenStreetMap-compatible map tiles
- Glide-based image processing
- DOMPDF-based property brochures
- Optional Groq-compatible AI integration

## Quick start

For production preparation, web-server examples, queues, backups, and troubleshooting, see the complete [installation guide](INSTALL.md).

```bash
git clone https://github.com/TheOrient/laravel-real-estate-cms.git
cd laravel-real-estate-cms

composer install
npm install
npm run build

cp .env.example .env
php artisan key:generate
```

Create an empty database, add its credentials and unique administrator credentials to `.env`, then run:

```bash
php artisan migrate
php artisan db:seed
php artisan storage:link
php artisan serve
```

Open `http://localhost:8000`. The administration sign-in page is available at `/login`. The initial administrator is created from `ADMIN_EMAIL` and `ADMIN_PASSWORD` in your environment file.

> Run the seeders only during a fresh installation on an empty database. They intentionally install fictional sample content and may not be appropriate for an existing website.

## Configuration

The application works without external AI or paid map services. Optional integrations can be enabled through environment variables.

### AI translation and chatbot

```env
GROQ_API_KEY=your-api-key
GROQ_MODEL=your-compatible-model
```

If no API key is configured, the website remains usable; AI translation and chatbot responses are simply unavailable.

### Map provider

The default map setup uses Leaflet and OpenStreetMap-compatible tiles. To use Google Maps:

```env
MAPS_PROVIDER=google
GOOGLE_MAPS_API_KEY=your-google-maps-key
```

## Languages

- Turkish and English interface translations are included in `lang/tr` and `lang/en`.
- Public visitors can switch languages from the site navigation.
- Dynamic listing, page, and blog content retains its source text and can use the configured translation service for the alternate locale.
- To add another language, create `lang/<locale>`, register it in the language seeder, and run that seeder explicitly.

All user-facing application text should use Laravel translation keys rather than hardcoded strings.

## Listing import

The project includes a command-line importer for structured listing data and local image packages. Imports are drafts by default and include validation, duplicate protection, ordered WebP galleries, and retry-safe references.

See [Listing Import Guide](docs/LISTING_IMPORT.md) for the package format and commands.

## Portal feeds

Configurable XML/JSON feed endpoints can be used as an integration foundation for external listing portals. Every portal has its own current contract, authentication rules, and approval process, so validate an export against the target portal before enabling it in production.

See [Portal Feed Integration Guide](docs/PORTAL_FEED_GUIDE.md).

## White-label customization

Public brand information is read from database-backed settings and can be changed from `/admin/settings` without editing templates.

| Setting | Purpose |
|---|---|
| `site_title` | Header identity, browser title, and footer attribution |
| `site_description` | Homepage description and metadata |
| `contact_email`, `contact_phone`, `contact_address` | Contact and footer details |
| `social_facebook_url`, `social_instagram_url` | Social profile links |
| `home_hero_image` | Homepage hero background |

Before launching a real website, replace all demo settings, sample media, placeholder legal text, and administrator credentials.

## Project structure

```text
app/
├── Http/Controllers/       Public and administration controllers
├── Models/                 Listings, categories, pages, blog posts, and settings
└── Services/               Translation, AI, import, feed, and supporting services

resources/views/
├── admin/                  Administration interface
├── blog/                   Blog index and article pages
├── components/             Shared maps, chatbot, and interface components
└── listings/               Listing search and property details

database/seeders/
├── DatabaseSeeder.php      Fresh-install seeder orchestration
└── SampleContentSeeder.php Fictional listings, articles, and site settings
```

## Security

- Never commit `.env`, API keys, production exports, customer information, or uploaded property media.
- Use a unique `APP_KEY`, administrator password, and database user for every installation.
- Set `APP_DEBUG=false` and serve the application through HTTPS in production.
- Review [SECURITY.md](SECURITY.md) before deployment and report vulnerabilities privately as described there.

## Contributing

Contributions are welcome. Please read [CONTRIBUTING.md](CONTRIBUTING.md), include tests for behavior changes, and run the project checks before opening a pull request.

## License

This project is released under the [MIT License](LICENSE).

## Acknowledgements

This project uses open-source work from the Laravel, AdminLTE, Leaflet, OpenStreetMap, Tailwind CSS, and related communities. Third-party names and trademarks belong to their respective owners.
