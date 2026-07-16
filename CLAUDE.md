# LinuxBCN Analytics for GoatCounter — WordPress Plugin

## Plugin Identity

- **Plugin Name:** LinuxBCN Analytics for GoatCounter
- **Slug:** `linuxbcn-analytics-for-goatcounter`
- **Version:** 1.2.0
- **Author:** LinuxBCN (112books.eu)
- **License:** GPL-2.0-or-later
- **Text Domain:** `linuxbcn-analytics-for-goatcounter`
- **WordPress.org URL:** https://wordpress.org/plugins/linuxbcn-analytics-for-goatcounter
- **SVN URL:** https://plugins.svn.wordpress.org/linuxbcn-analytics-for-goatcounter
- **GitHub:** git@github.com:112books/goatcounter-wp.git (branch: main)

## Project Purpose

Integrates [GoatCounter](https://www.goatcounter.com/) privacy-friendly analytics into WordPress. No cookies, no GDPR banner needed. Provides a full analytics dashboard inside the WordPress admin panel.

## File Structure

```
goatcounter.php               Main plugin file (entry point, constants, script injection)
uninstall.php                 Cleans up wp_options on uninstall
readme.txt                    WordPress.org directory readme (standard format)
README.md                     Project docs (bilingual: English + Catalan)
includes/
  class-admin.php             Admin UI, dashboard, settings (609 lines)
  class-api.php               GoatCounter API client wrapper (66 lines)
assets/
  admin.css                   Dashboard CSS (166 lines, color: #D51F17 red)
  screenshots/                screenshot-1.png to screenshot-4.png
languages/
  linuxbcn-analytics-for-goatcounter-ca.po   Catalan source (140+ strings)
  linuxbcn-analytics-for-goatcounter-ca.mo   Compiled Catalan binary
.github/workflows/
  deploy-to-wporg.yml         GitHub Actions: auto-deploy to SVN on tag push
```

## WordPress Options

- `gcwp_site_code` — GoatCounter subdomain (e.g., "mysite")
- `gcwp_api_token` — GoatCounter API Bearer token

## Constants

```php
GCWP_VERSION  // '1.2.0'
GCWP_DIR      // Plugin directory absolute path
GCWP_URL      // Plugin URL
```

## External Services

- **Tracking script:** `https://gc.zgo.at/count.js` (injected on frontend, async)
- **Tracking endpoint:** `https://{site}.goatcounter.com/count`
- **API base:** `https://{site}.goatcounter.com/api/v0`
  - Endpoints used: `/stats/total`, `/stats/hits`, `/stats/browsers`, `/stats/systems`, `/stats/locations`

## Admin Dashboard Features

- 5 time periods: day, week (7d), month (30d), quarter (90d), year (365d)
- 4 summary metric cards: total visits, page count, countries, top browser
- 3 tabs (persisted via localStorage):
  - **Temporal:** SVG bar chart + day-of-week distribution
  - **Pages:** Top 15 pages with WordPress title lookup
  - **Devices:** Browsers (top 8), OS (top 8), Countries (top 15)
- SVG charts: custom-rendered, responsive, red gradient (`#D51F17`)
- Catalan day labels: Dl, Dt, Dc, Dj, Dv, Ds, Dg

## Deployment: GitHub Actions → SVN

**Trigger:** Git tag push matching `v*` (e.g., `git tag v1.2.0 && git push --tags`)

**GitHub Secrets required:**
- `SVN_USERNAME` = `linuxbcn`
- `SVN_PASSWORD` = (WordPress.org SVN password, set at wordpress.org profile → Account & Security)

**Action used:** `10up/action-wordpress-plugin-deploy@stable`

**Manual SVN workflow (if needed):**
```bash
svn co https://plugins.svn.wordpress.org/linuxbcn-analytics-for-goatcounter svn-repo
# Copy plugin files to svn-repo/trunk/
# svn add new files, svn delete removed files
svn ci -m "Update to 1.2.0" --username linuxbcn
# Tag the release:
svn cp trunk tags/1.2.0
svn ci -m "Tag 1.2.0" --username linuxbcn
```

## Requirements

- WordPress 6.0+
- PHP 7.4+
- Free GoatCounter account (https://www.goatcounter.com)

## Internationalization

- Text domain: `linuxbcn-analytics-for-goatcounter`
- Domain path: `/languages`
- Catalan (ca) translation: 100% complete
- Add new languages by creating `.po`/`.mo` files in `/languages/`

## Security Patterns

- Settings sanitized with `sanitize_text_field()`
- Output escaped with `esc_attr()`, `esc_html()`, `esc_url()`
- SVG output sanitized with `wp_kses()` + custom whitelist (`svg_allowed_html()`)
- Admin pages protected with `manage_options` capability
- API token stored as WordPress option (password input in UI)

## Key Classes

### `GCWP_Admin` (includes/class-admin.php)
Handles: menu registration, settings, asset enqueuing, dashboard rendering, SVG chart generation, API data display.

### `GCWP_API` (includes/class-api.php)
Handles: HTTP requests to GoatCounter API, date range calculation, response parsing.

## Changelog

- **1.2.0** — Major redesign: 3 tabs, 5 periods, SVG bar chart, day-of-week distribution, localStorage tab persistence
- **1.1.0** — Initial dashboard with period filter
- **1.0.0** — Initial release (tracking script injection only)

## Estat actual

- **v1.2.0 publicada** al directori oficial de WordPress.org (2026-07-16)
- Plugin URL: https://wordpress.org/plugins/linuxbcn-analytics-for-goatcounter/
- Perfil autor: https://profiles.wordpress.org/linuxbcn/
- Deploy via GitHub Actions (tag `v1.2.0` → SVN automàtic)

## Pendent

- **Traducció catalana a translate.wordpress.org** — el projecte es crea automàticament però pot trigar 24h. Recordatori al calendari per 2026-07-17. URL: https://translate.wordpress.org/projects/wp-plugins/linuxbcn-analytics-for-goatcounter/
- **Assets de WordPress.org** (banner 1544×500, icona 256×256) — opcionals però recomanats per millorar la presència al directori. Van a SVN `/assets/`, no a `/trunk/`.

## Development Notes

- No build step, no npm, no Composer — pure PHP/CSS/JS
- Screenshots go in `assets/screenshots/` (WordPress.org reads them from SVN `assets/` folder, NOT from `trunk/assets/`)
- WordPress.org assets (banner, icon, screenshots) live in SVN `/assets/` at the root, separate from `/trunk/`
- The `.github/` folder is excluded from SVN deployment (action handles this automatically)
