# VALT Website — Page Completion Roadmap

**Last updated:** 2026-04-09
**Status key:** ✅ Done | 🔄 In Progress | ⬜ Pending

---

## Phase 1 — Core Pages

### 1. Home `index.html` 🔄
- ✅ Hero banner with headerbg01.jpg
- ✅ Feature cards (3 pillars)
- ✅ Programme carousel (VALT 101–106)
- ✅ Partner With VALT CTA
- ✅ Testimonials section
- ✅ CTA bottom bar
- ⬜ Review hero text and tagline
- ⬜ Confirm all links are correct

### 2. About `about.html` 🔄
- ✅ Feature cards (3 pillars)
- ✅ Who We Are section (with Read More toggle)
- ✅ Programme Impact accordion
- ✅ Why Choose VALT section
- ✅ Why VALT cards (3 blog cards)
- ✅ Enquiry form (wired to /api/enquiry)
- ✅ CTA section removed
- ⬜ Add Founders/Team section
- ⬜ Add partner school logos

### 3. Programmes `courses.html` 🔄
- ✅ Financial Stats intro section
- ✅ Programme cards (VALT 101–106 + Reflective Learning)
- ✅ Enquiry form
- ✅ CTA section removed
- ⬜ Link each card to its own detail page
- ⬜ Add programme comparison table

### 4. Course Details `course-details.html` ⬜
- ⬜ Create individual layout for each programme
- ⬜ Programme overview, topics, outcomes
- ⬜ Who it's for (grade/age group)
- ⬜ Enquiry form
- ⬜ Related programmes section

### 5. Gallery `gallery.html` ⬜
- ✅ Basic grid (7 images)
- ✅ Unique banner background (headerbg02.jpg)
- ✅ CTA section removed
- ⬜ Replace stock images with real VALT photos
- ⬜ Add image categories/filter tabs
- ⬜ Add alt text to all images

### 6. Contact `contact.html` ⬜
- ✅ Contact form (wired to /api/contact)
- ✅ Address, phone, WhatsApp, email details
- ✅ Unique banner background (cta-bg.jpg)
- ⬜ Add Google Maps embed
- ⬜ Add office hours
- ⬜ Test form end-to-end (needs .env configured on server)

---

## Phase 2 — Supporting Pages

### 7. Events `events.html` ⬜
- ⬜ Decide: keep or remove from nav
- ⬜ Replace all placeholder content with real VALT events
- ⬜ Add dates, venues, registration links

### 8. Blog `blog-home.html` + `blog-single.html` ⬜
- ⬜ Decide: keep or remove from nav
- ⬜ Replace placeholder posts with real VALT articles
- ⬜ Categories: Financial Literacy, Entrepreneurship, EQ, News

---

## Phase 3 — Site-wide Features

| # | Feature | Status | Notes |
|---|---------|--------|-------|
| A | Contact form live | ⬜ | Configure .env SMTP on Afrihost |
| B | Enquiry form live | ⬜ | Same — needs Node.js running |
| C | Social media links | ⬜ | Update placeholder URLs to real VALT profiles |
| D | Image optimisation | ⬜ | Compress banner-bg, logoimage (save ~50% size) |
| E | Terms & Privacy pages | ⬜ | POPIA requirement |
| F | Cookie consent banner | ⬜ | POPIA compliance |
| G | Google Analytics | ⬜ | Add tracking once site is live |
| H | Schema.org markup | ⬜ | Helps Google rich results |
| I | Node.js on Afrihost | ⬜ | Check if Node.js Selector available in cPanel |

---

## Deployment Workflow (Every Change)
1. Make changes locally (Claude Code)
2. `git push` → github.com/oluwatosindot/valtwebsiteproj
3. cPanel → Git Version Control → **Deploy HEAD Commit**
4. Check live at www.valt.co.za

---

## Colour Reference
| Use | Hex |
|-----|-----|
| Logo / Header / Footer | `#00455A` |
| Teal accent / Buttons | `#2a9d8f` |
| Star / Highlight | `#f0b429` |
| Body text | `#444` |
| Paragraph text | `#555` |
| Background (light sections) | `#f8f9fa` |
