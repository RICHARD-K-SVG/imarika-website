# Imarika School Website — Project Structure

*Reference document consolidating Phases 1–6. Source of truth for requirements remains `IMARIKA_WEBSITE_REQUIREMENTS_v3.docx`.*

---

## 1. Project Summary

| | |
|---|---|
| **Client** | Imarika School (Pre-Primary & Primary, Playgroup–Standard 4) |
| **Type** | Marketing / informational site with contact & admissions funnel |
| **Stack** | HTML5, CSS3, vanilla JS (front-end) · PHP + optional MySQL (back-end) |
| **Status** | Phases 1–6 complete (audit → IA → design system → wireframes → comps → prototype). Phase 7 (development) not started — pending real content. |

---

## 2. File & Folder Structure

Structure as scaffolded in the Phase 6 prototype, extended with the Phase 7 additions this project will need:

```
imarika-website/
│
├── index.html              # Home
├── about.html               # About (Mission, Vision, History)
├── programs.html            # Programs (Pre-Primary / Primary tabs)
├── gallery.html              # Gallery (photo grid)
├── admissions.html           # Admissions (steps + requirements)
├── contact.html              # Contact (map, details, form)
│
├── styles.css                # Single shared stylesheet (design system tokens)
├── script.js                  # Nav toggle, tab switch, gallery lightbox
│
├── assets/                    # Images, favicon
│   ├── images/
│   │   ├── logo.svg
│   │   ├── gallery/            # Real school photos replace placeholder tiles
│   │   │   ├── classroom-1.jpg
│   │   │   ├── outdoor-play-1.jpg
│   │   │   ├── school-event-1.jpg
│   │   │   ├── arts-crafts-1.jpg
│   │   │   ├── graduation-1.jpg
│   │   │   └── story-time-1.jpg
│   │   └── og-preview.jpg       # Open Graph share image (Section 9 enhancement)
│   └── favicon.ico              # Section 9 enhancement
│
├── includes/                    # Phase 7 back-end
│   ├── contact-handler.php       # Processes the Contact form via PHPMailer
│   ├── db-config.php              # MySQL credentials (optional inquiry storage)
│   └── vendor/                     # PHPMailer library (install via Composer)
│
├── sitemap.xml                     # Section 9 / SEO default inclusion
├── robots.txt                       # Section 9 / SEO default inclusion
│
├── IMARIKA_WEBSITE_REQUIREMENTS_v3.docx   # Single source of truth for scope
└── PROJECT_STRUCTURE.md                     # This file
```

---

## 3. Sitemap (Information Architecture)

```
Home
 ├── About
 ├── Programs
 │    ├── Tab: Pre-Primary (Playgroup, PP1, PP2)
 │    └── Tab: Primary (Standard 1–4)
 ├── Gallery
 ├── Admissions
 └── Contact
```

All 6 pages sit one click from Home via the persistent nav. Logo and "Apply Now" button appear on every page independent of the nav bar. Footer duplicates nav links plus contact details site-wide.

---

## 4. Design System Reference

### Colors
| Token | Hex | Approved use |
|---|---|---|
| Deep Maroon | `#7A1B2E` | Navbar, headings, primary buttons, Admissions bg |
| Royal Blue | `#1B4FA0` | Links, subheadings, icons |
| Sky Blue | `#3FA9DC` | Hover states, dividers — **never text** (fails contrast) |
| Golden Yellow | `#F5C518` | "Apply Now" button only — reserved, not overused |
| White | `#FFFFFF` | Card / body backgrounds |
| Dark Ink | `#211417` | Body text |

### Typography
- Headings: **Fraunces** (600 weight), 40/28/20px desktop → 32/24/18px mobile
- Body & nav: **Work Sans** (400/500), 16px body, 14px small text

### Layout
- 8px spacing base unit · 12-column grid desktop → single column at 768px breakpoint
- Cards: white bg, 1px border, 10–12px radius, no drop shadows

### Accessibility
- Visible focus outline: 2px Royal Blue, 2px offset
- `prefers-reduced-motion` respected — no default animation
- All interactive elements keyboard-reachable

---

## 5. Page-by-Page Content Map

| Page | Key sections |
|---|---|
| **Home** | Nav+logo → Hero (headline, tagline, dual CTA) → Established badge → footer |
| **About** | Mission → Vision → School history (3–5 sentences) |
| **Programs** | Tab switcher (Pre-Primary / Primary) → 7 grade cards total |
| **Gallery** | 3-col desktop / 2-col mobile photo grid → lightbox on click |
| **Admissions** | 4-step numbered process → requirements checklist |
| **Contact** | Embedded map + phone/email (large text) → 3-field form (Name, Email, Message) |

---

## 6. Project Phase Log

| Phase | Status | Output |
|---|---|---|
| 1 — Requirements Audit | ✅ Complete | Resolved age-range conflict, transfer-rule conflict, photo-deadline mitigation plan; flagged hosting as open item |
| 2 — Information Architecture | ✅ Complete | Sitemap, nav structure, Programs tab decision |
| 3 — Design System | ✅ Complete | Color/type/spacing tokens, component rules, contrast fix for Sky Blue |
| 4 — Wireframes | ✅ Complete | Grayscale layouts for Home, Programs, Gallery |
| 5 — Visual Comps | ✅ Complete | Full-color mockups, Home/Programs/Gallery |
| 6 — Prototype | ✅ Complete | Working clickable HTML/CSS/JS prototype, 6 pages |
| 7 — Development | 🔄 In progress | Scaffold rebuilt with placeholder content/images/backend — blocked on real copy (tagline, mission, vision, history), school address/phone/email, established year, hosting confirmation |
| 8 — Accessibility & QA | ⏳ Not started | — |
| 9 — Launch | ⏳ Not started | — |
| 10 — Post-launch | ⏳ Not started | Maintenance ownership not yet assigned |

---

## 7. Open Action Items

1. **Hosting/domain** — needs confirmation from the school (PHP-capable hosting required; static hosts like Netlify/GitHub Pages won't work).
2. **Real content** — tagline, established year, mission/vision/history text, address, phone, email. Currently placeholder throughout the site.
3. **Real photography** — Gallery tiles are generated placeholders; soft target of 30 days post-launch to swap in real photos.
4. **PHPMailer install** — run `composer require phpmailer/phpmailer` inside `includes/` before the contact form can send mail. See `includes/vendor/README.md`.
5. **SMTP / email credentials** — update the `CHANGE_ME` placeholders in `includes/contact-handler.php` once hosting/email is confirmed.
6. **Section 9 enhancements** (optional, not committed scope) — trust-signal section, WhatsApp click-to-chat, Swahili/English toggle, staff/teacher-ratio note, favicon + OG image, Admissions FAQ block.
7. **Maintenance ownership** — who updates the Gallery and checks the contact form inbox post-launch (not yet assigned).

---

*This document should be updated alongside `IMARIKA_WEBSITE_REQUIREMENTS_v3.docx` whenever scope changes.*
