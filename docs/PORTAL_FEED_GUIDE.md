# Portal Feed Integration Guide

The project includes configurable XML and JSON listing feed endpoints that can serve as a foundation for third-party property portal integrations.

These endpoints are integration templates, not a guarantee of compatibility with any named portal. Feed schemas, authentication methods, media rules, commercial terms, and approval processes can change. Obtain the current specification from the target portal and validate the generated feed before production use.

## Available endpoints

The application exposes feed routes in the following form:

```text
https://properties.example.com/feeds/listings.xml
https://properties.example.com/feeds/listings.json
```

Use the route list to confirm the exact paths in your installed version:

```bash
php artisan route:list
```

Only public, eligible listings should be included. Drafts, archived records, incomplete listings, and records excluded by business rules must remain outside the feed.

## Recommended integration process

1. Request the current technical specification and account requirements from the target portal.
2. Map required portal fields to the application's listing, location, category, feature, and media data.
3. Add any portal-specific identifiers as configurable values rather than hardcoded constants.
4. Generate a small test feed containing fictional or approved staging records.
5. Validate encoding, required fields, currency, measurement units, image URLs, and listing status.
6. Submit the test feed through the portal's official onboarding or validation process.
7. Enable production exports only after approval and monitoring are in place.

## Data quality checklist

Before exposing a feed, verify that every exported record has:

- A stable external identifier
- A valid publication status
- A title and description in the required language
- An accepted category and transaction type
- A numeric price and supported currency
- A complete location mapping
- Valid property attributes and units
- Public HTTPS image URLs
- Properly licensed media
- A canonical public listing URL
- Accurate creation and update timestamps

Avoid exporting internal notes, owner details, private contact data, unpublished media, or administration URLs.

## Portal-specific mapping

Keep each integration isolated so changes for one consumer do not affect another. A typical adapter maps:

```text
Internal listing
├── identity and status
├── category and transaction type
├── localized title and description
├── price and currency
├── country, region, city, district, and address
├── coordinates
├── rooms, area, and feature values
├── public image URLs
└── canonical listing URL
        ↓
Portal-specific XML or JSON contract
```

Prefer dedicated transformer or service classes for portal-specific rules. Keep controllers limited to authorization, request handling, and the response.

## Access control

If a portal supports a secret token, HTTP Basic authentication, signed requests, or IP allowlisting, enforce the mechanism required by its current specification.

Do not place credentials directly in routes, controllers, templates, or committed configuration. Store secrets in environment variables:

```env
PORTAL_FEED_ENABLED=false
PORTAL_FEED_TOKEN=replace-with-a-random-secret
```

When a token is used, send it in an authorization header rather than a query string whenever the receiving service supports that approach. Query-string credentials can be retained in browser history, proxy logs, and analytics systems.

## Media requirements

- Export absolute HTTPS URLs.
- Ensure images are publicly reachable without an authenticated session.
- Do not expose original uploads if a sanitized derivative is intended for publication.
- Preserve a deterministic gallery order.
- Provide dimensions and MIME types if required by the portal.
- Confirm each portal's current limits before deciding image count or file size.

## Performance and caching

For large inventories, avoid rebuilding the entire feed for every request. Generate or cache the feed for a bounded interval and invalidate it when an eligible listing changes.

Useful safeguards include:

- Response caching
- Chunked database reads
- Eager loading for related categories, locations, and media
- Request rate limiting
- Generation timeouts
- Structured error logging
- Health monitoring for stale feeds

Never cache credentials or private listing fields inside a publicly accessible file.

## XML safety

- Emit UTF-8.
- Escape all text and attribute values through a proper XML writer.
- Avoid hand-building XML through string concatenation.
- Reject invalid control characters.
- Validate the result against the portal's schema when an XSD is provided.
- Use CDATA only when the target contract specifically requires it.

## JSON safety

- Return the declared content type and UTF-8 encoding.
- Use framework serialization rather than assembling JSON strings.
- Keep numeric values numeric when required by the contract.
- Use a documented timestamp format and timezone.
- Do not leak hidden model attributes or unrelated relationships.

## Testing

Add automated coverage for:

- Draft and archived listing exclusion
- Required field mapping
- Localized content
- Category and location conversion
- Currency and numeric formatting
- XML escaping and valid JSON
- Public image URLs and ordering
- Authentication failures
- Cache invalidation after listing updates

Run the application suite before deployment:

```bash
php artisan test
```

## Operational notes

Treat a portal feed as a production data interface. Assign an owner, document the target contract version, monitor failures, rotate credentials, and keep a rollback path. If the portal changes its specification, update and revalidate its adapter before re-enabling exports.
