# VALT Landing Page Fix + Entrepreneurship Sub-site Design

**Date:** 2026-05-01
**Status:** Approved

---

## Overview

Two scoped tasks:
1. Fix and visually redesign `public/landing.html`
2. Build out the VALT Entrepreneurship sub-site (`public/entrepreneurship/`) to mirror the Academy's structure with its own brand theme

The Academy site (`public/academy/`) is **not touched** except as a structural reference.

---

## Task 1 — Landing Page

### Baseline State
The current `landing.html` has two issues that both need addressing:
1. **Broken HTML:** Raw CSS and a duplicate HTML body block are injected inside the `<body>` after the `<!-- Content -->` comment. Remove this duplicate CSS/HTML text block, keeping only the single clean content wrapper below it.
2. **Visual redesign needed:** Even after the broken markup is removed, the background is a dark gradient (the `<video>` reference points to a missing file). The visual redesign below replaces this.

### Background Asset
No video file exists in the project. Use `img/banner-bg.jpg` as the CSS `background-image` on the container (`background-size: cover; background-position: center`). Remove the `<video>` element entirely.

### Visual Redesign

- **Background:** `img/banner-bg.jpg` as full-screen `background-image` (`background-size: cover; background-position: center`) on `.landing-container`. Place a separate overlay `<div>` with `position: absolute; inset: 0` using a `135deg` gradient (`rgba(10,25,41,0.75)` → `rgba(42,157,143,0.6)`) on top — this keeps the photo partially visible beneath the tint
- **Layout:** VALT logo (`img/logo02.png`) centered at top. "Choose Your Path" heading + subtitle. Two large side-by-side clickable panels filling the lower portion of the viewport (~45% width each with a gap).
- **Panel default state (always visible):**
  - Icon (Font Awesome): graduation cap for Academy, rocket for Entrepreneurship
  - Title text: "ACADEMY" / "ENTREPRENEURSHIP" in bold uppercase
  - Both panels visible and legible before hover
- **Panel hover state:**
  - Academy: teal overlay (`#2a9d8f`) fades in over the panel
  - Entrepreneurship: deep purple overlay (`#3B0F5E`) fades in over the panel
  - Short description text fades/slides in
  - Arrow (`→`) animates in — style at implementer's discretion
- **Typography:** Inter font (already imported)
- **Responsive:** Panels stack vertically on mobile (< 768px), each full width

### Panel Descriptions (shown on hover)
- **Academy:** "Empowering young adults and adults with lifelong skills in financial literacy, emotional intelligence, and entrepreneurship"
- **Entrepreneurship:** "Building innovative businesses and ventures with cutting-edge strategies and mentorship for aspiring entrepreneurs"

### Links
- Academy panel → `academy/index.html`
- Entrepreneurship panel → `entrepreneurship/index.html`

### Meta
- `<title>VALT - Choose Your Path</title>`
- Favicon: `img/logo02.png`
- OG tags: retain existing (og:type, og:title, og:description, og:image, og:url)
- Twitter card tags (complete set):
  - `<meta name="twitter:card" content="summary_large_image">`
  - `<meta name="twitter:title" content="VALT - Choose Your Path">`
  - `<meta name="twitter:description" content="Choose between Academy education and Entrepreneurship innovation">`
  - `<meta name="twitter:image" content="img/logo02.png">`

---

## Task 2 — Entrepreneurship Sub-site

### Pages
| File | Purpose |
|------|---------|
| `entrepreneurship/index.html` | Home — hero banner, 3 highlight programme cards |
| `entrepreneurship/about.html` | About — mission, vision, who we are |
| `entrepreneurship/courses.html` | Programmes — all 6 programme cards in a grid |
| `entrepreneurship/contact.html` | Contact — form + contact details |
| `entrepreneurship/entrepreneurship-theme.css` | Single theme file controlling all purple/gold overrides |

### Color Scheme
| Role | Value |
|------|-------|
| Primary | `#3B0F5E` (deep purple) |
| Accent | `#C9A84C` (gold) |
| Light background | `#FAF7F2` (cream) |
| Dark text | `#1a1a2e` |
| White text | `#ffffff` |

Brand reference: VALT Entrepreneurship brand guide — "V" with key logo, deep purple + gold.

### Navigation (all 4 pages)
`Home | About | Programmes | Contact`

- Purple (`#3B0F5E`) header background
- Gold (`#C9A84C`) hover and active link states
- Logo: `../img/logo02.png` with "Educational enrichment provider" tagline (matching Academy pattern)
- Gallery is **intentionally excluded** from the nav (out of scope)
- No Register button for now

### CSS & JS Strategy
**CSS** — load via `../css/` relative paths:
- `../css/linearicons.css`
- `../css/font-awesome.min.css` (FA4 — kept for existing icon usage in main.css)
- `https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css` (CDN — FA6 for all new icons specified in this spec, e.g. `fas fa-lightbulb`, `fas fa-chart-line`, `fas fa-piggy-bank`)
- `../css/bootstrap.css`
- `../css/magnific-popup.css`
- `../css/nice-select.css`
- `../css/animate.min.css`
- `../css/owl.carousel.css`
- `../css/jquery-ui.css`
- `../css/main.css`
- `entrepreneurship-theme.css` (local, replaces `valt-theme.css` — do NOT load valt-theme.css)

