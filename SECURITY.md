# Security Policy

## Supported version

Security fixes are applied to the latest version on the default branch.

## Reporting a vulnerability

Please do not publish exploitable details in a public issue. Use GitHub's private vulnerability reporting feature on this repository instead. Include the affected route or component, reproduction steps, impact, and any suggested mitigation.

Before production use:

- set `APP_ENV=production` and `APP_DEBUG=false`;
- generate a unique `APP_KEY` and strong administrator password;
- configure HTTPS and secure session cookies;
- keep `.env`, database exports, logs, and user uploads out of Git;
- configure CAPTCHA, mail, queue workers, backups, and rate limits for your environment.

The included accounts, listings, prices, addresses, and contact details are fictional demo data.
