## PerfumeStore — PHP E‑Commerce (Hostinger Ready)

A production-ready PHP 8.x + MySQL storefront for an Indian luxury solid perfume & attar brand. No frameworks or Node build steps. Admin panel included. Secure, responsive, and SEO-friendly.

### 1) Features (high-level)
- Storefront: Home, Shop, Product, Cart, Checkout (Razorpay server order stub), Account (Login/Register/Orders), Scent Quiz, Contact, Search, Policies
- Admin: Login, Dashboard, Products CRUD (image upload), Categories, Orders (status), Customers, Testimonials moderation, Newsletter list, Settings (site, tax, shipping, SMTP, Razorpay)
- Security: PDO prepared statements, CSRF tokens, password_hash, input sanitization, CSP headers, uploads protected via `.htaccess`
- Performance: Lazy images, minimal CSS/JS, preloaded hero, service worker caching
- Accessibility: Keyboard navigation, focus-visible, ARIA where relevant
- SEO: Meta, OG, JSON-LD, sitemap generator

### 2) Requirements
- PHP 8.0+ with extensions: pdo_mysql, mbstring, openssl, json, curl, fileinfo
- MySQL/MariaDB
- Hostinger shared hosting compatible

### 3) Deploy on Hostinger
1. Create a MySQL database (hPanel > Databases). Note DB name, user, pass, host.
2. Upload all files to `public_html` (or subfolder) via File Manager or FTP.
3. Create the database tables:
   - Open phpMyAdmin (hPanel), select your DB.
   - Import `db/schema.sql`.
4. Configure app:
   - Open `includes/config.php` and set `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS`, `SITE_URL`, `ADMIN_EMAIL`.
   - Set `RAZORPAY_KEY_ID`, `RAZORPAY_SECRET` (or configure in Admin > Settings).
   - Set `SMTP_HOST`, `SMTP_USER`, `SMTP_PASS`, `SMTP_PORT` (Hostinger example: `smtp.hostinger.com`, port 587).
5. Permissions:
   - Ensure `uploads/` is `755`. `uploads/.htaccess` blocks script execution.
   - Ensure `logs/` exists (app tries to create) and is not web-accessible (`700` recommended).
6. SSL:
   - Enable free SSL in Hostinger (required for service worker & Razorpay). Update `SITE_URL` to `https://...`.
7. Test Razorpay:
   - Use test keys, set webhook in Razorpay dashboard pointing to `https://yourdomain.com/razorpay-webhook.php`.
8. Cron suggestions:
   - Weekly: `php /home/username/public_html/sitemap-generator.php > /dev/null 2>&1`
   - Daily: temp cleanup script (if added).
9. Optional:
   - Upload `PHPMailer` library to `includes/PHPMailer` (if you prefer over `mail()`) or use Composer locally and upload `vendor/`.

### 4) Default Admin
- Email: `admin@yourdomain.com`
- Password: `tempPass123!`
- Note: `db/schema.sql` seeds admin with empty password and the app hashes the default on first run. Change immediately after login (Admin > Customers or Settings).

### 5) Security Notes
- All POST forms include hidden `_csrf`. Keep sessions secure and site on HTTPS.
- Rate limiting: consider enabling fail2ban at server or add a simple login attempt counter (not included by default).
- CSP added in `includes/config.php`. Adjust if you add CDNs.
- Uploads restricted to images with MIME checks and 2MB max.

### 6) Payment & Email
- Razorpay:
  - Server-side order created via `create_razorpay_order()` using cURL auth with Key ID/Secret.
  - Webhook endpoint `razorpay-webhook.php` verifies HMAC SHA256 and updates order status.
- Email:
  - `send_email()` uses PHPMailer if present at `includes/PHPMailer/`, otherwise falls back to `mail()`.
  - Hostinger SMTP example: host `smtp.hostinger.com`, port `587`, TLS, sender `no-reply@yourdomain.com`.

### 7) PWA
- `manifest.json` with icons; `service-worker.js` pre-caches CSS/JS and runtime caches images.
- Registration is in `assets/js/main.js`. Requires HTTPS.

### 8) SEO
- JSON-LD on homepage for product list, OG tags, canonical.
- `sitemap-generator.php` builds `sitemap.xml` from DB.

### 9) Post-install Test Plan
1. Visit home page and confirm it loads without errors.
2. Register a new customer; login and logout.
3. Add multiple items to cart; update quantities.
4. Proceed to checkout; ensure server creates order and returns Razorpay order id.
5. Simulate payment using Razorpay test mode; verify webhook updates status to `paid`.
6. Check `account/orders.php` shows the new order with correct status and items.
7. Admin login with default credentials; change admin password.
8. Create a product with image; verify upload and listing on `shop.php`.
9. Approve a testimonial; verify it shows on homepage carousel.
10. Run `sitemap-generator.php` and verify `sitemap.xml` exists and is accessible.

### 10) QA Checklist
- Registration/login success and error cases
- Add to cart from product list and detail pages
- Cart quantity updates, totals (tax, shipping)
- Checkout flow: order create, Razorpay test payment, webhook status update
- Admin: Product CRUD (create/edit/delete), category assign, image upload validations
- Orders: list, view, status transitions; CSV export
- Newsletter: subscription and welcome email
- Contact form: sends email and stores message (if implemented)
- Sitemap generation and robots access
- Mobile layout: header, grid, hero, carousel; 44px touch targets
- Lighthouse: performance and accessibility; consider minifying CSS/JS

### 11) Config Variables Reference
- DB_HOST, DB_NAME, DB_USER, DB_PASS
- SITE_URL, ADMIN_EMAIL
- RAZORPAY_KEY_ID, RAZORPAY_SECRET
- SMTP_HOST, SMTP_USER, SMTP_PASS, SMTP_PORT

### 12) Notes
- This project is framework-free vanilla PHP with minimal CSS/JS. Extend as needed.
- Use WebP images for performance; provide JPEG/PNG fallbacks if required.
