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

### Problem
`landing.html` has a broken HTML structure: raw CSS and a duplicate HTML body are embedded inside the `<body>` tag (lines 330–395), causing invalid markup. The page renders because the second copy of the content (lines 396–469) is what displays.

### Fix
Remove lines 330–395 (the raw CSS/HTML text inside the body), leaving only the single clean content wrapper.

### Visual Redesign
The current design has small glass-morphism cards at low contrast. The redesign:

- **Background:** Full-screen video (`img/background-video.mp4`) at higher opacity (~0.4) with a dark gradient overlay for readability. Static image fallback if video fails.
- **Layout:** VALT logo centered at top. "Choose Your Path" heading + subtitle. Two large side-by-side clickable panels occupying the lower half of the viewport.
- **Panels:** Wide prominent blocks (not small cards). On hover:
  - Academy panel: teal overlay (`#2a9d8f`)
  - Entrepreneurship panel: deep purple overlay (`#3B0F5E`)
  - Each shows a label + animated arrow on hover
- **Typography:** Modern, clean — Inter font (already in use)
- **Responsive:** Panels stack vertically on mobile

### Links
- Academy panel → `academy/index.html`
- Entrepreneurship panel → `entrepreneurship/index.html`

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
- VALT logo (existing `../img/logo02.png`) in header
- No Register button for now

### CSS Strategy
- Reuse Academy's existing CSS stack: `bootstrap.css`, `font-awesome.min.css`, `main.css`, `linearicons.css`, etc. via `../css/` relative paths
- Add one new file: `entrepreneurship/entrepreneurship-theme.css` for all purple/gold overrides
- No changes to `main.css` or `valt-theme.css`

### Page Content

**index.html — Home**
- Hero: full-width purple banner, VALT Entrepreneurship logo, tagline ("Build Your Future"), gold CTA button
- Below hero: 3 highlight cards — Ideation & Validation, Startup Acceleration, Growth Strategy
- Brief intro paragraph about VALT Entrepreneurship

**about.html — About**
- Purple hero banner with page title
- Mission + Vision sections
- "Who We Are" paragraph (VALT founded 2019, South Africa)
- Same layout pattern as `academy/about.html`

**courses.html — Programmes**
- Purple hero banner
- 6 programme cards in a 3-column grid (md: 2-col, sm: 1-col):
  1. Ideation & Validation
  2. Startup Acceleration
  3. Growth Strategy
  4. Network & Community
  5. Funding & Investment
  6. Business Certification
- Cards: white background, purple icon, gold hover border

**contact.html — Contact**
- Purple hero banner
- Contact form (Name, Email, Message, Submit)
- Contact details section
- Same layout as `academy/contact.html`

### Out of Scope
- Gallery page (not built yet)
- Register functionality (planned later — will tie into age-based registration for Academy)
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
