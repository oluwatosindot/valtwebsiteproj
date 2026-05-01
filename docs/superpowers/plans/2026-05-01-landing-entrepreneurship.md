# Landing Page Redesign + Entrepreneurship Sub-site Implementation Plan

> **For agentic workers:** REQUIRED: Use superpowers:subagent-driven-development (if subagents available) or superpowers:executing-plans to implement this plan. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Fix and redesign `landing.html` and build the VALT Entrepreneurship sub-site (4 pages + theme CSS) mirroring the Academy structure with a purple/gold brand.

**Architecture:** landing.html is a standalone file in `public/`. The entrepreneurship sub-site lives in `public/entrepreneurship/` and loads shared assets from `../css/` and `../js/`. A single `entrepreneurship-theme.css` overrides the teal academy colors to purple `#3B0F5E` and gold `#C9A84C`. The Academy files are never touched.

**Tech Stack:** HTML5, CSS3, Bootstrap 3 (via `../css/bootstrap.css`), Font Awesome 4 (local) + Font Awesome 6 (CDN), jQuery 2 (local vendor), Poppins font (Google Fonts), Inter font (Google Fonts for landing page only)

**Spec:** `docs/superpowers/specs/2026-05-01-landing-entrepreneurship-design.md`

---

## Chunk 1: Fix and Redesign landing.html

**Files:**
- Modify: `public/landing.html`

---

### Task 1: Remove the broken duplicate HTML from the body

The current `landing.html` has raw CSS text and a second `</style></head><body>` block injected inside the `<body>` after the `<!-- Content -->` comment. This makes the markup invalid.

- [ ] **Step 1: Open `public/landing.html` and locate the broken section**

  Find the `<!-- Content -->` comment inside the `<body>`. Immediately after it, there is a block of raw CSS text (starting around `to {` and `opacity: 1;`) followed by a duplicate `</style></head><body>` structure. The actual real content begins with `<div class="content-wrapper">` inside a nested `<body>` tag.

- [ ] **Step 2: Delete the broken block**

  Remove everything between `<!-- Content -->` and the real `<div class="content-wrapper">` — that is, delete the raw CSS text, the stray `</style>`, `</head>`, `<body>`, and the second `<div class="landing-container">` / `<video>` / `<div class="video-overlay">` that appear inside the body.

  After the fix, the body should look like this (clean, no duplication):

  ```html
  <body>
      <div class="landing-container">
          <!-- overlay div will be added in Task 2 -->
          <!-- Content -->
          <div class="content-wrapper">
              ... (the real content)
          </div>
      </div>
      <!-- scripts -->
  </body>
  ```

- [ ] **Step 3: Verify the HTML is valid**

  Open the file in a browser. The page should render without layout breakage. Check browser DevTools (F12 → Elements) — there should be only one `<body>` and one `.landing-container`.

- [ ] **Step 4: Commit**

  ```bash
  git add public/landing.html
  git commit -m "fix: remove duplicate CSS/HTML block injected inside landing.html body"
  ```

---

### Task 2: Redesign landing.html — background image + overlay

Replace the dark CSS gradient background (and missing video reference) with `banner-bg.jpg` + a gradient overlay `<div>`.

- [ ] **Step 1: Update the `<style>` block — `.landing-container` and overlay**

  Find the `.landing-container` rule in the `<style>` block and replace it:

  ```css
  .landing-container {
      position: relative;
      height: 100vh;
      overflow: hidden;
      background-image: url('img/banner-bg.jpg');
      background-size: cover;
      background-position: center;
  }

  .video-overlay {
      position: absolute;
      inset: 0;
      background: linear-gradient(135deg, rgba(10,25,41,0.78) 0%, rgba(42,157,143,0.62) 100%);
      z-index: 1;
  }
  ```

  Remove the old `.video-background` rule entirely (the `<video>` element is being removed).

- [ ] **Step 2: Update the HTML body — remove `<video>`, keep overlay `<div>`**

  In the body, inside `.landing-container`, remove the `<video>` element. Keep or add the overlay div:

  ```html
  <div class="landing-container">
      <div class="video-overlay"></div>
      <div class="content-wrapper">
          ...
      </div>
  </div>
  ```

- [ ] **Step 3: Verify in browser**

  `img/banner-bg.jpg` should now be visible as the background with a dark teal-navy gradient tint over it. Text should be clearly readable.

- [ ] **Step 4: Commit**

  ```bash
  git add public/landing.html
  git commit -m "feat: replace missing video background with banner-bg.jpg image on landing page"
  ```

---

### Task 3: Redesign landing.html — two large side-by-side panels

Replace the small `.choice-card` elements with full-width `.choice-panel` blocks.

- [ ] **Step 1: Replace the choice card CSS in the `<style>` block**

  Remove the old `.choice-card`, `.choice-card::before`, `.choice-card:hover`, `.choice-icon`, `.choice-title`, `.choice-description`, `.choice-arrow` rules and replace with:

  ```css
  .choice-section {
      animation: fadeInUp 1.2s ease-out 0.6s both;
      width: 100%;
      max-width: 860px;
  }

  .choice-intro {
      color: rgba(255, 255, 255, 0.7);
      font-size: 0.85rem;
      font-weight: 500;
      text-transform: uppercase;
      letter-spacing: 0.15em;
      margin-bottom: 24px;
  }

  .choice-panels {
      display: flex;
      gap: 20px;
      width: 100%;
  }

  .choice-panel {
      flex: 1;
      position: relative;
      overflow: hidden;
      border-radius: 20px;
      padding: 52px 36px;
      cursor: pointer;
      text-decoration: none;
      color: white;
      background: rgba(255, 255, 255, 0.07);
      border: 1px solid rgba(255, 255, 255, 0.15);
      transition: border-color 0.4s ease, box-shadow 0.4s ease;
      display: flex;
      flex-direction: column;
      align-items: center;
      text-align: center;
  }

  .choice-panel::before {
      content: '';
      position: absolute;
      inset: 0;
      opacity: 0;
      transition: opacity 0.4s ease;
      border-radius: 20px;
  }

  .choice-panel.academy::before {
      background: rgba(42, 157, 143, 0.82);
  }

  .choice-panel.entrepreneurship::before {
      background: rgba(59, 15, 94, 0.88);
  }

  .choice-panel:hover {
      border-color: rgba(255, 255, 255, 0.3);
      box-shadow: 0 24px 60px rgba(0, 0, 0, 0.35);
      text-decoration: none;
      color: white;
  }

  .choice-panel:hover::before {
      opacity: 1;
  }

  .panel-content {
      position: relative;
      z-index: 1;
  }

  .panel-icon {
      font-size: 2.8rem;
      color: #f0b429;
      margin-bottom: 18px;
      display: block;
      transition: transform 0.3s ease;
  }

  .choice-panel:hover .panel-icon {
      transform: scale(1.12);
  }

  .panel-title {
      font-size: 1.35rem;
      font-weight: 700;
      letter-spacing: 0.06em;
      text-transform: uppercase;
      margin-bottom: 0;
      color: white;
  }

  .panel-description {
      font-size: 0.9rem;
      color: rgba(255, 255, 255, 0.92);
      line-height: 1.65;
      max-height: 0;
      overflow: hidden;
      opacity: 0;
      transition: max-height 0.4s ease 0.05s, opacity 0.35s ease 0.1s, margin-top 0.3s ease;
      margin-top: 0;
  }

  .choice-panel:hover .panel-description {
      max-height: 160px;
      opacity: 1;
      margin-top: 14px;
  }

  .panel-arrow {
      display: inline-block;
      color: #f0b429;
      font-size: 1.4rem;
      opacity: 0;
      transform: translateX(-8px);
      transition: opacity 0.3s ease 0.18s, transform 0.3s ease 0.18s;
      margin-top: 10px;
  }

  .choice-panel:hover .panel-arrow {
      opacity: 1;
      transform: translateX(0);
  }

  @media (max-width: 768px) {
      .choice-panels {
          flex-direction: column;
          gap: 16px;
      }

      .choice-panel {
          padding: 40px 28px;
      }

      .panel-description {
          max-height: 160px;
          opacity: 1;
          margin-top: 12px;
      }
  }
  ```

