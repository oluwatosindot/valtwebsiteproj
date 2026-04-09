# VALT Website — Upgrade Roadmap & Status

**Last updated:** 3 March 2026
**Project:** C:\xampp\htdocs\valtwebsite

---

## WHAT'S BEEN DONE (Completed)

### Branding & Visual
- [x] Removed all Colorlib branding/attribution from all pages
- [x] Replaced text logo with `logoimage.png` (65px) across all 11 pages
- [x] Updated favicons to `valtlogo.png` across all pages
- [x] Fixed WhatsApp icon alignment on contact page
- [x] Navbar blended with logo navy (#0a2342) background
- [x] Full restyle: orange (#f7631b) replaced with teal (#2a9d8f)
- [x] Backgrounds changed to warm off-white (#f8f9fa)
- [x] Header/footer unified to navy (#0a2342)
- [x] Modern v2 style applied: gradient buttons, rounded cards, hover effects, smooth transitions
- [x] Brighter typography (body #444, paragraphs #555, headings #1a1a2e)
- [x] Text visibility fixes for text over images/dark backgrounds
- [x] SCSS source files updated (_variables, _header, _footer, _home)
- [x] Consolidated inline styles into single `css/valt-theme.css` (no more duplicated style blocks in 11 files)

### SEO & Social
- [x] SEO meta tags (title, description, keywords) on all pages
- [x] Open Graph meta tags (og:title, og:description, og:image, og:url) on all pages
- [x] Twitter Card meta tags on all pages

### Backend & Security
- [x] Node.js/Express backend with API endpoints (contact, enquiry, newsletter)
- [x] Database schema for contacts, enquiries, newsletter subscribers
- [x] Rate limiting on all API endpoints (express-rate-limit)
- [x] Input sanitization — all form inputs HTML-escaped before DB storage and email
- [x] Email validation on all form endpoints
- [x] Auto-reply confirmation emails for contact and enquiry submissions
- [x] Deleted insecure `mail.php` (legacy PHP mailer)
- [x] Deleted unused `Education - Doc/` folder

### Accessibility & Forms
- [x] Added `aria-label` attributes to contact form fields
- [x] Added `aria-label` attributes to enquiry form fields
- [x] Added `form-handlers.js` script to `contact.html` (was missing)

### Build Process
- [x] Added SCSS compilation scripts to `package.json` (`npm run scss`, `npm run scss:watch`, `npm run build`)
- [x] Added `express-rate-limit` dependency

---

## CONTACT FORM — EMAIL STATUS

### Current Setup

| Component | File | Status |
|-----------|------|--------|
| **Node.js server** | `server.js` | PRIMARY — handles `/api/contact`, `/api/enquiry`, `/api/newsletter` |
| **Legacy PHP mailer** | `mail.php` | DELETED |

### How It Works Now
1. User fills out contact form on `contact.html`
2. `js/mail-script.js` intercepts form submit via AJAX
3. Sends POST to `/api/contact` (Node.js endpoint)
4. `server.js` validates + sanitizes input, saves to MySQL `contacts` table, sends email via Nodemailer
5. Auto-reply confirmation email sent to the submitter

### CRITICAL: Email Won't Send Until You Configure .env

The `.env` file currently has **placeholder SMTP credentials**:
```
SMTP_USER=your-email@gmail.com        <-- REPLACE with real email
SMTP_PASSWORD=your-app-password        <-- REPLACE with Gmail App Password
SMTP_FROM_EMAIL=your-email@gmail.com   <-- REPLACE with real email
```

**To make email work:**

1. **Option A — Gmail SMTP (easiest)**
   - Go to Google Account > Security > 2-Step Verification > App Passwords
   - Generate an app password for "Mail"
   - Update `.env`:
     ```
     SMTP_HOST=smtp.gmail.com
     SMTP_PORT=587
     SMTP_SECURE=false
     SMTP_USER=info@valt-fq.co.za    (or a Gmail address)
     SMTP_PASSWORD=xxxx-xxxx-xxxx-xxxx  (the app password)
     SMTP_FROM_NAME=VALT Academy
     SMTP_FROM_EMAIL=info@valt-fq.co.za
     CONTACT_EMAIL=info@valt-fq.co.za
     ENQUIRY_EMAIL=info@valt-fq.co.za
     ```

2. **Option B — Domain email SMTP**
   - If `info@valt-fq.co.za` is hosted (e.g., on cPanel, Zoho, Microsoft 365):
     ```
     SMTP_HOST=mail.valt-fq.co.za   (or your provider's SMTP host)
     SMTP_PORT=587
     SMTP_USER=info@valt-fq.co.za
     SMTP_PASSWORD=your-email-password
     ```

3. **Start the server:**
   ```bash
   npm start          # or: node server.js
   ```

4. **Create the database:**
   ```bash
   mysql -u root < db/schema.sql
   ```

### IMPORTANT: Running on XAMPP vs Node.js

- If you serve the site through **XAMPP/Apache** (http://localhost/valtwebsite/), the AJAX calls to `/api/contact` will FAIL because Apache doesn't run Node.js
- You need to either:
  - **Run `node server.js`** and access site at `http://localhost:3000`
  - OR set up Apache as a reverse proxy to forward `/api/*` to Node.js on port 3000

---

## THINGS LEFT TO DO

### High Priority (Before Going Live)

1. **Configure real SMTP credentials in `.env`** — Without this, no emails are sent from the contact form. Form submissions are still saved to the database though.

2. **Create MySQL database** — Run `db/schema.sql` in phpMyAdmin or MySQL CLI to create the `valt_website` database and tables.

3. **Test the full contact flow** — After configuring .env and database, submit a test form and verify email arrives at `info@valt-fq.co.za`.

4. **Set a real database password** — Currently `DB_PASSWORD=` is empty.

5. **Set `NODE_ENV=production`** in `.env` when deploying to live server.

### Medium Priority

6. **Placeholder content on blog/event pages** — Blog and event pages still have template placeholder text (lorem ipsum style content, stock author names like "Mark Wiens", dates from 2017). Replace with real VALT content or remove these pages from navigation until ready.

7. **Social media links unverified** — The header links (facebook.com/valt, twitter.com/valt, instagram.com/valt, linkedin.com/company/valt) may not point to real VALT profiles. Update with actual social media URLs.

8. **`VALT/` subfolder** — Contains a separate coming-soon page and 2 PDF company profiles (5.8MB total). Decide if this should stay or be moved elsewhere.

### Low Priority

9. **Unused images** — 8 images not referenced anywhere (~348KB): `b4.jpg`, `blog/c6.jpg`, `elements/a.jpg`, `elements/a2.jpg`, `elements/user1.png`, `elements/user2.png`, `logo.jpeg`, `search-bg.jpg`. Can be deleted to clean up.

---

## FUTURE UPGRADES

### Frontend

| # | Upgrade | Description | Effort |
|---|---------|-------------|--------|
| 1 | **Upgrade jQuery** | Currently using jQuery 2.2.4 (2016). Upgrade to jQuery 3.7+ for security patches and modern browser support. | Small |
| 2 | **Optimize images** | `logoimage.png` is 166KB, `banner-bg.jpg` is 388KB. Compress images or convert to WebP. Could save 50%+ file size. Add responsive `srcset` for different screen sizes. | Small |
| 3 | **Structured data (Schema.org)** | Add JSON-LD markup for Organization, Course, and Event types. Helps Google show rich search results. | Medium |
| 4 | **Add loading animations** | Skeleton screens or fade-in effects for content sections as user scrolls. The CSS transitions are already in place. | Small |
| 5 | **Dark mode toggle** | The navy/teal palette is already close. A CSS toggle for dark mode could be added with CSS variables. | Medium |
| 6 | **Cookie consent banner** | Required for POPIA (South Africa) compliance if using analytics or tracking. | Small |

### Backend

| # | Upgrade | Description | Effort |
|---|---------|-------------|--------|
| 7 | **CSRF protection** | Add CSRF tokens to forms to prevent cross-site request forgery. | Medium |
| 8 | **Admin dashboard** | Build a simple admin page to view contact submissions, enquiries, and newsletter subscribers from the database. | Large |
| 9 | **Newsletter integration** | Connect newsletter signup to Mailchimp, SendGrid, or similar service for actual email campaigns. Currently it just saves emails to the database. | Medium |
| 10 | **File upload for enquiries** | Allow programme enquiry form to accept document uploads (e.g., school ID). | Medium |
| 11 | **WhatsApp Business API** | `.env` already has placeholder fields for WhatsApp API. Could add automated WhatsApp notifications for new enquiries. | Medium |

### Deployment / Hosting

| # | Upgrade | Description | Effort |
|---|---------|-------------|--------|
| 12 | **Move off XAMPP** | For production, deploy to a proper hosting service (e.g., Vercel, Railway, DigitalOcean, or a VPS with PM2). XAMPP is for local development only. | Medium |
| 13 | **SSL certificate** | Set up HTTPS with Let's Encrypt or through your hosting provider. Required for secure form submissions. | Small |
| 14 | **Domain setup** | Point valt-fq.co.za (or chosen domain) to the hosting server. Configure DNS records. | Small |
| 15 | **PM2 process manager** | Use PM2 to keep `server.js` running permanently and auto-restart on crashes. | Small |
| 16 | **Backup strategy** | Set up automated MySQL database backups and file backups. | Small |
| 17 | **CI/CD pipeline** | Set up GitHub Actions or similar to auto-deploy when code is pushed. | Medium |

### Content

| # | Upgrade | Description | Effort |
|---|---------|-------------|--------|
| 18 | **Real blog content** | Replace placeholder blog posts with actual VALT articles, news, or educational content. | Medium |
| 19 | **Real event content** | Replace placeholder events with actual VALT workshops, seminars, dates. | Medium |
| 20 | **Testimonials** | Add real student/parent testimonials to the review section on the home page. | Small |
| 21 | **Gallery images** | Replace stock gallery images with real photos from VALT sessions and events. | Small |
| 22 | **Terms & Privacy pages** | Add Terms of Service and Privacy Policy pages (POPIA requirement). | Medium |

---

## FILE REFERENCE

```
valtwebsite/
├── index.html              Home page
├── about.html              About VALT
├── courses.html            Programmes listing
├── course-details.html     Programme detail page
├── contact.html            Contact form page
├── events.html             Events listing
├── event-details.html      Event detail page
├── gallery.html            Photo gallery
├── blog-home.html          Blog listing
├── blog-single.html        Blog post detail
├── elements.html           UI elements demo page
├── server.js               Express backend (port 3000)
├── package.json            Node.js dependencies
├── .env                    Environment config (SMTP, DB)
├── .env.example            Config template
├── config/database.js      MySQL connection pool
├── db/schema.sql           Database tables
├── js/mail-script.js       Contact form AJAX handler
├── js/form-handlers.js     Enquiry + newsletter AJAX
├── js/main.js              Theme JavaScript
├── css/main.css            Compiled theme CSS (minified)
├── css/valt-theme.css      VALT custom theme overrides (single source of truth)
├── scss/                   SCSS source (compile with: npm run scss)
├── img/                    Images (~4MB)
├── fonts/                  FontAwesome + Linearicons (1.5MB)
└── VALT/                   Coming-soon page + PDF company profiles
```

---

## QUICK START (When You Return)

1. Open terminal in `C:\xampp\htdocs\valtwebsite`
2. Run `npm install` (if node_modules is missing)
3. Edit `.env` with real SMTP credentials (see "Contact Form" section above)
4. Import database: open phpMyAdmin > run `db/schema.sql`
5. Start server: `npm start`
6. Open `http://localhost:3000` in browser
7. Test the contact form — submit and check email arrives
8. To edit styles: modify `css/valt-theme.css` (changes apply to all pages instantly)
9. To compile SCSS: `npm run scss:watch` (auto-recompiles on save)
