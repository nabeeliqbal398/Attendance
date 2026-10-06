# Docker Development Setup

Runs Innovexify on Windows (or any OS) with Docker Desktop. No local PHP, Composer, Node or MySQL needed.

```
Browser ──> nginx :8000 ──> php-fpm (app) ──> mysql
Browser ──> vite  :5173   (node container, HMR)
Browser ──> phpMyAdmin :8080 ──> mysql
```

The root `Dockerfile` / `Procfile` are the **production (Railway)** setup and are untouched. Everything here is dev-only (`Dockerfile.dev`, `docker-compose.yml`, `docker/`).

## Requirements

- Docker Desktop (running)
- Git (to clone) — nothing else

## Start

```bash
docker compose build        # first time only (or after Dockerfile.dev changes)
docker compose up -d
```

First start takes several minutes: `composer install`, `npm install`, migrations and seeding all run automatically. Watch it:

```bash
docker compose logs -f app
```

It is ready when you see `ready to handle connections` and http://localhost:8000 loads. (Until then nginx may answer 502.)

Every later start is just `docker compose up -d` (about 30 s; `composer install` re-checks dependencies).

## What startup does (`docker/entrypoint.sh`)

Idempotent, never destructive:

1. Creates `.env` from `.env.example` if missing; generates `APP_KEY` if missing.
2. `composer install`
3. `php artisan migrate --force` (applies only pending migrations)
4. `php artisan db:seed` **only if the `users` table is empty** (`DatabaseSeeder` creates non-unique barcode/shift rows, so re-running it would duplicate them)
5. `php artisan storage:link` if the link is missing

It never runs `migrate:fresh` or `db:wipe`.

Dev overrides (`APP_ENV=local`, `APP_DEBUG=true`, `DB_HOST=mysql`, DB credentials) are set in `docker-compose.yml` and take precedence over `.env`. Your `.env` is therefore safe to keep as-is.

## Check containers / logs

```bash
docker compose ps
docker compose logs -f              # all
docker compose logs -f app          # one service
```

## Laravel commands

Run as `www-data` so created files get the right owner:

```bash
docker compose exec -u www-data app php artisan migrate
docker compose exec -u www-data app php artisan migrate:status
docker compose exec -u www-data app php artisan db:seed
docker compose exec -u www-data app php artisan storage:link
docker compose exec -u www-data app php artisan tinker
```

Optional demo data (not called by `DatabaseSeeder`):

```bash
docker compose exec -u www-data app php artisan db:seed --class=FakeDataSeeder
```

## Composer / NPM

```bash
docker compose exec -u www-data app composer install
docker compose exec -u www-data app composer require vendor/package
docker compose exec node npm install --no-package-lock
```

The `node` service runs `npm install` then `npm run dev` (Vite) automatically. `vendor/` and `node_modules/` live in named Docker volumes (fast on Windows), so they will not appear in your Windows folder.

The repo has no `package-lock.json` (only `bun.lock`), so `npm install` resolves from the `^` ranges in `package.json`.

## Stop

```bash
docker compose down
```

This **keeps** the database (`mysql-data` volume), `vendor` and `node_modules`. Never add `-v` unless you mean to wipe data.

## Reset database (DESTRUCTIVE — dev data is lost)

Only run this on purpose:

```bash
docker compose down -v          # deletes mysql-data, vendor and node_modules volumes
docker compose up -d            # re-migrates and re-seeds from scratch
```

## URLs and ports

| What | URL |
|---|---|
| Laravel app | http://localhost:8000 |
| Vite dev server | http://localhost:5173 (browser loads assets and HMR from here) |
| phpMyAdmin | http://localhost:8080 (auto-login as root) |
| MySQL from Windows tools | `127.0.0.1:3307`, user `root` |

Change the defaults by creating a `.env` value or environment variable: `APP_PORT`, `DB_PASSWORD` (default `secret`, dev only), `DB_DATABASE`, `DB_FORWARD_PORT`.

## Login credentials (from `database/seeders/AdminSeeder.php`)

With `SEED_ADMIN_PASSWORD` empty and a local environment, the seeder uses the password `password`:

| Account | Email | Password |
|---|---|---|
| Super admin | `superadmin@example.com` | `password` |
| Admin | `admin@example.com` | `password` |