- [ ] **Step 2: Replace the HTML choice buttons with panels**

  Find the `<div class="choice-buttons">` block and replace it entirely:

  ```html
  <div class="choice-panels">
      <a href="academy/index.html" class="choice-panel academy">
          <div class="panel-content">
              <span class="panel-icon"><i class="fas fa-graduation-cap"></i></span>
              <h2 class="panel-title">Academy</h2>
              <p class="panel-description">Empowering young adults and adults with lifelong skills in financial literacy, emotional intelligence, and entrepreneurship</p>
              <span class="panel-arrow">&#8594;</span>
          </div>
      </a>
      <a href="entrepreneurship/index.html" class="choice-panel entrepreneurship">
          <div class="panel-content">
              <span class="panel-icon"><i class="fas fa-rocket"></i></span>
              <h2 class="panel-title">Entrepreneurship</h2>
              <p class="panel-description">Building innovative businesses and ventures with cutting-edge strategies and mentorship for aspiring entrepreneurs</p>
              <span class="panel-arrow">&#8594;</span>
          </div>
      </a>
  </div>
  ```

  Also update the wrapping `<div class="choice-buttons">` class to `<div class="choice-panels">` (or remove the outer wrapper if it exists separately).

- [ ] **Step 3: Also fix the Twitter card meta tags**

  In the `<head>`, ensure the full set of Twitter card tags is present:

  ```html
  <meta name="twitter:card" content="summary_large_image">
  <meta name="twitter:title" content="VALT - Choose Your Path">
  <meta name="twitter:description" content="Choose between Academy education and Entrepreneurship innovation">
  <meta name="twitter:image" content="img/logo02.png">
  ```

- [ ] **Step 4: Verify in browser**

  - Two large panels appear side by side
  - Background photo is visible through the gradient overlay
  - Hover on Academy panel: teal overlay fades in, description and arrow appear
  - Hover on Entrepreneurship panel: purple overlay fades in, description and arrow appear
  - On mobile (DevTools responsive mode, < 768px): panels stack vertically

- [ ] **Step 5: Commit**

  ```bash
  git add public/landing.html
  git commit -m "feat: redesign landing page with photo background and full-width choice panels"
  ```

---

## Chunk 2: Create entrepreneurship-theme.css

**Files:**
- Create: `public/entrepreneurship/entrepreneurship-theme.css`

This file replaces `valt-theme.css` for the entrepreneurship sub-site. It takes every teal `#2a9d8f` reference and maps it to purple `#3B0F5E` or gold `#C9A84C`, and changes the header from `#00455A` to `#3B0F5E`.

---

### Task 4: Write entrepreneurship-theme.css

