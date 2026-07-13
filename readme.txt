=== GoatCounter Analytics ===
Contributors: linuxbcn
Tags: analytics, statistics, privacy, goatcounter, gdpr
Requires at least: 6.0
Tested up to: 7.0
Requires PHP: 7.4
Stable tag: 1.2.0
License: GPL-2.0-or-later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Privacy-friendly analytics via GoatCounter. No cookies, no GDPR banner needed. Full stats dashboard in your WordPress admin.

== Description ==

Integrate [GoatCounter](https://www.goatcounter.com) into your WordPress site with a clean admin dashboard showing visits, pages and device breakdown — all without cookies, without tracking personal data, and without needing a GDPR consent banner.

**Why GoatCounter?**

* No cookies — no GDPR/cookie banner required
* Lightweight — single async script, zero performance impact
* Open source and privacy-respecting
* Free for up to 100,000 pageviews/month

**Dashboard features**

* Three tabs: **Temporal** (time series), **Pages** (top content), **Devices** (browsers, OS, countries)
* Five time periods: today, 7 days, 30 days, quarter (90 days), year (365 days)
* SVG bar chart of visits per hour / day / week / month depending on period
* Day-of-week distribution with percentage and visit count
* Top pages with WordPress page title lookup and direct links
* Browsers and operating systems with percentage bars
* Countries breakdown with percentage bars
* Summary metric cards (total visits, pages, countries, top browser)
* Remembers the active tab across page reloads (localStorage)
* Available in English and Catalan

**Setup in 3 steps**

1. Create a free account at [goatcounter.com](https://www.goatcounter.com)
2. Go to **Analytics → Settings** and enter your site code and API token
3. Done — the tracking script is injected automatically and stats appear in the dashboard

== Installation ==

1. Download the plugin ZIP file from the GitHub releases page or via Code → Download ZIP.
   Note for macOS users: Safari automatically unzips downloaded files and moves the ZIP to the trash. To keep the ZIP file, right-click the download link and choose "Download Linked File", or use a different browser such as Chrome or Firefox.
2. In your WordPress admin, go to Plugins → Add New → Upload Plugin.
3. Select the `.zip` file and click Install Now, then Activate.
4. Go to Analytics → Settings and enter your GoatCounter credentials.

== Frequently Asked Questions ==

= Do I need a GoatCounter account? =
Yes. Create a free account at [goatcounter.com](https://www.goatcounter.com). It's free for up to 100,000 pageviews/month.

= Where do I find my site code? =
It's the subdomain of your GoatCounter dashboard URL. If your dashboard is at `mysite.goatcounter.com`, your site code is `mysite`.

= Where do I find my API token? =
In your GoatCounter dashboard: **Settings → API tokens → Create token**.

The API token section only appears after you have **confirmed your email address**. When you register at goatcounter.com, they send a confirmation email — check your spam folder if you don't see it. The API options won't show up until your account is confirmed.

= Does this plugin set cookies? =
No. GoatCounter does not use cookies and does not track personal data.

= Do I need a cookie consent banner? =
No. Since GoatCounter does not use cookies or collect personal data, no GDPR consent banner is required.

= Is this plugin free? =
Yes. The plugin is free and open source (GPL-2.0). GoatCounter itself is also free for personal and low-traffic use.

== Screenshots ==

1. Dashboard — Temporal tab: period selector, summary cards, SVG bar chart, day-of-week distribution
2. Dashboard — Pages tab: top pages with title, link and percentage bars
3. Dashboard — Devices tab: browsers, operating systems and countries
4. Settings page with step-by-step setup instructions

== Changelog ==

= 1.2.0 =
* Redesigned dashboard with three tabs: Temporal, Pages, Devices
* New periods: quarter (90 days) and year (365 days)
* SVG bar chart with gradient, grid lines and adaptive granularity (hour/day/week/month)
* Day-of-week distribution with percentage and visit count
* Top pages with WordPress page title lookup and clickable links
* Browsers, OS and countries with percentage bars
* Period selector redesigned as a full-width segmented nav
* Summary metric cards with icons
* Active tab persisted via localStorage across page reloads
* Catalan day names (Dl–Dg) in weekly chart

= 1.1.0 =
* Stats dashboard with top pages, referrers and countries
* Period filter: today / 7 days / 30 days
* Catalan translation

= 1.0.0 =
* Initial release — tracking script injection and settings page

== Upgrade Notice ==

= 1.2.0 =
Major dashboard redesign. Three tabs, five periods, SVG chart, day-of-week distribution.
