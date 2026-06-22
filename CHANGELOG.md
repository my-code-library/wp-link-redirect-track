# Changelog
All notable changes to **WP Link Redirect Track** will be documented in this file.

The format follows **Semantic Versioning**:  
`MAJOR.MINOR.PATCH`  
- **MAJOR** — breaking changes  
- **MINOR** — new features (backwards‑compatible)  
- **PATCH** — bug fixes, optimizations, internal improvements  

---

## [0.0.3] – 2026-06-22
### Added
- Updated official plugin header to use:
  - `Plugin Name: WP Link Redirect Track`
  - `Author: @my-code-library`
- Standardized naming for future releases.
- Prepared repository structure for semantic versioning.

### Changed
- Improved internal naming consistency for future maintainability.

---

## [0.0.2] – 2026-06-21
### Added
- Introduced generic, professional prefix `wplr_` across all functions, meta keys, and CPT names.
- Updated plugin architecture to match WordPress best practices.
- Improved code readability and modularity.

### Changed
- Replaced previous brand‑specific prefixes with neutral namespace.
- Cleaned up comments and internal documentation.

---

## [0.0.1] – 2026-06-20
### Added
- Initial plugin release.
- Custom post type `wplr_redirect`.
- Admin UI for creating redirect entries.
- Meta fields for destination URL and GA4 event label.
- Front‑end redirect endpoint `/go/{slug}`.
- GA4 outbound click event firing.
- Server‑side click logging stored in `wp_postmeta`.
- Admin column showing click counts.
- Fully functional redirect template with timed JS redirect.

---

## Upcoming
### Planned
- UTM auto‑tagging system.
- Server‑side GA4 Measurement Protocol fallback.
- Analytics dashboard with charts and referrer tracking.
- CSV export for click logs.
- QR code generator for each redirect.
- REST API endpoints for external integrations.