- [ ] **Step 1: Create the file**

  Create `public/entrepreneurship/entrepreneurship-theme.css` with this content:

  ```css
  /* VALT Entrepreneurship Theme — Purple + Gold */

  /* --- GLOBAL TYPOGRAPHY --- */
  body { color: #444; -webkit-font-smoothing: antialiased; }
  h1, h2, h3, h4, h5, h6 { color: #1a1a2e !important; letter-spacing: -0.02em; }
  p { color: #555 !important; line-height: 1.8 !important; }
  .section-title h1 { color: #3B0F5E !important; }
  .banner-content h1 { color: #fff !important; text-shadow: 0 2px 4px rgba(0,0,0,0.1); }
  .banner-content p { color: rgba(255,255,255,0.9) !important; }
  .footer-area h4, .footer-area p, .footer-area a { color: rgba(255,255,255,0.85) !important; }
  .single-footer-widget h4 { color: #fff !important; }
  .about-content h1, .about-content a { color: #fff !important; }

  /* --- TEXT ON DARK BACKGROUNDS --- */
  .generic-banner h1, .generic-banner p, .generic-banner a { color: #fff !important; }
  .cta-two-area h1, .cta-two-area p { color: #fff !important; }

  /* --- HEADER & NAVIGATION --- */
  #header { background-color: #3B0F5E !important; }
  #header.header-scrolled { background-color: rgba(59,15,94,.97) !important; box-shadow: 0 2px 20px rgba(0,0,0,0.18) !important; }
  .main-menu { border-bottom: 1px solid rgba(255,255,255,0.08) !important; }
  .nav-menu a { font-weight: 600 !important; font-size: 13px !important; letter-spacing: 0.5px !important; color: rgba(255,255,255,0.85) !important; }
  .nav-menu li:hover > a, .nav-menu ul li:hover > a,
  .nav-menu li.menu-active > a { color: #C9A84C !important; }
  #mobile-nav ul .menu-item-active { color: #C9A84C !important; }

  /* --- HERO BANNER BACKGROUNDS --- */
  .banner-area {
      background-color: #3B0F5E !important;
      background-image: url('../img/headerbg01.jpg') !important;
      background-size: cover !important;
      background-position: center !important;
      background-repeat: no-repeat !important;
  }
  .contact-banner { background-image: url('../img/cta-bg.jpg') !important; background-position: center !important; }
  .banner-area .overlay-bg { background: linear-gradient(135deg, rgba(59,15,94,.85), rgba(59,15,94,.7)) !important; }

  /* --- PRIMARY BUTTONS --- */
  .primary-btn {
      background: linear-gradient(135deg, #3B0F5E, #5a1a8a) !important;
      border-radius: 8px !important;
      padding: 14px 32px !important;
      font-weight: 600 !important;
      font-size: 14px !important;
      letter-spacing: 0.5px !important;
      box-shadow: 0 4px 15px rgba(59,15,94,0.35) !important;
      transition: all 0.3s ease !important;
      border: 2px solid transparent !important;
      color: #fff !important;
  }
  .primary-btn:hover {
      background: linear-gradient(135deg, #C9A84C, #b8962e) !important;
      box-shadow: 0 6px 25px rgba(201,168,76,0.45) !important;
      transform: translateY(-2px);
      color: #fff !important;
  }
  .banner-content .primary-btn {
      background: linear-gradient(135deg, #C9A84C, #b8962e) !important;
      padding: 16px 40px !important;
      font-size: 15px !important;
  }
  .banner-content .primary-btn:hover {
      background: #fff !important;
      color: #3B0F5E !important;
  }

  .genric-btn.primary { background: linear-gradient(135deg, #3B0F5E, #5a1a8a) !important; border-color: transparent !important; box-shadow: 0 4px 15px rgba(59,15,94,0.3) !important; }
  .genric-btn.primary:hover { color: #3B0F5E !important; border-color: #3B0F5E !important; background: #fff !important; }
  .genric-btn.primary-border { color: #3B0F5E !important; border-color: #3B0F5E !important; border-radius: 8px !important; }
  .genric-btn.primary-border:hover { background: #3B0F5E !important; color: #fff !important; }

  /* --- SECTION BACKGROUNDS --- */
  .review-area, .contact-page-area { background: #FAF7F2 !important; }

  /* --- CTA AREAS --- */
  .cta-two-area { background: linear-gradient(135deg, #3B0F5E, #5a1a8a) !important; }
  .generic-banner { background: linear-gradient(135deg, #3B0F5E, #5a1a8a) !important; }

  /* --- SECTION TITLE UNDERLINE --- */
  .section-title h1::after {
      content: '';
      display: block;
      width: 60px;
      height: 3px;
      background: linear-gradient(135deg, #C9A84C, #b8962e);
      margin: 15px auto 0;
      border-radius: 2px;
  }

  /* --- CARDS & HOVER ACCENTS --- */
  .single-popular-carusel {
      border-radius: 12px !important;
      overflow: hidden;
      box-shadow: 0 5px 20px rgba(0,0,0,0.06) !important;
      transition: all 0.3s ease !important;
  }
  .single-popular-carusel:hover { box-shadow: 0 10px 35px rgba(59,15,94,0.15) !important; transform: translateY(-4px); }
  .single-popular-carusel .details h4:hover { color: #3B0F5E !important; }

  /* --- CONTACT PAGE --- */
  .contact-page-area .address-wrap .single-contact-address .lnr { color: #3B0F5E !important; }
  .contact-page-area .form-area input,
  .contact-page-area .form-area textarea { border-radius: 8px !important; border: 1px solid #e0e0e0 !important; transition: border-color 0.3s ease !important; }
  .contact-page-area .form-area input:focus,
  .contact-page-area .form-area textarea:focus { border-color: #C9A84C !important; box-shadow: 0 0 0 2px rgba(201,168,76,0.15) !important; }

  /* --- FOOTER --- */
  .footer-area { background-color: #1a0a2e !important; }
  .single-footer-widget ul li a:hover { color: #C9A84C !important; }
  .footer-bottom .footer-social a:hover { background-color: #C9A84C !important; transform: translateY(-3px); }
  .footer-bottom .footer-text { color: rgba(255,255,255,0.6) !important; }
  .footer-bottom .lnr, .footer-bottom a { color: #C9A84C !important; }

  /* --- FLOAT CONTACT ICONS --- */
  .float-contact {
      position: fixed;
      bottom: 30px;
      right: 25px;
      z-index: 9999;
      display: flex;
      flex-direction: column;
      align-items: center;
      gap: 10px;
  }
  .float-contact a {
      display: flex;
      align-items: center;
      justify-content: center;
      width: 50px;
      height: 50px;
      border-radius: 50%;
      font-size: 22px;
      color: #fff !important;
      box-shadow: 0 4px 15px rgba(0,0,0,0.25);
      transition: transform 0.3s ease, box-shadow 0.3s ease !important;
      text-decoration: none;
  }
  .float-contact a:hover { transform: translateY(-3px); box-shadow: 0 8px 20px rgba(0,0,0,0.3); }
  .float-contact .float-whatsapp { background: #25D366; }
  .float-contact .float-email { background: #3B0F5E; }

  /* --- MOBILE TAGLINE --- */
  .mobile-tagline { display: none; }
  @media (max-width: 960px) {
      .mobile-tagline {
          display: block;
          color: #C9A84C !important;
          font-size: 13px !important;
          font-weight: 600 !important;
          letter-spacing: 1.5px !important;
          text-transform: uppercase !important;
          font-family: 'Poppins', sans-serif !important;
          flex: 1;
          padding: 0 10px;
          text-align: center;
      }
  }

  /* --- SMOOTH TRANSITIONS --- */
  a { transition: color 0.3s ease !important; }
  a:hover { color: #3B0F5E; }

  /* --- PROGRAMME CARDS (custom, used on index + courses pages) --- */
  .e-card {
      background: #fff;
      border-radius: 14px;
      padding: 36px 28px;
      text-align: center;
      box-shadow: 0 8px 28px rgba(59,15,94,0.08);
      transition: transform 0.3s ease, box-shadow 0.3s ease, border-color 0.3s ease;
      border: 2px solid transparent;
      height: 100%;
  }
  .e-card:hover {
      transform: translateY(-8px);
      box-shadow: 0 18px 40px rgba(59,15,94,0.15);
      border-color: #C9A84C;
  }
  .e-card .e-icon {
      font-size: 2.6rem;
      color: #3B0F5E;
      margin-bottom: 18px;
  }
  .e-card h3 {
      color: #3B0F5E !important;
      font-size: 1.2rem;
      font-weight: 700;
      margin-bottom: 12px;
  }
  .e-card p { color: #555 !important; line-height: 1.65 !important; font-size: 0.92rem; margin: 0; }
  ```

- [ ] **Step 2: Verify the file was saved correctly** — no syntax errors, all braces balanced.

- [ ] **Step 3: Commit**

  ```bash
  git add public/entrepreneurship/entrepreneurship-theme.css
  git commit -m "feat: add VALT Entrepreneurship purple/gold theme CSS"
  ```

---

## Chunk 3: Rebuild entrepreneurship/index.html

**Files:**
- Modify (full rebuild): `public/entrepreneurship/index.html`

Reference: `public/academy/index.html` for the nav/footer/JS pattern.

---

### Task 5: Rebuild the Entrepreneurship home page

