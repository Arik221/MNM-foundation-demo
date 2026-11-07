# MNM Foundation: WordPress website prototype

**Live click-through demo (WordPress Playground, no install):** https://playground.wordpress.net/?blueprint-url=https://raw.githubusercontent.com/Arik221/mnm-foundation-demo/main/playground/blueprint.json
_It runs the real theme and plugin in your browser with demo content and resets on every visit. It needs this repository to be public (raw files must be reachable) and has not been tested yet; first load can take a minute while demo photos are processed. Admin login is automatic._

A native WordPress site (custom theme + small companion plugin) for MNM Foundation, Ukhrul, Manipur.
This is a **client-facing prototype**: polished public pages, a convincing admin dashboard, sample data and
demonstration-mode registration and fundraising. It is not yet a production launch.

| Piece | Folder | What it does |
|---|---|---|
| Theme | `mnm-foundation-theme/` | All visual design: header, footer, homepage, page templates, archives, responsive CSS, self-hosted Manrope and Inter fonts. No data logic. |
| Plugin | `mnm-foundation-core/` | Content types, forms, gallery collections, fundraising, settings, admin dashboard, demo content. Survives theme changes. |
| Installable ZIPs | `dist/` | `mnm-foundation-theme.zip`, `mnm-foundation-core.zip` (Appearance > Themes > Add New > Upload / Plugins > Add New > Upload). |
| Preview helpers | `preview/` | Local-preview scripts, plus screenshots of every page in `preview/screenshots/`. |

Tested with WordPress 7.1.4, PHP 8.3, MariaDB 10.11.

## Preview locally (about 5 minutes)

Pick one. You need a real WordPress install; this repo does not bundle core.

**A. Docker (easiest on a laptop)**
```bash
cd preview && docker compose up -d
```
Open http://localhost:8080 and finish the WordPress installer, then see "Load the demo content" below.
The compose file mounts the theme and plugin straight from this repo.

**B. LocalWP / XAMPP / any WordPress**
1. Create a fresh WordPress site.
2. Copy (or symlink) `mnm-foundation-theme` into `wp-content/themes/` and `mnm-foundation-core` into `wp-content/plugins/`, or upload the two ZIPs from `dist/`.
3. Activate the theme, then the plugin.

**C. Command line, no Docker (what produced the screenshots)**: see `preview/setup-local.sh`
(PHP built-in server + MariaDB).

### Load the demo content
In wp-admin go to **Foundation > Demo Content > Install / refresh demo content**. This creates the pages,
menus, programmes, sample projects/events/articles, 12 gallery photos, two fundraising campaigns and 46
demonstration registrations, and sets the homepage. "Remove demo content" deletes everything flagged as demo.

Local preview scripts create an `admin` user with a generated password (printed once; or set `MNM_ADMIN_PASS`). No passwords are stored in this repository. To also create a limited Editor user for demos, define `MNM_DEMO_STAFF_PASSWORD` in `wp-config.php` before seeding.

## Design notes
Deep teal / coral / sun palette derived from (not copied from) the logo; animated colour-mesh hero with pointer-driven 3D glass cards, floating tab-bar navigation with sliding indicator, tabbed programmes panel, scroll-linked statement text, sticky stacked mission cards, auto-scrolling photo reels, view-transition page changes. All motion is transform/opacity only, pointer-gated, and honours `prefers-reduced-motion`.

## What the client can try
- **Public site:** Home, About (memorial section), Programmes, Projects & Impact (category and location filters, detail pages), Events (upcoming/past), **Marathon** (registration form, live registration count, progress bar, updates, gallery), Gallery (filter + lightbox), News, Contact, Get Involved.
- **Register for the marathon** on `/marathon/`, then see the entry in **Foundation > Registrations** (search, filter by race/status, mark confirmed, bulk delete, **Export CSV**).
- **Fundraising:** Foundation > Fundraising Campaigns > open one > change *Amount raised (manually verified)* > Update. Progress bars update on the site. The site always says the figure is manually verified and flags demo data.
- **Settings:** Foundation > Website Settings (contact details, social links, homepage text and hero photo, announcement bar, impact statistics, marathon details, footer, memorial).
- **Gallery:** Media Library > edit a photo > *Gallery collection* (also under Foundation > Gallery Collections).

## Demonstration mode (important)
Foundation > Website Settings > **Demonstration mode** is on. While on:
- sample content shows "Demonstration data"/"Sample" labels;
- registration and contact forms save/accept entries but **send no email**;
- statistics, campaign amounts, events and projects are **placeholders, not real figures**.
No payments are taken and no donation is verified automatically. Untick it before launch.

## Content that is real vs sample
Real (from the supplied poster/logo): organisation name, address, phone, the three people honoured and their years, mission, vision, dream, programme descriptions.
Sample (replace): all statistics, projects, events, articles, campaign targets/amounts, marathon date/venue/race categories, core-values wording, and every gallery/programme image (soft abstract placeholders tagged "PHOTO PLACEHOLDER"). See `docs/PHOTOS.md` for search keywords and how to drop real photos in.

## Security notes
Nonce on every form, hidden honeypot and a signed time-trap, server-side validation and escaping, prepared queries (WordPress APIs only), registrations stored as a **private** post type never exposed on the site or REST API, capability checks on export/confirm actions, spreadsheet-formula guard in CSV export. Because forms carry time-limited nonces, exclude `/marathon/`, `/contact/` and `/get-involved/` from page caching on Hostinger (the plugin sends no-cache headers for those templates).

## Future: payment integration
Fundraising is intentionally manual today. Planned approach: keep the campaign post type; add a gateway plugin hook (for example Razorpay, the common choice in India) that records each confirmed payment as a child "donation" record and **updates the verified total only after a webhook-verified payment**; keep manual entry as an override with the existing audit label. Registrations would gain a fee and a "paid" status the same way. Nothing in the current data model needs to change.

## Before a production launch (not done in this prototype)
Real photography and copy; confirm marathon details; privacy policy and consent wording review; SMTP/email setup and turning demo mode off; page-cache exclusions; backups and security plugin; remove demo users; set `WP_DEBUG` off; Hostinger SSL and domain; check the Facebook address (below).

## Open question
The brief gives the Facebook page as `.../MNMFOUNDATIONININDIA` but the poster prints `.../MNMFOUNDATIONINDIA`. The prototype uses the **poster** version. Change it in Website Settings > Social media.

See `docs/INSTALL-HOSTINGER.md` for deployment steps and `docs/ADMIN-GUIDE.md` for the staff guide.

## License
Released under the [MIT License](LICENSE) (copyright (c) 2026 Arik221). The theme and the plugin each carry a copy in their own folder.

### Third-party material
- **Fonts:** Manrope and Inter, self-hosted, under the SIL Open Font License 1.1 (licence texts in `mnm-foundation-theme/assets/fonts/`).
- **Photographs** in `mnm-foundation-core/assets/demo/photos/` were supplied as stand-ins for the prototype. Several come from third parties and some carry watermarks or other organisations' branding. They are **not** covered by the MIT licence, and permission must be confirmed (or the photos replaced) before any public launch. See `credits.txt` in that folder.
- WordPress itself is not included; it is licensed under GPL-2.0-or-later.

## Security notes for contributors
No credentials, keys or passwords are stored in this repository. The preview scripts generate an admin password at install time. The demo-content seeder only runs from an administrator-only screen (with a nonce) or the command line, and never creates a user with a preset password. Report anything suspicious by opening an issue.
