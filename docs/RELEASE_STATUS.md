# Release Status

## Repository readiness

- The repository contains fictional, clearly labelled demo listings and editorial content only.
- Environment files, secrets, database exports, logs, runtime caches, customer uploads, and local deployment archives are excluded from Git.
- Brand-facing values are configurable through database-backed settings.
- Turkish and English interfaces, listings, blog routes, SEO endpoints, maps, media galleries, and protected administration routes are covered by automated tests.
- GitHub Actions runs dependency installation, frontend compilation, application tests, and dependency security audits on pushes and pull requests.

## Latest local verification

- 17 tests passed with 174 assertions.
- Production frontend assets compiled successfully.
- Composer and npm reported no known dependency vulnerabilities.
- The application was verified with the test suite's in-memory SQLite database.

## Before a production deployment

Production operators must supply their own domain, database, administrator credentials, mail settings, legal company information, listing data, media, privacy text, and any optional API credentials. They should also validate the installation against their chosen MySQL or PostgreSQL environment, web server, queue, scheduler, backups, HTTPS configuration, and local legal requirements.

See `INSTALL.md` and `SECURITY.md` for the deployment checklist.