- [ ] **Step 1: Replace the entire contents of `public/entrepreneurship/index.html`**

  ```html
  <!DOCTYPE html>
  <html lang="en" class="no-js">
  <head>
      <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
      <link rel="shortcut icon" href="../img/logo02.png">
      <meta name="author" content="VALT Entrepreneurship">
      <meta name="description" content="VALT Entrepreneurship - Building innovative businesses and ventures with cutting-edge strategies and mentorship for aspiring entrepreneurs in South Africa.">
      <meta name="keywords" content="VALT, entrepreneurship, business innovation, startups, mentorship, South Africa, Durban">
      <meta property="og:type" content="website">
      <meta property="og:title" content="VALT Entrepreneurship - Build Your Future">
      <meta property="og:description" content="Building innovative businesses and ventures with cutting-edge strategies and mentorship.">
      <meta property="og:image" content="../img/logo02.png">
      <meta property="og:url" content="https://valt.co.za/entrepreneurship/">
      <meta name="twitter:card" content="summary_large_image">
      <meta name="twitter:title" content="VALT Entrepreneurship - Build Your Future">
      <meta name="twitter:description" content="Building innovative businesses and ventures with cutting-edge strategies and mentorship.">
      <meta name="twitter:image" content="../img/logo02.png">
      <meta charset="UTF-8">
      <title>VALT Entrepreneurship - Build Your Future</title>

      <link href="https://fonts.googleapis.com/css?family=Poppins:100,200,400,300,500,600,700" rel="stylesheet">
      <link rel="stylesheet" href="../css/linearicons.css">
      <link rel="stylesheet" href="../css/font-awesome.min.css">
      <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
      <link rel="stylesheet" href="../css/bootstrap.css">
      <link rel="stylesheet" href="../css/magnific-popup.css">
      <link rel="stylesheet" href="../css/nice-select.css">
      <link rel="stylesheet" href="../css/animate.min.css">
      <link rel="stylesheet" href="../css/owl.carousel.css">
      <link rel="stylesheet" href="../css/jquery-ui.css">
      <link rel="stylesheet" href="../css/main.css">
      <link rel="stylesheet" href="entrepreneurship-theme.css">
  </head>
  <body>

  <header id="header">
      <div class="container main-menu">
          <div class="row align-items-center justify-content-between d-flex">
              <div id="logo">
                  <a href="index.html"><img src="../img/logo02.png" alt="VALT Entrepreneurship" style="height: 65px;"></a>
              </div>
              <div class="mobile-tagline">Build Your Future</div>
              <nav id="nav-menu-container">
                  <ul class="nav-menu">
                      <li class="menu-active"><a href="index.html">Home</a></li>
                      <li><a href="about.html">About</a></li>
                      <li><a href="courses.html">Programmes</a></li>
                      <li><a href="contact.html">Contact</a></li>
                  </ul>
              </nav>
          </div>
      </div>
  </header>

  <!-- Hero Banner -->
  <section class="banner-area relative" id="home">
      <div class="overlay overlay-bg"></div>
      <div class="container">
          <div class="row fullscreen d-flex align-items-center justify-content-between">
              <div class="banner-content col-lg-9 col-md-12">
                  <h1 class="text-uppercase">VALT Entrepreneurship</h1>
                  <p>Build Your Future — cutting-edge strategies and mentorship for aspiring entrepreneurs in South Africa.</p>
                  <a href="courses.html" class="primary-btn text-uppercase">View Programmes</a>
              </div>
          </div>
      </div>
  </section>

  <!-- Intro Section -->
  <section class="section-gap">
      <div class="container">
          <div class="row justify-content-center">
              <div class="col-lg-8 text-center">
                  <div class="section-title">
                      <h1>Who We Are</h1>
                  </div>
                  <p>VALT Entrepreneurship supports aspiring entrepreneurs in Durban, KZN, South Africa with the tools, knowledge, and network needed to build successful and sustainable businesses. Founded in 2019, we are committed to growing a thriving entrepreneurial ecosystem across South Africa.</p>
                  <a href="about.html" class="primary-btn text-uppercase mt-20">Learn More</a>
              </div>
          </div>
      </div>
  </section>

  <!-- Highlight Programme Cards -->
  <section class="section-gap" style="background: #FAF7F2;">
      <div class="container">
          <div class="row justify-content-center">
              <div class="col-lg-8 text-center">
                  <div class="section-title">
                      <h1>Our Programmes</h1>
                  </div>
                  <p class="mb-40">Comprehensive support for aspiring entrepreneurs at every stage of their journey.</p>
              </div>
          </div>
          <div class="row">
              <div class="col-lg-4 col-md-6 mb-30">
                  <div class="e-card">
                      <div class="e-icon"><i class="fas fa-lightbulb"></i></div>
                      <h3>Ideation &amp; Validation</h3>
                      <p>Transform your ideas into viable business concepts through market research and validation methodologies.</p>
                  </div>
              </div>
              <div class="col-lg-4 col-md-6 mb-30">
                  <div class="e-card">
                      <div class="e-icon"><i class="fas fa-rocket"></i></div>
                      <h3>Startup Acceleration</h3>
                      <p>Fast-track your startup with our intensive accelerator program, mentorship, and funding opportunities.</p>
                  </div>
              </div>
              <div class="col-lg-4 col-md-6 mb-30">
                  <div class="e-card">
                      <div class="e-icon"><i class="fas fa-chart-line"></i></div>
                      <h3>Growth Strategy</h3>
                      <p>Scale your business with proven growth strategies, digital marketing, and operational excellence.</p>
                  </div>
              </div>
          </div>
          <div class="row justify-content-center mt-10">
              <div class="col-auto">
                  <a href="courses.html" class="primary-btn text-uppercase">View All Programmes</a>
              </div>
          </div>
      </div>
  </section>

  <!-- Float Contact -->
  <div class="float-contact">
      <a href="https://wa.me/27614610828" class="float-whatsapp" target="_blank" title="WhatsApp us"><i class="fa fa-whatsapp"></i></a>
      <a href="mailto:info@valt.co.za" class="float-email" title="Email us"><i class="fa fa-envelope"></i></a>
  </div>

  <!-- Footer -->
  <footer class="footer-area section-gap">
      <div class="container">
          <div class="row">
              <div class="col-lg-4 col-md-6 col-sm-6">
                  <div class="single-footer-widget">
                      <h4>VALT Entrepreneurship</h4>
                      <p>Building innovative businesses and ventures with cutting-edge strategies and mentorship for aspiring entrepreneurs.</p>
                  </div>
              </div>
              <div class="col-lg-4 col-md-6 col-sm-6">
                  <div class="single-footer-widget">
                      <h4>Quick Links</h4>
                      <ul>
                          <li><a href="index.html">Home</a></li>
                          <li><a href="about.html">About</a></li>
                          <li><a href="courses.html">Programmes</a></li>
                          <li><a href="contact.html">Contact</a></li>
                      </ul>
                  </div>
              </div>
              <div class="col-lg-4 col-md-6 col-sm-6">
                  <div class="single-footer-widget">
                      <h4>Contact</h4>
                      <p>Durban, KZN, South Africa</p>
                      <p><a href="https://wa.me/27614610828">061 461 0828</a></p>
                      <p><a href="mailto:info@valt.co.za">info@valt.co.za</a></p>
                  </div>
              </div>
          </div>
          <div class="footer-bottom row align-items-center">
              <p class="footer-text col-lg-8">&copy; 2026 VALT Entrepreneurship. All rights reserved.</p>
          </div>
      </div>
  </footer>

  <script src="../js/vendor/jquery-2.2.4.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js"></script>
  <script src="../js/vendor/bootstrap.min.js"></script>
  <script src="../js/easing.min.js"></script>
  <script src="../js/hoverIntent.js"></script>
  <script src="../js/superfish.min.js"></script>
  <script src="../js/jquery.ajaxchimp.min.js"></script>
  <script src="../js/jquery.magnific-popup.min.js"></script>
  <script src="../js/jquery.tabs.min.js"></script>
  <script src="../js/jquery.nice-select.min.js"></script>
  <script src="../js/owl.carousel.min.js"></script>
  <script src="../js/mail-script.js"></script>
  <script src="../js/main.js"></script>
  </body>
  </html>
  ```

