# Admissions Frontend

## Stack

Laravel 13 Blade, Tailwind CSS 4, TypeScript compiled by Vite, Lucide icons, PHPUnit and Playwright. MEMA design tokens use primary `#0A3E50`, secondary `#1E8449`, accent `#E67E22` and white `#FFFFFF`.

## Development

```bash
php artisan serve --host=127.0.0.1 --port=8000
npm run dev
```

Demo users use password `password`: `admin@mema.ac.ke`, `teacher@mema.ac.ke`, `dit0012026@student.mema.ac.ke`, and `parent@mema.ac.ke`.

## Tests

```bash
php artisan test
./vendor/bin/pint --test
npm run build
npm run test:e2e
```

The public catalogue begins at `/programmes`; applicant routes use `/applicant/*`; staff workspaces use `/admissions/*`. Missing REST contracts and production limitations are tracked in `FRONTEND_API_GAPS.md`.
