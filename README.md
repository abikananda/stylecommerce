# Jewellery store

Laravel 13 storefront and admin for earrings and bangles, with per-variant stock, guest checkout, a Razorpay payment path, and configurable store details. The demo catalogue and hero photo are illustrative; replace the products, descriptions and images with your real merchandise before publishing.

## Local setup

Requirements: PHP 8.3+, Composer 2, Node 22+, MySQL 8+, and a mail transport. Copy `.env.example` to `.env`; set database credentials, `APP_URL`, `ADMIN_EMAIL`, and a strong `ADMIN_PASSWORD` before seeding. Never commit `.env`.

```bash
composer install
npm install
php artisan key:generate
php artisan migrate --seed
php artisan storage:link
npm run build
php artisan serve
```

In separate shells run `php artisan queue:work` and `php artisan schedule:work`. Open `http://localhost:8000`. The admin login is the email and password supplied for seeding, and the admin UI is at `/admin`. Re-running the seeder does not overwrite an existing admin password.

Alternatively, after setting `.env` and `MYSQL_ROOT_PASSWORD`, run `docker compose build`, `docker compose run --rm app php artisan key:generate`, `docker compose up -d`, `docker compose exec app php artisan migrate --seed`, and `docker compose exec app php artisan storage:link`. Use `http://localhost:8000`. Production needs TLS, durable MySQL and image storage, backups, a mail service, and process supervision for the worker and scheduler.

## Payments

Set `RAZORPAY_KEY_ID`, `RAZORPAY_KEY_SECRET`, and `RAZORPAY_WEBHOOK_SECRET` to **test-mode** credentials first. Configure the webhook URL `https://YOUR_DOMAIN/payments/razorpay/webhook` for `payment.captured`. Orders are created server-side in paise; the checkout signature, webhook signature, fetched payment state, order ID, amount and INR currency are checked before an order becomes paid. A browser redirect alone does not confirm an order. A queued job checks expired 15-minute stock reservations against Razorpay before releasing them. Keep the scheduler and queue worker running. A payment captured after an order has already been cancelled needs manual reconciliation/refund; investigate these cases in the Razorpay dashboard before fulfilling.

The optional COD setting is off by default. When enabled, COD orders move to processing and reduce stock immediately; the amount is due on delivery. Confirm the displayed shipping, tax and final amount with customers before fulfilling COD orders.

## Tests

Run `php artisan test` and `npm run build`. The feature tests use SQLite in memory and fake Razorpay HTTP responses. GitHub Actions runs both commands on pushes and pull requests. Real gateway test credentials and an HTTPS webhook endpoint are still required for an end-to-end Razorpay test transaction.

## Before launch

- Add the actual brand name, contact information, legal pages, return terms, product images, descriptions, stock, shipping coverage and fulfilment process in admin.
- Confirm GST treatment, tax rates, invoice fields and refund policy with your accountant. Tax is configurable in basis points; the demo starts at zero until you set the correct rate.
- Supply a mail transport, Razorpay live credentials, webhook secret, a domain with HTTPS, persistent media storage, and a compatible PHP/MySQL deployment host.
- Check real product packaging, delivery rates and serviceability; review accessibility and the checkout in a real browser on mobile and desktop.

This project has not been deployed from the authoring workspace because it lacks PHP/Composer and live gateway credentials. GitHub Actions provides an independent build/test check once pushed.
