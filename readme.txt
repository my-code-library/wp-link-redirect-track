=== WP Link Redirect Track ===
Contributors: my-code-library
Tags: redirect, tracking, analytics, ga4, outbound links
Requires at least: 5.0
Tested up to: 6.5
Requires PHP: 7.4
Stable tag: 0.0.3
License: MIT
License URI: https://opensource.org/licenses/MIT

Creates trackable redirect pages that fire GA4 events before redirecting. Includes click logging, custom post type, and clean /go/{slug} endpoints.

== Description ==

WP Link Redirect Track is a lightweight, high‑performance plugin for creating trackable redirect links inside WordPress.

Each redirect:

- Fires a GA4 outbound_click event  
- Logs clicks server‑side  
- Redirects users via `/go/{slug}`  
- Requires no theme edits  
- Works with caching, Cloudflare, and shared hosting  

Perfect for creators, marketers, musicians, and anyone who needs accurate outbound click tracking.

== Features ==

* Custom post type for managing redirects
* GA4 event firing before redirect
* Server-side click logging
* Clean `/go/{slug}` routing
* Admin UI for creating/editing redirects
* Click count column in admin list
* Zero theme edits required
* Fully compatible with caching and SEO plugins

== Installation ==

1. Upload the plugin folder to `/wp-content/plugins/`
2. Activate **WP Link Redirect Track** through the Plugins menu
3. Go to **Redirects → Add New**
4. Create your redirect and publish
5. Share your link: `https://yourdomain.com/go/{slug}`

== Frequently Asked Questions ==

= Does this work with caching? =
Yes. Redirects are dynamic and safe with all caching layers.

= Does this work with Cloudflare? =
Yes. No special configuration required.

= Does this track clicks if GA4 is blocked? =
Yes. Server-side click logging works independently.

== Changelog ==

See `CHANGELOG.md` for full version history.

== Upgrade Notice ==

= 0.0.3 =
Updated plugin header and standardized naming.

== Screenshots ==

1. Redirect list table with click counts
2. Redirect editor screen
3. Example redirect flow

== License ==

MIT License. Free for personal and commercial use.