If you set `SEED_ADMIN_PASSWORD` in `.env` **before the first seed**, that value is used instead. Demo users (`user@example.com`, `head.<division>@example.com`, …) exist only after running `FakeDataSeeder`.

## Timezone

`.env.example` previously defaulted to `Asia/Baghdad`; it is now `Asia/Karachi` (matches the Pakistan-oriented setup). PHP's `date.timezone` in `docker/php/dev.ini` is also `Asia/Karachi`. If your existing `.env` still says Baghdad, change `APP_TIMEZONE` and run `docker compose restart app`.

## Feature notes: GPS, camera, QR, face

How the existing app implements them (nothing was changed):

| Feature | Implementation | Needs |
|---|---|---|
| GPS / geofence | `navigator.geolocation` in the browser; `@capacitor/geolocation` in the Android app | Secure context in browsers |
| Camera / selfie | `navigator.mediaDevices.getUserMedia` (browser); Capacitor Camera (Android) | Secure context in browsers |
| QR scan | Bundled `public/assets/js/html5-qrcode.min.js` + camera | Secure context in browsers |
| Face ID | `face-api.js`; the UI marks it an **Enterprise (license-gated) feature**. The enrollment page loads models from `cdn.jsdelivr.net` | Secure context + internet |
| Mock-location detection | Capacitor plugin | Android app only (not available in a browser) |
| Maps | Leaflet from `unpkg.com`, OpenStreetMap tiles | Internet |

**A. PC / browser testing:** `http://localhost:8000` is treated as a secure context by browsers, so GPS and camera work here (the browser asks for permission). Desktop GPS is IP/Wi-Fi based and imprecise; use Chrome DevTools → Sensors to fake a location inside a geofence.

**B. Real phone, browser/PWA:** `http://<your-PC-LAN-IP>:8000` is **not** a secure context, so browsers block GPS and camera. Use HTTPS (below).

**C. HTTPS requirement:** needed for any non-`localhost` browser access to GPS/camera/QR/face.

**D. localhost exception:** only applies to the same machine (`localhost` / `127.0.0.1`). It does not apply to a phone reaching your PC.

## Android testing

`capacitor.config.ts` has `server.url` hard-coded to the **production Railway URL** (`https://innovexify-app-production.up.railway.app`). The installed Android app therefore loads production, **not** your Docker app. `localhost` on a phone means the phone itself, so `http://localhost:8000` will never reach your PC. The config has not been modified.

To test the Docker app on a real device over HTTPS:

1. Start a tunnel to the app, for example Cloudflare Tunnel:
   ```bash
   cloudflared tunnel --url http://localhost:8000
   ```
   (or `ngrok http 8000`). Note the `https://….trycloudflare.com` URL.
2. Tell Laravel its public URL, then restart:
   set `APP_URL=https://<tunnel-url>` in `.env` and run `docker compose up -d` (compose reads `APP_URL` from the shell/`.env` for the app container).
3. **Quickest:** open the tunnel URL in the phone's Chrome. Login, GPS, camera and QR work over HTTPS.
4. **To test the Capacitor APK against it:** temporarily set `server.url` in `capacitor.config.ts` to the tunnel URL, then
   ```bash
   npm run build && npx cap sync android
   ```
   and rebuild/install the APK. Revert `server.url` afterwards.

Vite note: with the dev server running, pages reference `http://localhost:5173` for assets, which a phone can't reach. For phone testing either run `npm run build` once (creates `public/build`, and stop the `node` service so `public/hot` is removed), or accept the browser-only PC workflow for UI work.

The Android emulator can reach the host PC at `http://10.0.2.2:8000`, but browser-API features still prefer HTTPS.

## Troubleshooting

- **502 Bad Gateway right after start/restart:** php-fpm is still booting (composer/migrate). Wait for `ready to handle connections` in `docker compose logs app`. nginx re-resolves the app container automatically, so no nginx restart is needed.
- **Vite assets not loading:** check `docker compose logs node`; `public/hot` must contain `http://localhost:5173`.
- **Port in use:** change `APP_PORT` / `DB_FORWARD_PORT` as above.
- **`/storage/...` files 404:** `public/storage` must be a symlink (`docker compose exec -u www-data app php artisan storage:link`); nginx mounts `storage/app/public` for it.
- **Slow first request on Windows:** project files are a bind mount; keeping the repo on the WSL2 filesystem is faster if it becomes an issue.
