# wp-link-redirect

A lightweight, high‑performance WordPress plugin for creating **trackable redirect links** with:

- GA4 event tracking  
- Server‑side click logging  
- Clean `/go/{slug}` redirect endpoints  
- Custom post type for managing redirects  
- Zero theme edits required  
- Fully compatible with caching, SEO plugins, and shared hosting environments  

Built for artists, creators, and marketers who need **accurate outbound click tracking** without relying on third‑party link shorteners.

---

## ✨ Features

### 🔗 Custom Redirect Post Type
Create and manage redirects directly in the WordPress admin:

- Title = internal name  
- Slug = redirect URL (`/go/my-link`)  
- Destination URL  
- GA4 event label  

### 📈 GA4 Event Tracking
Each redirect fires a GA4 event:

```
event: outbound_click
event_category: Redirect
event_label: {your label}
destination: {your target URL}
```

### 🗃 Server‑Side Click Logging
Clicks are stored in `wp_postmeta`:

- No JavaScript required  
- Works even if GA4 is blocked  
- Click count displayed in the admin list table  

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

### 🛡 Compatible With:
- WordPress caching  
- Cloudflare  
- Bluehost shared hosting  
- SEO plugins (Yoast, RankMath)  
- Security plugins  

---

## 📦 Installation

1. Create a folder:

```
/wp-content/plugins/picklejuice-redirects/
```

2. Add the plugin file:

```
picklejuice-redirects.php
```

3. Paste the plugin code into the file.

4. Activate **Pickle Juice Redirect Manager** in WordPress → Plugins.

5. Go to **Redirects → Add New**.

---

## 🛠 Usage

### 1. Create a Redirect
- Go to **Redirects → Add New**
- Set the title (internal only)
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

If you want to mark redirect clicks as conversions:

1. GA4 → Admin → Events  
2. Find `outbound_click`  
3. Toggle **Mark as conversion**

---

## 🧱 Plugin Architecture

- **Custom Post Type:** `pj_redirect`
- **Rewrite Endpoint:** `/go/{slug}`
- **Click Logging:** `_pj_clicks`
- **Meta Fields:** `_pj_url`, `_pj_label`
- **Admin Columns:** Click count

---

## 📝 Changelog

### v1.0
- Initial release
- Redirect CPT
- GA4 event firing
- Server‑side click logging
- Admin UI
- `/go/{slug}` routing

---

## 🧑‍💻 Author

**Alias (Pickle Juice)**  
Clean, minimal, creator‑focused WordPress tools.

```
