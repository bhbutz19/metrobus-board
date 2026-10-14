# Metrobus Departure Board

Vintage-style browser departure board for the **C91 westbound** at WMATA stop **1001453**, with a five-minute walking countdown and adjustable buffer.

## Development

Open `index.html` in your browser for demo mode. Live mode requires PHP hosting with `api/arrivals.php`; GitHub Pages alone does not execute PHP.

## Deployment to 2M Hosting cPanel

1. Create `bus.cowboybutz.com` in cPanel **Domains**, with a separate document root. Confirm its exact path.
2. Edit `.cpanel.yml`: replace `CPANEL_USERNAME` and change `DEPLOYPATH` to the verified subdomain document root. **Do not deploy until that path is correct.**
3. In cPanel **Git Version Control**, clone `https://github.com/bhbutz19/metrobus-board.git` to a non-public directory such as `/home/USERNAME/repositories/metrobus-board`.
4. Use **Update from Remote** and **Deploy HEAD Commit**.
5. Create `/home/USERNAME/.wmata_bus_key` in cPanel File Manager, holding only your WMATA subscription key. Keep it outside the website directory and use restrictive permissions. Alternatively provide the `WMATA_API_KEY` PHP environment variable.
6. Visit `https://bus.cowboybutz.com/`. Test demo mode, then **Connect Live**.

The public endpoint is not authenticated. Consider caching/rate-limiting to manage API quota before sharing publicly. The current direction filter requires WMATA's westbound direction text; if no predictions appear when buses are listed on busETA, inspect the response and adjust the filter.

Never commit the real WMATA key.