- [ ] **Step 2: Verify in browser**

  - Purple header with gold hover nav links
  - Hero banner with purple overlay and "VALT Entrepreneurship" heading
  - "Who We Are" section below
  - 3 programme cards with purple icons, gold border on hover
  - Footer with dark purple/navy background

- [ ] **Step 3: Commit**

  ```bash
  git add public/entrepreneurship/index.html
  git commit -m "feat: rebuild entrepreneurship home page with purple/gold theme"
  ```

---

## Chunk 4: Entrepreneurship About + Courses Pages

**Files:**
- Create: `public/entrepreneurship/about.html`
- Create: `public/entrepreneurship/courses.html`

---

### Task 6: Create about.html

- [ ] **Step 1: Create `public/entrepreneurship/about.html`**

  Use the same head, header, footer, and JS stack from `index.html` (copy them). Change the nav active item to `about.html`. Page-specific content:

  ```html
  <!DOCTYPE html>
  <html lang="en" class="no-js">
  <head>
      <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
      <link rel="shortcut icon" href="../img/logo02.png">
      <meta name="author" content="VALT Entrepreneurship">
      <meta name="description" content="About VALT Entrepreneurship - Founded in 2019, empowering South African entrepreneurs with the tools and network to build successful businesses.">
      <meta name="keywords" content="VALT, about, entrepreneurship, South Africa, Durban">
      <meta property="og:type" content="website">
      <meta property="og:title" content="About Us - VALT Entrepreneurship">
      <meta property="og:description" content="Founded in 2019, VALT Entrepreneurship empowers South African entrepreneurs with tools, knowledge, and network.">
      <meta property="og:image" content="../img/logo02.png">
      <meta property="og:url" content="https://valt.co.za/entrepreneurship/about.html">
      <meta name="twitter:card" content="summary_large_image">
      <meta name="twitter:title" content="About Us - VALT Entrepreneurship">
      <meta name="twitter:description" content="Founded in 2019, VALT Entrepreneurship empowers South African entrepreneurs.">
      <meta name="twitter:image" content="../img/logo02.png">
      <meta charset="UTF-8">
      <title>About Us - VALT Entrepreneurship</title>

      <link href="https://fonts.googleapis.com/css?family=Poppins:100,200,400,300,500,600,700" rel="stylesheet">
      <link rel="stylesheet" href="../css/linearicons.css">
      <link rel="stylesheet" href="../css/font-awesome.min.css">
      <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
      <link rel="stylesheet" href="../css/bootstrap.css">
      <link rel="stylesheet" href="../css/magnific-popup.css">
      <link rel="stylesheet" href="../css/nice-select.css">
      <link rel="stylesheet" href="../css/animate.min.css">
      <link rel="stylesheet" href="../css/owl.carousel.css">
      <link rel="stylesheet" href="../css/jquery-ui.css">
      <link rel="stylesheet" href="../css/main.css">
      <link rel="stylesheet" href="entrepreneurship-theme.css">
  </head>
  <body>

  <header id="header">
      <div class="container main-menu">
          <div class="row align-items-center justify-content-between d-flex">
              <div id="logo">
                  <a href="index.html"><img src="../img/logo02.png" alt="VALT Entrepreneurship" style="height: 65px;"></a>
              </div>
              <div class="mobile-tagline">Build Your Future</div>
              <nav id="nav-menu-container">
                  <ul class="nav-menu">
                      <li><a href="index.html">Home</a></li>
                      <li class="menu-active"><a href="about.html">About</a></li>
                      <li><a href="courses.html">Programmes</a></li>
                      <li><a href="contact.html">Contact</a></li>
                  </ul>
              </nav>
          </div>
      </div>
  </header>

  <!-- Banner -->
  <section class="banner-area relative about-banner" id="home">
      <div class="overlay overlay-bg"></div>
      <div class="container">
          <div class="row d-flex align-items-center justify-content-center">
              <div class="about-content col-lg-12">
                  <h1 class="text-white">About Us</h1>
                  <p class="text-white link-nav">
                      <a href="index.html">Home</a>
                      <span class="lnr lnr-arrow-right"></span>
                      <a href="about.html">About Us</a>
                  </p>
              </div>
          </div>
      </div>
  </section>

  <!-- Who We Are -->
  <section class="section-gap">
      <div class="container">
          <div class="row justify-content-center">
              <div class="col-lg-8 text-center">
                  <div class="section-title">
                      <h1>Who We Are</h1>
                  </div>
                  <p>VALT Entrepreneurship was founded in 2019 in Durban, KZN, South Africa. We exist to support aspiring entrepreneurs with the tools, knowledge, and network needed to turn ideas into thriving businesses. Our programmes are designed for every stage of the entrepreneurial journey — from idea validation to scaling and funding.</p>
              </div>
          </div>
      </div>
  </section>

  <!-- Mission & Vision -->
  <section class="section-gap" style="background: #FAF7F2;">
      <div class="container">
          <div class="row">
              <div class="col-lg-6 mb-30">
                  <div class="e-card" style="text-align:left;">
                      <div class="e-icon"><i class="fas fa-bullseye"></i></div>
                      <h3>Our Mission</h3>
                      <p>To empower aspiring entrepreneurs with the tools, knowledge, and network to build successful and sustainable businesses that create lasting impact in their communities.</p>
                  </div>
              </div>
              <div class="col-lg-6 mb-30">
                  <div class="e-card" style="text-align:left;">
                      <div class="e-icon"><i class="fas fa-eye"></i></div>
                      <h3>Our Vision</h3>
                      <p>A thriving entrepreneurial ecosystem across South Africa, where every aspiring entrepreneur has access to world-class support, mentorship, and opportunity.</p>
                  </div>
              </div>
          </div>
      </div>
  </section>

  <!-- Float Contact -->
  <div class="float-contact">
      <a href="https://wa.me/27614610828" class="float-whatsapp" target="_blank" title="WhatsApp us"><i class="fa fa-whatsapp"></i></a>
      <a href="mailto:info@valt.co.za" class="float-email" title="Email us"><i class="fa fa-envelope"></i></a>
  </div>

  <!-- Footer -->
  <footer class="footer-area section-gap">
      <div class="container">
          <div class="row">
              <div class="col-lg-4 col-md-6 col-sm-6">
                  <div class="single-footer-widget">
                      <h4>VALT Entrepreneurship</h4>
                      <p>Building innovative businesses and ventures with cutting-edge strategies and mentorship.</p>
                  </div>
              </div>
              <div class="col-lg-4 col-md-6 col-sm-6">
                  <div class="single-footer-widget">
                      <h4>Quick Links</h4>
                      <ul>
                          <li><a href="index.html">Home</a></li>
                          <li><a href="about.html">About</a></li>
                          <li><a href="courses.html">Programmes</a></li>
                          <li><a href="contact.html">Contact</a></li>
                      </ul>
                  </div>
              </div>
              <div class="col-lg-4 col-md-6 col-sm-6">
                  <div class="single-footer-widget">
                      <h4>Contact</h4>
                      <p>Durban, KZN, South Africa</p>
                      <p><a href="https://wa.me/27614610828">061 461 0828</a></p>
                      <p><a href="mailto:info@valt.co.za">info@valt.co.za</a></p>
                  </div>
              </div>
          </div>
          <div class="footer-bottom row align-items-center">
              <p class="footer-text col-lg-8">&copy; 2026 VALT Entrepreneurship. All rights reserved.</p>
          </div>
      </div>
  </footer>

  <script src="../js/vendor/jquery-2.2.4.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js"></script>
  <script src="../js/vendor/bootstrap.min.js"></script>
  <script src="../js/easing.min.js"></script>
  <script src="../js/hoverIntent.js"></script>
  <script src="../js/superfish.min.js"></script>
  <script src="../js/jquery.ajaxchimp.min.js"></script>
  <script src="../js/jquery.magnific-popup.min.js"></script>
  <script src="../js/jquery.tabs.min.js"></script>
  <script src="../js/jquery.nice-select.min.js"></script>
  <script src="../js/owl.carousel.min.js"></script>
  <script src="../js/mail-script.js"></script>
  <script src="../js/main.js"></script>
  </body>
  </html>
  ```

