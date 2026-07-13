# GoatCounter Analytics for WordPress

Privacy-friendly analytics via [GoatCounter](https://www.goatcounter.com). No cookies, no GDPR banner needed. Full stats dashboard in your WordPress admin.

## Features

- **No cookies** — no GDPR/cookie consent banner required
- **Lightweight** — single async script, zero performance impact
- **Dashboard with three tabs**: Temporal, Pages, Devices
- **Five time periods**: today, 7 days, 30 days, quarter (90 days), year (365 days)
- **SVG bar chart** with gradient, grid lines and adaptive granularity (hour/day/week/month)
- **Day-of-week distribution** with percentage and visit count
- **Top pages** with WordPress page title lookup and direct links
- **Browsers, OS and countries** with percentage bars
- **Summary metric cards** (total visits, pages, countries, top browser)
- **Active tab persisted** via localStorage across page reloads
- Available in **English and Catalan**

## Screenshots

![Temporal tab — period selector, summary cards, SVG chart and day-of-week distribution](assets/screenshots/screenshot-1.png)

![Pages tab — top pages with title, link and percentage bars](assets/screenshots/screenshot-2.png)

![Devices tab — browsers, operating systems and countries](assets/screenshots/screenshot-3.png)

![Settings page](assets/screenshots/screenshot-4.png)

## Requirements

- WordPress 5.9+
- PHP 7.4+
- A free [GoatCounter](https://www.goatcounter.com) account

## Installation

1. Download the plugin zip or clone this repository into `wp-content/plugins/goatcounter-wp/`
2. Activate the plugin through the **Plugins** menu in WordPress
3. Go to **Analytics → Settings** and enter your GoatCounter site code and API token

## Setup

1. Create a free account at [goatcounter.com](https://www.goatcounter.com)
2. Go to **Analytics → Settings** in your WordPress admin
3. Enter your **site code** (the subdomain of your GoatCounter URL — e.g. `mysite` if your dashboard is at `mysite.goatcounter.com`)
4. Enter your **API token** (GoatCounter dashboard → Settings → API tokens → Create token)

The tracking script is injected automatically in the footer of every page.

## Privacy

GoatCounter does not use cookies, does not track personal data, and does not require a GDPR consent banner. All analytics data is stored on GoatCounter's servers (or your own if self-hosted).

## Changelog

### 1.2.0
- Redesigned dashboard with three tabs: Temporal, Pages, Devices
- New periods: quarter (90 days) and year (365 days)
- SVG bar chart with gradient, grid lines and adaptive granularity (hour/day/week/month)
- Day-of-week distribution with percentage and visit count
- Top pages with WordPress page title lookup and clickable links
- Browsers, OS and countries with percentage bars
- Period selector redesigned as a full-width segmented nav
- Summary metric cards with icons
- Active tab persisted via localStorage across page reloads
- Catalan day names (Dl–Dg) in weekly chart

### 1.1.0
- Stats dashboard with top pages, referrers and countries
- Period filter: today / 7 days / 30 days
- Catalan translation

### 1.0.0
- Initial release — tracking script injection and settings page

## License

GPL-2.0-or-later — see [LICENSE](https://www.gnu.org/licenses/gpl-2.0.html)

## Author

Built by [LinuxBCN](https://linuxbcn.com) for [112books.eu](https://112books.eu).
