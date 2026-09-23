# Listing Import Guide

The listing importer adds properties and their local image packages to an existing installation through an explicit command-line workflow. It does not crawl websites, run as a background bot, or publish listings automatically.

Imports are created as drafts by default. Publishing requires an explicit flag.

## Before you begin

1. Back up the database and uploaded media.
2. Confirm that the target categories, locations, and feature values already exist.
3. Apply the migration that adds the stable import reference:

```bash
php artisan migrate
```

The related migration is:

```text
database/migrations/2026_09_11_120000_add_import_reference_to_listings.php
```

## Inspect the destination catalog

Use the catalog command to obtain valid identifiers for categories, locations, and listing features:

```bash
php artisan listings:catalog
```

Save the output to a file if you need to map a large source dataset:

```bash
php artisan listings:catalog --json > catalog.json
```

## Import package layout

Create one directory containing `listings.json` and a folder for every listing's local images:

```text
import-package/
├── listings.json
└── images/
    ├── demo-residence-001/
    │   ├── 01-front.jpg
    │   ├── 02-living-room.jpg
    │   └── 03-kitchen.png
    └── demo-office-002/
        ├── 01-exterior.jpg
        └── 02-workspace.jpg
```

The image filenames determine gallery order. Supported formats are validated by the importer and converted to the application's optimized gallery format.

## JSON format

```json
[
  {
    "source_reference": "demo-residence-001",
    "category_id": 1,
    "subcategory_id": 2,
    "title": "Fictional Garden Residence",
    "description": "Demonstration copy only. Replace before production use.",
    "price": 425000,
    "currency": "EUR",
    "country_id": 1,
    "state_id": 1,
    "city_id": 1,
    "address": "100 Example Avenue",
    "latitude": 52.520008,
    "longitude": 13.404954,
    "bedrooms": 3,
    "bathrooms": 2,
    "area": 145,
    "feature_ids": [1, 3, 5],
    "images_directory": "images/demo-residence-001"
  }
]
```

Use only fictional or properly licensed content in public demo repositories. Never place customer exports, portal credentials, private addresses, or production media inside an import package committed to Git.

## Validate with a dry run

Always validate the package first:

```bash
php artisan listings:import /absolute/path/to/import-package/listings.json --dry-run
```

The dry run checks the input without creating records or copying images.

## Import as drafts

```bash
php artisan listings:import /absolute/path/to/import-package/listings.json
```

Review every imported listing in the administration panel before publication.

## Import and publish explicitly

```bash
php artisan listings:import /absolute/path/to/import-package/listings.json --publish
```

Use `--publish` only when the source data, media rights, pricing, location, and legal copy have already been reviewed.

## Duplicate protection and retries

- `source_reference` must be unique and stable for each external record.
- Re-running the same package updates or skips the matching record according to the importer's rules rather than creating uncontrolled duplicates.
- Keep the original package until the import has been verified.
- If an import fails, correct the reported validation issue and repeat the dry run before retrying.

## Security

- Run imports from a trusted local path, not directly from a public upload.
- Keep image and JSON packages outside the public web root.
- Validate the provenance and usage rights of every image.
- Do not embed API keys, passwords, session cookies, or portal tokens in JSON.
- Limit command access to trusted administrators.
- Back up both the database and `storage/app/public` before a large import.

## Test the importer

```bash
php artisan test
```

For a production deployment, test a small draft-only package first and verify the generated listing, location, attributes, and gallery order in the administration panel.