- [ ] **Step 2: Verify in browser** — purple banner, "About Us" heading, Who We Are text, Mission and Vision cards side by side.

- [ ] **Step 3: Commit**

  ```bash
  git add public/entrepreneurship/about.html
  git commit -m "feat: add entrepreneurship about page"
  ```

---

### Task 7: Create courses.html

- [ ] **Step 1: Create `public/entrepreneurship/courses.html`**

  Same head/header/footer/JS as above, active nav item = `courses.html`:

  ```html
  <!DOCTYPE html>
  <html lang="en" class="no-js">
  <head>
      <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
      <link rel="shortcut icon" href="../img/logo02.png">
      <meta name="author" content="VALT Entrepreneurship">
      <meta name="description" content="VALT Entrepreneurship Programmes - Ideation, Startup Acceleration, Growth Strategy, Networking, Funding, and Business Certification.">
      <meta name="keywords" content="VALT, entrepreneurship programmes, startup, business, South Africa">
      <meta property="og:type" content="website">
      <meta property="og:title" content="Programmes - VALT Entrepreneurship">
      <meta property="og:description" content="Comprehensive entrepreneurship programmes for every stage of your journey.">
      <meta property="og:image" content="../img/logo02.png">
      <meta property="og:url" content="https://valt.co.za/entrepreneurship/courses.html">
      <meta name="twitter:card" content="summary_large_image">
      <meta name="twitter:title" content="Programmes - VALT Entrepreneurship">
      <meta name="twitter:description" content="Comprehensive entrepreneurship programmes for every stage of your journey.">
      <meta name="twitter:image" content="../img/logo02.png">
      <meta charset="UTF-8">
      <title>Programmes - VALT Entrepreneurship</title>

      <link href="https://fonts.googleapis.com/css?family=Poppins:100,200,400,300,500,600,700" rel="stylesheet">
      <link rel="stylesheet" href="../css/linearicons.css">
      <link rel="stylesheet" href="../css/font-awesome.min.css">
      <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
      <link rel="stylesheet" href="../css/bootstrap.css">
      <link rel="stylesheet" href="../css/magnific-popup.css">
      <link rel="stylesheet" href="../css/nice-select.css">
      <link rel="stylesheet" href="../css/animate.min.css">
      <link rel="stylesheet" href="../css/owl.carousel.css">
      <link rel="stylesheet" href="../css/jquery-ui.css">
      <link rel="stylesheet" href="../css/main.css">
      <link rel="stylesheet" href="entrepreneurship-theme.css">
  </head>
  <body>

  <header id="header">
      <div class="container main-menu">
          <div class="row align-items-center justify-content-between d-flex">
              <div id="logo">
                  <a href="index.html"><img src="../img/logo02.png" alt="VALT Entrepreneurship" style="height: 65px;"></a>
              </div>
              <div class="mobile-tagline">Build Your Future</div>
              <nav id="nav-menu-container">
                  <ul class="nav-menu">
                      <li><a href="index.html">Home</a></li>
                      <li><a href="about.html">About</a></li>
                      <li class="menu-active"><a href="courses.html">Programmes</a></li>
                      <li><a href="contact.html">Contact</a></li>
                  </ul>
              </nav>
          </div>
      </div>
  </header>

  <!-- Banner -->
  <section class="banner-area relative about-banner" id="home">
      <div class="overlay overlay-bg"></div>
      <div class="container">
          <div class="row d-flex align-items-center justify-content-center">
              <div class="about-content col-lg-12">
                  <h1 class="text-white">Our Programmes</h1>
                  <p class="text-white link-nav">
                      <a href="index.html">Home</a>
                      <span class="lnr lnr-arrow-right"></span>
                      <a href="courses.html">Programmes</a>
                  </p>
              </div>
          </div>
      </div>
  </section>

  <!-- Programmes Grid -->
  <section class="section-gap">
      <div class="container">
          <div class="row justify-content-center">
              <div class="col-lg-8 text-center">
                  <div class="section-title">
                      <h1>What We Offer</h1>
                  </div>
                  <p class="mb-50">Comprehensive support for aspiring entrepreneurs at every stage of their journey.</p>
              </div>
          </div>
          <div class="row">
              <div class="col-lg-4 col-md-6 mb-30">
                  <div class="e-card">
                      <div class="e-icon"><i class="fas fa-lightbulb"></i></div>
                      <h3>Ideation &amp; Validation</h3>
                      <p>Transform your ideas into viable business concepts through market research and validation methodologies.</p>
                  </div>
              </div>
              <div class="col-lg-4 col-md-6 mb-30">
                  <div class="e-card">
                      <div class="e-icon"><i class="fas fa-rocket"></i></div>
                      <h3>Startup Acceleration</h3>
                      <p>Fast-track your startup with our intensive accelerator program, mentorship, and funding opportunities.</p>
                  </div>
              </div>
              <div class="col-lg-4 col-md-6 mb-30">
                  <div class="e-card">
                      <div class="e-icon"><i class="fas fa-chart-line"></i></div>
                      <h3>Growth Strategy</h3>
                      <p>Scale your business with proven growth strategies, digital marketing, and operational excellence.</p>
                  </div>
              </div>
              <div class="col-lg-4 col-md-6 mb-30">
                  <div class="e-card">
                      <div class="e-icon"><i class="fas fa-users"></i></div>
                      <h3>Network &amp; Community</h3>
                      <p>Join a thriving community of entrepreneurs, investors, and industry experts dedicated to your success.</p>
                  </div>
              </div>
              <div class="col-lg-4 col-md-6 mb-30">
                  <div class="e-card">
                      <div class="e-icon"><i class="fas fa-piggy-bank"></i></div>
                      <h3>Funding &amp; Investment</h3>
                      <p>Access funding opportunities, pitch competitions, and connect with potential investors.</p>
                  </div>
              </div>
              <div class="col-lg-4 col-md-6 mb-30">
                  <div class="e-card">
                      <div class="e-icon"><i class="fas fa-certificate"></i></div>
                      <h3>Business Certification</h3>
                      <p>Earn recognised certifications in entrepreneurship, business management, and innovation.</p>
                  </div>
              </div>
          </div>
      </div>
  </section>

  <!-- Float Contact -->
  <div class="float-contact">
      <a href="https://wa.me/27614610828" class="float-whatsapp" target="_blank" title="WhatsApp us"><i class="fa fa-whatsapp"></i></a>
      <a href="mailto:info@valt.co.za" class="float-email" title="Email us"><i class="fa fa-envelope"></i></a>
  </div>

  <!-- Footer -->
  <footer class="footer-area section-gap">
      <div class="container">
          <div class="row">
              <div class="col-lg-4 col-md-6 col-sm-6">
                  <div class="single-footer-widget">
                      <h4>VALT Entrepreneurship</h4>
                      <p>Building innovative businesses and ventures with cutting-edge strategies and mentorship.</p>
                  </div>
              </div>
              <div class="col-lg-4 col-md-6 col-sm-6">
                  <div class="single-footer-widget">
                      <h4>Quick Links</h4>
                      <ul>
                          <li><a href="index.html">Home</a></li>
                          <li><a href="about.html">About</a></li>
                          <li><a href="courses.html">Programmes</a></li>
                          <li><a href="contact.html">Contact</a></li>
                      </ul>
                  </div>
              </div>
              <div class="col-lg-4 col-md-6 col-sm-6">
                  <div class="single-footer-widget">
                      <h4>Contact</h4>
                      <p>Durban, KZN, South Africa</p>
                      <p><a href="https://wa.me/27614610828">061 461 0828</a></p>
                      <p><a href="mailto:info@valt.co.za">info@valt.co.za</a></p>
                  </div>
              </div>
          </div>
          <div class="footer-bottom row align-items-center">
              <p class="footer-text col-lg-8">&copy; 2026 VALT Entrepreneurship. All rights reserved.</p>
          </div>
      </div>
  </footer>

  <script src="../js/vendor/jquery-2.2.4.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js"></script>
  <script src="../js/vendor/bootstrap.min.js"></script>
  <script src="../js/easing.min.js"></script>
  <script src="../js/hoverIntent.js"></script>
  <script src="../js/superfish.min.js"></script>
  <script src="../js/jquery.ajaxchimp.min.js"></script>
  <script src="../js/jquery.magnific-popup.min.js"></script>
  <script src="../js/jquery.tabs.min.js"></script>
  <script src="../js/jquery.nice-select.min.js"></script>
  <script src="../js/owl.carousel.min.js"></script>
  <script src="../js/mail-script.js"></script>
  <script src="../js/main.js"></script>
  </body>
  </html>
  ```

