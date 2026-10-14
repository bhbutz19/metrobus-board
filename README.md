# Metrobus Split-Flap Departure Board

**Working baseline — October 13, 2026.** User-confirmed operational at https://bus.cowboybutz.com.

A mechanical-style departure board powered by WMATA's live predictions and published stop schedules.

## Baseline functionality
- Enter a **seven-digit WMATA stop ID** in Settings; it persists in the browser. Default: **1001453**.
- Retrieve and display the stop's actual name, available routes/destinations, and three upcoming arrivals.
- Live arrival predictions shown in minutes; scheduled-only times shown as **h:mmS**; **NO ETA** when no prediction is available.
- Configurable walk time (default 5 minutes) and safety buffer (default 1 minute).
- Departure warning uses **live predictions**, not scheduled-only times.
- Mechanical split-flap display, including animated empty tiles, with generated clacking sound and a mute toggle.
- Manual Refresh Live triggers the mechanical cycle; automatic data updates continue while the page is open.

## Architecture
- `index.html`: browser board and settings.
- `api/arrivals.php`: real-time arrivals, filtered by validated stop ID.
- `api/schedule.php`: published stop schedule (different from live predictions).
- `api/stop.php`: WMATA stop-name lookup.
- `.cpanel.yml`: deploys site and endpoints to `/home/cowboybu/public_html/bus.cowboybutz.com/`.

## Deployment
1. cPanel > Git Version Control > `metrobus-board` > **Update from Remote** > **Deploy HEAD Commit**.
2. Reload https://bus.cowboybutz.com (hard-refresh if necessary).
3. The private WMATA API key resides at `/home/cowboybu/.wmata_bus_key` **outside** `public_html`, or in the `WMATA_API_KEY` server environment variable. **Never commit the key.**

## Notes
- Live and scheduled trips are deduplicated by trip ID or approximate time; matching is best-effort.
- The website backend makes authenticated WMATA API requests. Browser-side code does not contain the secret.
- The user confirmed that the site, audio, live predictions, scheduled arrivals, stop switching, and stop-name headings work. Not all edge cases have been formally tested.
- Create future improvements on `main`; the `baseline-2026-10-13` branch preserves this state.
