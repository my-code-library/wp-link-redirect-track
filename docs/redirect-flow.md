# Redirect Flow

1. User visits `/go/{slug}`
2. Plugin resolves slug → redirect post
3. Server logs click
4. Page outputs GA4 event script
5. 150ms delay
6. Browser redirects to destination URL