**JS** — mirror exactly the Academy index.html pattern (all pages):
- `../js/vendor/jquery-2.2.4.min.js`
- `https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js` (CDN)
- `../js/vendor/bootstrap.min.js`
- `../js/easing.min.js`
- `../js/hoverIntent.js`
- `../js/superfish.min.js`
- `../js/jquery.ajaxchimp.min.js`
- `../js/jquery.magnific-popup.min.js`
- `../js/jquery.tabs.min.js`
- `../js/jquery.nice-select.min.js`
- `../js/owl.carousel.min.js`
- `../js/mail-script.js`
- `../js/main.js`

No changes to `main.css` or `valt-theme.css`.

### Page Titles & Meta
| Page | Title |
|------|-------|
| index.html | `VALT Entrepreneurship - Build Your Future` |
| about.html | `About Us - VALT Entrepreneurship` |
| courses.html | `Programmes - VALT Entrepreneurship` |
| contact.html | `Contact Us - VALT Entrepreneurship` |

All pages: favicon `../img/logo02.png`. Include OG and Twitter card meta tags matching the Academy pattern, adapted for Entrepreneurship content.

### Page Content

**index.html — Home**
- Hero: full-width purple banner (`#3B0F5E`), logo + "VALT Entrepreneurship" label, tagline ("Build Your Future"), gold CTA button linking to `courses.html`
- Intro paragraph: "VALT Entrepreneurship supports aspiring entrepreneurs in Durban, KZN, South Africa with cutting-edge strategies and mentorship"
- 3 highlight cards with icons, titles, and descriptions:

| Icon | Title | Description |
|------|-------|-------------|
| `fa-lightbulb` | Ideation & Validation | Transform your ideas into viable business concepts through market research and validation methodologies. |
| `fa-rocket` | Startup Acceleration | Fast-track your startup with our intensive accelerator program, mentorship, and funding opportunities. |
| `fa-chart-line` | Growth Strategy | Scale your business with proven growth strategies, digital marketing, and operational excellence. |

**about.html — About**
- Purple hero banner with "About Us" title + breadcrumb (`Home → About Us`)
- Mission: empower aspiring entrepreneurs with the tools, knowledge, and network to build successful businesses
- Vision: a thriving entrepreneurial ecosystem across South Africa
- "Who We Are" paragraph: VALT founded 2019, Durban KZN, South Africa
- Same layout pattern as `academy/about.html`

**courses.html — Programmes**
- Purple hero banner with "Our Programmes" title + breadcrumb (`Home → Programmes`)
- 6 programme cards in a 3-column grid (md: 2-col, sm: 1-col):

| Icon | Title | Description |
|------|-------|-------------|
| `fa-lightbulb` | Ideation & Validation | Transform your ideas into viable business concepts through market research and validation methodologies. |
| `fa-rocket` | Startup Acceleration | Fast-track your startup with our intensive accelerator program, mentorship, and funding opportunities. |
| `fa-chart-line` | Growth Strategy | Scale your business with proven growth strategies, digital marketing, and operational excellence. |
| `fa-users` | Network & Community | Join a thriving community of entrepreneurs, investors, and industry experts dedicated to your success. |
| `fa-piggy-bank` | Funding & Investment | Access funding opportunities, pitch competitions, and connect with potential investors. |
| `fa-certificate` | Business Certification | Earn recognised certifications in entrepreneurship, business management, and innovation. |

- Cards: white/cream background, purple icon, gold hover border

**contact.html — Contact**
- Purple hero banner with "Contact Us" title + breadcrumb (`Home → Contact Us`)
- Left column: address (Durban, KZN / South Africa), WhatsApp (061 461 0828 → `https://wa.me/27614610828`), email (`info@valt.co.za`)
- Right column: contact form with fields: Name, Email, Subject, Message, Submit button
- Form action: `../mail.php` method `POST` — note this differs from `academy/contact.html` which uses `mail.php` (broken path from sub-directory); `../mail.php` is correct for the entrepreneurship sub-directory

### Out of Scope
- Gallery page
- Register functionality (planned later — age-based registration for Academy is a separate task)
- Academy registration form changes (separate task)

---

## Files Changed

| File | Action |
|------|--------|
| `public/landing.html` | Fix broken HTML + visual redesign |
| `public/entrepreneurship/index.html` | Rebuild (was placeholder) |
| `public/entrepreneurship/about.html` | Create new |
| `public/entrepreneurship/courses.html` | Create new |
| `public/entrepreneurship/contact.html` | Create new |
| `public/entrepreneurship/entrepreneurship-theme.css` | Create new |

Academy files: **no changes.**
