# WP Link Redirect Track

[![Version](https://img.shields.io/badge/version-0.0.4-blue.svg)](./CHANGELOG.md)
[![License](https://img.shields.io/badge/license-MIT-green.svg)](#license)
[![WordPress](https://img.shields.io/badge/wordpress-compatible-brightgreen.svg)](https://wordpress.org)
[![PHP](https://img.shields.io/badge/php-7.4%2B-777BB4.svg)](https://www.php.net/)
[![Maintained](https://img.shields.io/badge/maintained-yes-success.svg)](https://github.com/my-code-library)

A lightweight, high‑performance WordPress plugin for creating **trackable redirect links** that fire **GA4 events** and log clicks server‑side before redirecting users.

Perfect for creators, marketers, musicians, and anyone who needs **accurate outbound click tracking** without relying on third‑party link shorteners.

---

## ✨ Features

### 🔗 Custom Redirect Post Type
Manage redirects directly in the WordPress admin:

- Title (internal name)
- Slug → becomes `/go/{slug}`
- Destination URL
- GA4 event label

### 📈 GA4 Event Tracking
Each redirect fires:

```
event: outbound_click
event_category: Redirect
event_label: {your label}
destination: {your target URL}
```

### 🗃 Server‑Side Click Logging
Clicks are stored in `wp_postmeta`:

- Works even if GA4 is blocked
- No JavaScript required
- Click count displayed in the admin list

### 🚀 Fast Redirect Endpoint
Redirects are served at:

```
/go/{slug}
```

Example:

```
/go/hard-to-walk-away
```

### 🧩 Zero Theme Edits
No templates, no shortcodes, no conflicts.

### 🛡 Fully Compatible With
- WordPress caching
- Cloudflare
- Shared hosting (Bluehost, SiteGround, etc.)
- SEO plugins (Yoast, RankMath)
- Security plugins

---

## 📦 Installation

1. Create a folder:

```
/wp-content/plugins/wp-link-redirect-track/
```

2. Add the plugin file:

```
wp-link-redirect-track.php
```

3. Paste the plugin code into the file.

4. Activate **WP Link Redirect Track** in WordPress → Plugins.

5. Go to **Redirects → Add New**.

---

## 🛠 Usage

### 1. Create a Redirect
- Go to **Redirects → Add New**
- Set the title
- Set the slug (auto‑generated or custom)
- Enter the destination URL
- Enter the GA4 event label
- Publish

### 2. Share Your Link
Your redirect URL will be:

```
https://yourdomain.com/go/{slug}
```

### 3. Track Clicks
- GA4 → Events → `outbound_click`
- WordPress → Redirects → Click count column

---

## 📊 GA4 Setup

To mark redirect clicks as conversions:

1. GA4 → Admin → Events  
2. Find `outbound_click`  
3. Toggle **Mark as conversion**

---

## 🧱 Plugin Architecture

- **Custom Post Type:** `wplr_redirect`
- **Rewrite Endpoint:** `/go/{slug}`
- **Click Logging:** `_wplr_clicks`
- **Meta Fields:** `_wplr_url`, `_wplr_label`
- **Admin Columns:** Click count

---

## 📜 Changelog

See [`CHANGELOG.md`](./CHANGELOG.md)

---

## 📝 License

MIT License — free to modify, distribute, and use in commercial projects.

---

## 👤 Author

**@my-code-library**  
Clean, modular, creator‑focused WordPress tools.
