# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## What this project is

**Website-mu** is a multi-tenant no-code website builder / CMS for organizations in the Muhammadiyah ecosystem (PDM/PCM/PRM branches, Ortom like Aisyiyah/IMM/IPM, AUM schools and clinics, mosques). Users pick an organization type and template, assemble pages via drag-and-drop components, manage content through a simple CMS, and publish to a subdomain or custom domain. See `prd.md` for the full Indonesian-language product brief (goals, target segments, UX flow, template library).

The repo is currently at an early/prototyping stage: it's a stock Laravel 13 skeleton (no custom routes, controllers, or models beyond the default `User`) plus a set of **standalone static HTML prototypes** at the repo root exploring landing-page design directions before they get built into the real app:

- `landingpage websitemu.html` - the main marketing landing page for the website-mu platform itself. This is mirrored almost verbatim into `resources/views/welcome.blade.php` (the actual Laravel-served homepage) - when editing one, check whether the other needs the same change.
- `index.html` - an earlier/alternate version of the platform landing page.
- `PCA.html`, `PCM.html`, `PCM 2.html`, `PCM 3.html` - prototype *tenant* sites (example output a real organization would get), specifically for "PCM Ambulu" (Pimpinan Cabang Muhammadiyah Ambulu). Multiple numbered variants are alternate design explorations, not sequential versions to build on.
- `prompt` - the design brief/prompt (Indonesian) used to AI-generate the PCM Ambulu prototypes: brand colors, tone, content requirements. Useful reference when creating new tenant-site prototypes or templates.

All prototypes are self-contained HTML using the Tailwind CDN build (`<script src="https://cdn.tailwindcss.com">`), Google Fonts, and Swiper.js from CDN - no build step. The Muhammadiyah brand palette used throughout: primary blue `#2C368B`, secondary green `#079C4E`.

The real app (routes, models, migrations, Vite-built Tailwind) has not been built out yet beyond Laravel defaults - expect to build multi-tenancy, organization/template/page/content models, and the drag-and-drop editor from scratch when that work starts.

## Commands

Backend (PHP/Laravel):
```bash
composer install                 # install PHP deps
composer run dev                 # run server + queue listener + logs (pail) + vite, concurrently
php artisan serve                # Laravel dev server only
php artisan migrate              # run migrations (sqlite db at database/database.sqlite)
composer test                    # clears config cache, then runs php artisan test
php artisan test --filter=Name   # run a single test
php artisan test tests/Feature/ExampleTest.php  # run a single test file
./vendor/bin/pint                # code style fixer (Laravel Pint)
```

Frontend (Vite/Tailwind for the compiled asset pipeline, used by Blade views):
```bash
npm install
npm run dev                      # vite dev server
npm run build                    # production build
```

There is no build step for the root-level standalone `*.html` prototype files - open them directly in a browser.

## Architecture notes

- Standard Laravel structure: `app/Http/Controllers`, `app/Models`, `routes/web.php`, `resources/views` (Blade), `database/migrations`. Currently minimal - only the framework-provided `User` model/migration and a single `/` route rendering `welcome.blade.php`.
- DB is SQLite (`database/database.sqlite`) per `.env` (`DB_CONNECTION=sqlite`).
- Blade views in this app use the Tailwind CDN + inline `tailwind.config` script pattern (see `welcome.blade.php`), matching the root HTML prototypes, rather than the Vite-compiled `resources/css/app.css` - reconcile this if/when the app moves off prototype-style pages.
- When asked to build out real platform features (organizations, templates, components, publishing), treat `prd.md` as the source of truth for scope, terminology, and user flow, and the root `*.html` files / `prompt` as visual/content reference for what generated tenant sites should look like - not as code to be reused directly.
