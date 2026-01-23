# Kukija Deployment Notes (XAMPP + subfolder `/kukija`)

## Assumptions
- Your project lives at: `c:\xampp\htdocs\WebDev\kukija`
- Your site is served under: `http://localhost/kukija`

## 1) Apache / Document Root
- Make sure Apache serves `htdocs` and that you access the app via:
  - `http://localhost/kukija`

## 2) `.env` critical values
- `APP_URL=http://localhost/kukija`
- `VITE_BASE_URL=/kukija/`

If `VITE_BASE_URL` is missing or incorrect, the SPA router and asset paths can break.

## 3) Build assets
From `c:\xampp\htdocs\WebDev\kukija`:
- `npm install`
- `npm run build`

## 4) Laravel caches
Whenever you change routes/config:
- `php artisan optimize:clear`

## 5) API routing under subfolder
This project supports both:
- `/api/*`
- `/kukija/api/*`

The `/kukija/api/*` alias exists to make XAMPP subfolder deployments reliable.

## 6) Sanctum tokens
- Storefront customer auth uses Bearer token stored in `localStorage` as `kukija_customer_token`.
- Admin auth uses `kukija_admin_token`.

## 7) Common “blank page” checks
- Confirm `@vite([...])` output exists in `public/build` after build.
- Confirm `VITE_BASE_URL=/kukija/`.
- Confirm API returns JSON:
  - `http://localhost/kukija/api/products`

