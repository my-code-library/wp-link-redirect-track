# Architecture Overview

WP Link Redirect Track is built around a lightweight, modular structure:

- Custom Post Type: `wplr_redirect`
- Rewrite Endpoint: `/go/{slug}`
- Meta Fields:
  - `_wplr_url`
  - `_wplr_label`
  - `_wplr_clicks`
- GA4 Event Firing (client-side)
- Server-side click logging
- Admin UI for managing redirects