- [ ] **Step 2: Verify in browser** — all 6 programme cards in a 3-column grid, each with purple icon and gold hover border.

- [ ] **Step 3: Commit**

  ```bash
  git add public/entrepreneurship/courses.html
  git commit -m "feat: add entrepreneurship programmes page with 6 cards"
  ```

---

## Chunk 5: Entrepreneurship Contact Page

**Files:**
- Create: `public/entrepreneurship/contact.html`

---

### Task 8: Create contact.html

- [ ] **Step 1: Create `public/entrepreneurship/contact.html`**

  ```html
  <!DOCTYPE html>
  <html lang="en" class="no-js">
  <head>
      <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
      <link rel="shortcut icon" href="../img/logo02.png">
      <meta name="author" content="VALT Entrepreneurship">
      <meta name="description" content="Contact VALT Entrepreneurship - Get in touch about our entrepreneurship programmes in South Africa.">
      <meta name="keywords" content="VALT, contact, entrepreneurship, Durban, South Africa">
      <meta property="og:type" content="website">
      <meta property="og:title" content="Contact Us - VALT Entrepreneurship">
      <meta property="og:description" content="Get in touch with VALT Entrepreneurship about our programmes and support.">
      <meta property="og:image" content="../img/logo02.png">
      <meta property="og:url" content="https://valt.co.za/entrepreneurship/contact.html">
      <meta name="twitter:card" content="summary_large_image">
      <meta name="twitter:title" content="Contact Us - VALT Entrepreneurship">
      <meta name="twitter:description" content="Get in touch with VALT Entrepreneurship.">
      <meta name="twitter:image" content="../img/logo02.png">
      <meta charset="UTF-8">
      <title>Contact Us - VALT Entrepreneurship</title>

      <link href="https://fonts.googleapis.com/css?family=Poppins:100,200,400,300,500,600,700" rel="stylesheet">
      <link rel="stylesheet" href="../css/linearicons.css">
      <link rel="stylesheet" href="../css/font-awesome.min.css">
      <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
      <link rel="stylesheet" href="../css/bootstrap.css">
      <link rel="stylesheet" href="../css/magnific-popup.css">
      <link rel="stylesheet" href="../css/nice-select.css">
      <link rel="stylesheet" href="../css/animate.min.css">
      <link rel="stylesheet" href="../css/owl.carousel.css">
      <link rel="stylesheet" href="../css/jquery-ui.css">
      <link rel="stylesheet" href="../css/main.css">
      <link rel="stylesheet" href="entrepreneurship-theme.css">
  </head>
  <body>

  <header id="header">
      <div class="container main-menu">
          <div class="row align-items-center justify-content-between d-flex">
              <div id="logo">
                  <a href="index.html"><img src="../img/logo02.png" alt="VALT Entrepreneurship" style="height: 65px;"></a>
              </div>
              <div class="mobile-tagline">Build Your Future</div>
              <nav id="nav-menu-container">
                  <ul class="nav-menu">
                      <li><a href="index.html">Home</a></li>
                      <li><a href="about.html">About</a></li>
                      <li><a href="courses.html">Programmes</a></li>
                      <li class="menu-active"><a href="contact.html">Contact</a></li>
                  </ul>
              </nav>
          </div>
      </div>
  </header>

  <!-- Banner -->
  <section class="banner-area relative about-banner contact-banner" id="home">
      <div class="overlay overlay-bg"></div>
      <div class="container">
          <div class="row d-flex align-items-center justify-content-center">
              <div class="about-content col-lg-12">
                  <h1 class="text-white">Contact Us</h1>
                  <p class="text-white link-nav">
                      <a href="index.html">Home</a>
                      <span class="lnr lnr-arrow-right"></span>
                      <a href="contact.html">Contact Us</a>
                  </p>
              </div>
          </div>
      </div>
  </section>

  <!-- Contact Section -->
  <section class="contact-page-area section-gap">
      <div class="container">
          <div class="row">
              <div class="col-lg-4 d-flex flex-column address-wrap">
                  <div class="single-contact-address d-flex flex-row">
                      <div class="icon"><span class="lnr lnr-home"></span></div>
                      <div class="contact-details">
                          <h5>Durban, KZN</h5>
                          <p>South Africa</p>
                      </div>
                  </div>
                  <div class="single-contact-address d-flex flex-row">
                      <div class="icon">
                          <span class="fa fa-whatsapp" style="font-size:30px;font-weight:500;color:#25D366;margin-right:30px;"></span>
                      </div>
                      <div class="contact-details">
                          <h5><a href="https://wa.me/27614610828">061 461 0828</a></h5>
                          <p>WhatsApp us anytime!</p>
                      </div>
                  </div>
                  <div class="single-contact-address d-flex flex-row">
                      <div class="icon"><span class="lnr lnr-envelope"></span></div>
                      <div class="contact-details">
                          <h5><a href="mailto:info@valt.co.za">info@valt.co.za</a></h5>
                          <p>Send us your query anytime!</p>
                      </div>
                  </div>
              </div>
              <div class="col-lg-8">
                  <form class="form-area contact-form text-right" id="myForm" action="../mail.php" method="post">
                      <input type="text" name="website" style="display:none;" tabindex="-1" autocomplete="off">
                      <div class="row">
                          <div class="col-lg-6 form-group">
                              <input name="name" placeholder="Enter your name" onfocus="this.placeholder=''" onblur="this.placeholder='Enter your name'" class="common-input mb-20 form-control" required type="text" aria-label="Your name">
                              <input name="email" placeholder="Enter email address" pattern="[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{1,63}$" onfocus="this.placeholder=''" onblur="this.placeholder='Enter email address'" class="common-input mb-20 form-control" required type="email" aria-label="Email address">
                              <input name="subject" placeholder="Enter subject" onfocus="this.placeholder=''" onblur="this.placeholder='Enter subject'" class="common-input mb-20 form-control" required type="text" aria-label="Subject">
                          </div>
                          <div class="col-lg-6 form-group">
                              <textarea class="common-textarea form-control" name="message" placeholder="Enter your message" onfocus="this.placeholder=''" onblur="this.placeholder='Enter your message'" required aria-label="Message"></textarea>
                          </div>
                          <div class="col-lg-12">
                              <div class="alert-msg" style="text-align:left;"></div>
                              <button class="genric-btn primary submit-btn" style="float:right;">Send Message</button>
                          </div>
                      </div>
                  </form>
              </div>
          </div>
      </div>
  </section>

  <!-- Float Contact -->
  <div class="float-contact">
      <a href="https://wa.me/27614610828" class="float-whatsapp" target="_blank" title="WhatsApp us"><i class="fa fa-whatsapp"></i></a>
      <a href="mailto:info@valt.co.za" class="float-email" title="Email us"><i class="fa fa-envelope"></i></a>
  </div>

  <!-- Footer -->
  <footer class="footer-area section-gap">
      <div class="container">
          <div class="row">
              <div class="col-lg-4 col-md-6 col-sm-6">
                  <div class="single-footer-widget">
                      <h4>VALT Entrepreneurship</h4>
                      <p>Building innovative businesses and ventures with cutting-edge strategies and mentorship.</p>
                  </div>
              </div>
              <div class="col-lg-4 col-md-6 col-sm-6">
                  <div class="single-footer-widget">
                      <h4>Quick Links</h4>
                      <ul>
                          <li><a href="index.html">Home</a></li>
                          <li><a href="about.html">About</a></li>
                          <li><a href="courses.html">Programmes</a></li>
                          <li><a href="contact.html">Contact</a></li>
                      </ul>
                  </div>
              </div>
              <div class="col-lg-4 col-md-6 col-sm-6">
                  <div class="single-footer-widget">
                      <h4>Contact</h4>
                      <p>Durban, KZN, South Africa</p>
                      <p><a href="https://wa.me/27614610828">061 461 0828</a></p>
                      <p><a href="mailto:info@valt.co.za">info@valt.co.za</a></p>
                  </div>
              </div>
          </div>
          <div class="footer-bottom row align-items-center">
              <p class="footer-text col-lg-8">&copy; 2026 VALT Entrepreneurship. All rights reserved.</p>
          </div>
      </div>
  </footer>

  <script src="../js/vendor/jquery-2.2.4.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js"></script>
  <script src="../js/vendor/bootstrap.min.js"></script>
  <script src="../js/easing.min.js"></script>
  <script src="../js/hoverIntent.js"></script>
  <script src="../js/superfish.min.js"></script>
  <script src="../js/jquery.ajaxchimp.min.js"></script>
  <script src="../js/jquery.magnific-popup.min.js"></script>
  <script src="../js/jquery.tabs.min.js"></script>
  <script src="../js/jquery.nice-select.min.js"></script>
  <script src="../js/owl.carousel.min.js"></script>
  <script src="../js/mail-script.js"></script>
  <script src="../js/form-handlers.js"></script>
  <script src="../js/main.js"></script>
  </body>
  </html>
  ```

  > **Note:** The form action is `../mail.php` (with `../`) — this is correct from the `entrepreneurship/` subdirectory. Do NOT use `mail.php` without the prefix.

- [ ] **Step 2: Verify in browser**

  - Purple banner with "Contact Us" heading
  - Left column: address, WhatsApp, email
  - Right column: form with Name, Email, Subject, Message fields and Send button
  - Submit button styled with purple/gold theme

- [ ] **Step 3: Commit**

  ```bash
  git add public/entrepreneurship/contact.html
  git commit -m "feat: add entrepreneurship contact page"
  ```

---

## Final Verification Checklist

- [ ] Open `public/landing.html` in browser — photo background visible, two panels side by side, hover effects work
- [ ] Click Academy panel → lands on `public/academy/index.html` (teal theme)
- [ ] Click Entrepreneurship panel → lands on `public/entrepreneurship/index.html` (purple theme)
- [ ] Navigate all 4 entrepreneurship pages — nav links work, active item highlighted
- [ ] Check on mobile (Chrome DevTools, 375px) — panels stack, nav collapses correctly
- [ ] Contact form on entrepreneurship/contact.html — fields and submit button visible and styled
- [ ] No console errors in browser DevTools on any page
- [ ] Academy pages unchanged — verify `academy/index.html` still loads with teal theme

```bash
git log --oneline -8
```
