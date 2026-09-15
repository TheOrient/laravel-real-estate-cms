# Contributing

Contributions are welcome through GitHub issues and pull requests.

## Local workflow

1. Fork the repository and create a focused branch.
2. Follow the setup steps in `INSTALL.md`.
3. Keep UI text in both `lang/tr` and `lang/en`; do not hardcode user-facing copy in controllers or views.
4. Add or update tests for behavior changes.
5. Run the checks below before opening a pull request.

```bash
./vendor/bin/pint --test
php artisan test
npm run build
composer audit --locked --no-interaction
npm audit --audit-level=high
```

Never commit `.env` files, API keys, database dumps, logs, customer data, or uploaded listing media.
