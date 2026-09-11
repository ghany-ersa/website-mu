# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## What this project is

**Website-mu** is a multi-tenant no-code website builder / CMS for organizations in the Muhammadiyah ecosystem (Muhammadiyah/Aisyiyah branches, AUM clinics, news portals, mosques). Users pick an organization type and template, assemble pages from a section registry, manage content through a CMS, and publish to a subdomain.

**This is a working Laravel 13 application, not a prototype.** It has ~26 models, ~44 controllers, ~65 migrations, a section-based page builder, a Midtrans-backed subscription system, and subdomain multi-tenancy. The old root-level `*.html` prototypes and the `prompt` file were deleted in `fa9545c` — do not look for them. (Stale copies exist under `.claude/worktrees/prd-masjid-kegiatan/` and are *tracked in git* by accident; ignore that directory entirely.)

The app is feature-complete for its current scope. The remaining work is **filling it with real content/data**, not building new subsystems.

- `prd.md` — Indonesian product brief: vision, segments, roadmap. Long-term direction, deliberately ahead of the code.
- `prd-status.md` — what is actually built, per subsystem. **Read this first** when you need to know whether something exists.
- `README.md` — public-facing summary.

## Commands

```bash
composer run dev                 # server + queue listener + pail logs + vite, concurrently
composer run setup               # install, .env, key, migrate, npm install, npm build
php artisan serve                # Laravel dev server only
php artisan migrate              # sqlite db at database/database.sqlite
php artisan db:seed              # plans, org types, section variants, 10 templates, articles
composer test                    # config:clear, then php artisan test
php artisan test --filter=Name   # single test
./vendor/bin/pint                # code style fixer
npm run dev / npm run build      # Vite
```

## Development approach

- **Mobile-first, always.** Unprefixed Tailwind utilities target mobile; add `sm:`/`md:`/`lg:` for larger screens. Applies to the builder, admin, and tenant template output alike — never design desktop-first and retrofit.
- **Comment the *why*, not the *what*.** This codebase's most distinctive convention: classes, config entries, and non-obvious routes carry long doc comments explaining the *decision* and the alternative rejected. `config/page-builder.php`, `routes/web.php`, `bootstrap/app.php`, `tests/TestCase.php`, and `TenantPageCache` are good examples. Match this when you add code — a future session should not have to re-derive a decision from `git log`.
- **Config-driven over hardcoded.** New sections go in `config/page-builder.php`; new fonts/radii in `config/branding.php`; new section layouts in the `section_variants` table. Adding one should not require touching controllers.

## Architecture

**Frontend build:** Vite + Tailwind v4 (`resources/css/app.css`, `@vite(...)` in layouts). The Tailwind CDN pattern is **gone** — do not reintroduce `cdn.tailwindcss.com`. JS deps: Alpine, TipTap (rich text), SortableJS (reorder), Litepicker, Swiper, driver.js (onboarding tours).

**Multi-tenancy.** Native `Route::domain('{organization_slug}.'.config('tenancy.domain'))`, no third-party package. When `TENANT_DOMAIN` is unset the tenant route group is **never registered**, so local `php artisan serve` behaves normally. Tenant routes use the `tenant` middleware group (`bootstrap/app.php`), *not* `web`: no session/CSRF (pure reads), plus `UseReadOnlyConnection` which swaps to a SELECT-only MySQL connection in production. Rendered tenant pages are cached by `TenantPageCache` (version-counter invalidation, since the file/database cache stores don't support tags); `App\Models\Concerns\InvalidatesTenantPageCache` bumps the version on CMS writes.

**Page builder.** `config/page-builder.php` is the section registry (~26 keys). Per-section flags:
- `locked` — `header`/`footer`; exactly one each, always first/last, never added/deleted/duplicated/reordered.
- `hidden` — under development; hidden from the picker and from public render (currently `jadwal-salat`).
- `exclusive` — requires a plan with `has_exclusive_templates`. The five premium mosque sections (`fasilitas-masjid`, `donasi-progress`, `laporan-keuangan`, `kalkulator-zakat`, `sewa-aula`).
- `cms` — single source of truth mapping a section to the CMS resource backing its `items`.

**Section variants.** Each section renders `templates/sections/{key}/{variant}.blade.php`, resolved through the `section_variants` table (`SectionVariantResolver`), *not* a flat path. Variant-level `is_exclusive` gates which *layout* a section may use; the registry's `exclusive` gates whether the section may be *added at all*. Both are needed for the premium mosque sections.

**Plans & billing.** Three seeded plans (Starter 10k / Organization 18k / Professional 25k per month). Entitlements: `hide_branding`, `has_exclusive_templates`. `PlanLimitService` resolves limits three ways: per-tenant override → paid `limits_snapshot` frozen at approval → live plan limits. Payment is **Midtrans Snap only** (`config/billing.php`, `MidtransWebhookController` — signature-verified, outside `web`); there is no manual bank-transfer flow. Admins bypass payment via the plan-override panel. `Organization::planViolations()` gates publishing.

**Template authoring.** Admins don't hand-write `Template::structure` JSON — `TemplateSandboxService` creates a throwaway `is_sandbox` organization, the admin designs it in the normal builder, then exports back to `structure`. Sandbox orgs are hidden from real tenant listings via `Organization::scopeExcludingSandbox()`.

**Brand tokens.** Every token (colors, font, radius) resolves organization override → template default → platform default. Fonts/radii are whitelisted in `config/branding.php`.

**Storage.** Media on Cloudflare R2 (`MEDIA_DISK=r2`); tests pin `public` via phpunit.xml. Images via Intervention Image v4.

**Seeded data.** 5 organization types in 2 categories (`OrganizationCategory`: Organisasi, Aum — the `Ortom` case is a deprecated alias kept only so old rows cast). Types name the *movement or institution* ("Muhammadiyah", "Klinik/Rumah Sakit"), never the tier or category. 10 templates: a standard + exclusive pair for each of PCM Ambulu, PCA Ambulu, Klinik Aisyiyah, Suara Muhammadiyah, Masjid Nurul Huda. `TemplateSeeder` and `OrganizationSeeder` are intentionally empty/disabled.

**Renaming caution.** `OrganizationTypeSeeder` entries are matched by slug, and template seeders look types up by slug **null-safely** (`$organizationType?->id`) — a stale slug fails *silently*, seeding a template with no type. Rename both sides together.

## Test suite status

`php artisan test` → **2 failed, 2 errors**, the rest passing (~168 tests; the exact total moves with in-flight work). These 4 are pre-existing and reproduce on a clean `HEAD` with the working tree stashed — they are not caused by in-flight work:

- `TenantDetailPagesTest::test_announcement_detail_page_renders` and `::test_agenda_detail_page_renders` — 404. The route matches, but implicit model binding does not substitute: the matched route's params stay `{"agenda":"1"}` as a raw string instead of resolving to a model. The sibling post route passes because it takes a plain string slug and queries manually. Affects `tenant.announcements.show` / `tenant.agendas.show`.
- `OrganizationBrandTest::test_onboarding_checklist_*` — `Organization::phone must return a relationship instance`. `Organization` has both a `phone` column and a `phone()` method; `onboardingChecklist()` reads `filled($this->phone)`. When the attribute isn't loaded (a factory-created instance that never set it), Eloquent falls through to relationship resolution and throws. A `fresh()`ed or route-bound model has the attribute and works — so this is reachable in tests, and in any production path holding an Organization whose `phone` column wasn't selected. The same shadowing exists for `email`, `whatsapp`, `address`, and the four social-URL methods.

When you change code, re-run the suite and compare against this baseline rather than assuming a green suite.
