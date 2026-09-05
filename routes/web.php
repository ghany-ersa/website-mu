<?php

use App\Http\Controllers\Admin\ArticleController as AdminArticleController;
use App\Http\Controllers\Admin\ArticleImageController;
use App\Http\Controllers\Admin\DiscountCodeController;
use App\Http\Controllers\Admin\OrganizationController as AdminOrganizationController;
use App\Http\Controllers\Admin\PlanChangeRequestController;
use App\Http\Controllers\Admin\PlanController as AdminPlanController;
use App\Http\Controllers\Admin\SectionVariantController;
use App\Http\Controllers\Admin\SectionVariantPreviewController;
use App\Http\Controllers\Admin\TemplateController as AdminTemplateController;
use App\Http\Controllers\ArticleController;
use App\Http\Controllers\MediaController;
use App\Http\Controllers\MidtransWebhookController;
use App\Http\Controllers\OrganizationAgendaController;
use App\Http\Controllers\OrganizationAnnouncementController;
use App\Http\Controllers\OrganizationBrandController;
use App\Http\Controllers\OrganizationBuilderController;
use App\Http\Controllers\OrganizationController;
use App\Http\Controllers\OrganizationDonationProgramController;
use App\Http\Controllers\OrganizationDonationTransactionController;
use App\Http\Controllers\OrganizationEditController;
use App\Http\Controllers\OrganizationFacilityController;
use App\Http\Controllers\OrganizationFinancialReportController;
use App\Http\Controllers\OrganizationGalleryController;
use App\Http\Controllers\OrganizationMemberController;
use App\Http\Controllers\OrganizationNetworkController;
use App\Http\Controllers\OrganizationOfficerController;
use App\Http\Controllers\OrganizationPageController;
use App\Http\Controllers\OrganizationPlanController;
use App\Http\Controllers\OrganizationPostController;
use App\Http\Controllers\OrganizationProgramController;
use App\Http\Controllers\OrganizationSectionController;
use App\Http\Controllers\OrganizationSiteController;
use App\Http\Controllers\OrganizationTemplateController;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\TemplateController;
use App\Http\Controllers\TemplatePreviewController;
use App\Http\Controllers\TemplateUseController;
use App\Models\Article;
use App\Models\Plan;
use App\Models\Template;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    // Curated set, not the full catalog: one representative per organization-type grouping
    // so the homepage grid stays a short, skimmable preview instead of all ~13 templates
    // (most of which only differ from a sibling by brand color/copy - see templates.index for
    // the full, filterable list). 'muhammadiyah-eksekutif' stands in for Persyarikatan instead
    // of the standard 'muhammadiyah' template, since both target the same PDM/PCM/PRM audience
    // and showing both here would look like duplication rather than distinct choices.
    $homepageTemplateSlugs = [
        'muhammadiyah-eksekutif',
        'pemuda-muhammadiyah',
        'aum-pendidikan',
        'aum-kesehatan-sosial',
        'masjid-mushola',
    ];

    $templates = Template::where('is_active', true)
        ->whereIn('slug', $homepageTemplateSlugs)
        ->get()
        ->sortBy(fn ($template) => array_search($template->slug, $homepageTemplateSlugs))
        ->values();

    $plans = Plan::with('limits')
        ->where('is_active', true)
        ->orderBy('price_monthly')
        ->get();

    $articles = Article::published()
        ->orderByDesc('published_at')
        ->take(4)
        ->get();

    return view('welcome', ['templates' => $templates, 'plans' => $plans, 'articles' => $articles]);
})->name('home');

Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap');

Route::get('/berita', [ArticleController::class, 'index'])->name('articles.index');
Route::get('/berita/{article:slug}', [ArticleController::class, 'show'])->name('articles.show');

Route::get('/templates', [TemplateController::class, 'index'])->name('templates.index');

Route::get('/templates/{template:slug}/preview/{page?}', [TemplatePreviewController::class, 'show'])
    ->name('templates.preview');

Route::get('/templates/{template:slug}/use', TemplateUseController::class)
    ->name('templates.use');

Route::middleware('auth')->group(function () {
    Route::resource('organizations', OrganizationController::class)->only(['index', 'create', 'store', 'show', 'destroy']);

    Route::patch('organizations/{organization}/publish', [OrganizationController::class, 'publish'])
        ->name('organizations.publish');

    Route::post('organizations/{organization}/members', [OrganizationMemberController::class, 'store'])
        ->name('organizations.members.store');
    Route::patch('organizations/{organization}/members/{user}', [OrganizationMemberController::class, 'update'])
        ->name('organizations.members.update');
    Route::delete('organizations/{organization}/members/{user}', [OrganizationMemberController::class, 'destroy'])
        ->name('organizations.members.destroy');

    // scopeBindings() constrains {page} implicit bindings through {organization} (Organization
    // has a direct pages() relation), so a page id from another tenant 404s instead of leaking
    // cross-tenant. {section} is a grandchild (Organization -> Page -> Section), which
    // scopeBindings()'s naming convention can't reach, so those routes sit outside this group
    // and are scoped manually in OrganizationSectionController via $section->page->organization_id.
    Route::scopeBindings()->group(function () {
        Route::get('organizations/{organization}/builder', [OrganizationBuilderController::class, 'edit'])
            ->name('organizations.builder.edit');
        Route::get('organizations/{organization}/builder/{page:slug}', [OrganizationBuilderController::class, 'edit'])
            ->name('organizations.builder.page');
        Route::get('organizations/{organization}/builder/{page:slug}/canvas', [OrganizationBuilderController::class, 'canvas'])
            ->name('organizations.builder.canvas');
        Route::get('organizations/{organization}/builder/{page:slug}/sections/{key}/preview', [OrganizationBuilderController::class, 'sectionPreview'])
            ->name('organizations.builder.section-preview');

        Route::get('organizations/{organization}/preview', [OrganizationSiteController::class, 'preview'])
            ->name('organizations.preview');
        Route::get('organizations/{organization}/preview/{page:slug}', [OrganizationSiteController::class, 'preview'])
            ->name('organizations.preview.page');

        Route::post('organizations/{organization}/pages', [OrganizationPageController::class, 'store'])
            ->name('organizations.pages.store');
        Route::patch('organizations/{organization}/pages/{page}', [OrganizationPageController::class, 'update'])
            ->name('organizations.pages.update');
        Route::delete('organizations/{organization}/pages/{page}', [OrganizationPageController::class, 'destroy'])
            ->name('organizations.pages.destroy');

        Route::post('organizations/{organization}/pages/{page}/sections', [OrganizationSectionController::class, 'store'])
            ->name('organizations.sections.store');
        Route::post('organizations/{organization}/pages/{page}/sections/reorder', [OrganizationSectionController::class, 'reorder'])
            ->name('organizations.sections.reorder');

        Route::get('organizations/{organization}/media', [MediaController::class, 'index'])
            ->name('organizations.media.index');
        Route::post('organizations/{organization}/media', [MediaController::class, 'store'])
            ->name('organizations.media.store');
        Route::delete('organizations/{organization}/media/{media}', [MediaController::class, 'destroy'])
            ->name('organizations.media.destroy');

        Route::resource('organizations.posts', OrganizationPostController::class)
            ->except(['show']);
        Route::resource('organizations.agendas', OrganizationAgendaController::class)
            ->except(['show']);
        Route::resource('organizations.announcements', OrganizationAnnouncementController::class)
            ->except(['show']);

        Route::post('organizations/{organization}/officers/reorder', [OrganizationOfficerController::class, 'reorder'])
            ->name('organizations.officers.reorder');
        Route::resource('organizations.officers', OrganizationOfficerController::class)
            ->except(['show']);
        Route::resource('organizations.programs', OrganizationProgramController::class)
            ->except(['show']);
        Route::resource('organizations.networks', OrganizationNetworkController::class)
            ->except(['show']);

        Route::post('organizations/{organization}/gallery/reorder', [OrganizationGalleryController::class, 'reorder'])
            ->name('organizations.gallery.reorder');
        Route::resource('organizations.gallery', OrganizationGalleryController::class)
            ->parameters(['gallery' => 'photo'])
            ->except(['show']);

        Route::post('organizations/{organization}/facilities/reorder', [OrganizationFacilityController::class, 'reorder'])
            ->name('organizations.facilities.reorder');
        Route::resource('organizations.facilities', OrganizationFacilityController::class)
            ->except(['show']);

        Route::resource('organizations.financial-reports', OrganizationFinancialReportController::class)
            ->except(['show']);

        Route::get('organizations/{organization}/brand', [OrganizationBrandController::class, 'edit'])
            ->name('organizations.brand.edit');
        Route::patch('organizations/{organization}/brand', [OrganizationBrandController::class, 'update'])
            ->name('organizations.brand.update');

        Route::get('organizations/{organization}/edit', [OrganizationEditController::class, 'edit'])
            ->name('organizations.edit.edit');
        Route::patch('organizations/{organization}/edit/name', [OrganizationEditController::class, 'updateName'])
            ->name('organizations.edit.name.update');
        Route::patch('organizations/{organization}/edit/slug', [OrganizationEditController::class, 'updateSlug'])
            ->name('organizations.edit.slug.update');
        Route::patch('organizations/{organization}/edit/description', [OrganizationEditController::class, 'updateDescription'])
            ->name('organizations.edit.description.update');

        Route::get('organizations/{organization}/template', [OrganizationTemplateController::class, 'edit'])
            ->name('organizations.template.edit');
        Route::patch('organizations/{organization}/template', [OrganizationTemplateController::class, 'update'])
            ->name('organizations.template.update');

        Route::get('organizations/{organization}/plan', [OrganizationPlanController::class, 'edit'])
            ->name('organizations.plan.edit');
        Route::post('organizations/{organization}/plan', [OrganizationPlanController::class, 'store'])
            ->name('organizations.plan.store');
        Route::post('organizations/{organization}/plan/apply-discount', [OrganizationPlanController::class, 'applyDiscount'])
            ->name('organizations.plan.apply-discount');
        Route::get('organizations/{organization}/plan/{planChangeRequest}/pay', [OrganizationPlanController::class, 'pay'])
            ->name('organizations.plan.pay');
    });

    // Outside scopeBindings(): that group derives the parent relation from the parameter name
    // ({program} -> Organization::program()), which doesn't exist - the relation is
    // donationPrograms(). Ownership is verified in the controller instead, same as the section
    // routes below.
    Route::get('organizations/{organization}/preview-donasi/{program}', [OrganizationSiteController::class, 'previewDonationProgram'])
        ->name('organizations.preview.donation');

    // Same reason these sit outside scopeBindings(): {donation} would be scoped through
    // Organization::donations(), but the relation is donationPrograms(). Each action verifies
    // ownership itself (OrganizationDonationProgramController::ensureBelongsToOrganization()).
    Route::resource('organizations.donations', OrganizationDonationProgramController::class)
        ->parameters(['donations' => 'donation'])
        ->except(['show']);
    Route::get('organizations/{organization}/donations/{donation}/transactions/create', [OrganizationDonationTransactionController::class, 'create'])
        ->name('organizations.donations.transactions.create');
    Route::post('organizations/{organization}/donations/{donation}/transactions', [OrganizationDonationTransactionController::class, 'store'])
        ->name('organizations.donations.transactions.store');
    Route::delete('organizations/{organization}/donations/{donation}/transactions/{transaction}', [OrganizationDonationTransactionController::class, 'destroy'])
        ->name('organizations.donations.transactions.destroy');

    Route::patch('organizations/{organization}/sections/{section}', [OrganizationSectionController::class, 'update'])
        ->name('organizations.sections.update');
    Route::delete('organizations/{organization}/sections/{section}', [OrganizationSectionController::class, 'destroy'])
        ->name('organizations.sections.destroy');
    Route::post('organizations/{organization}/sections/{section}/duplicate', [OrganizationSectionController::class, 'duplicate'])
        ->name('organizations.sections.duplicate');
});

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::resource('templates', AdminTemplateController::class)->except(['show']);
    Route::resource('articles', AdminArticleController::class)->except(['show']);
    Route::post('articles/images', [ArticleImageController::class, 'store'])->name('articles.images.store');
    Route::resource('plans', AdminPlanController::class)->except(['show']);
    Route::resource('discount-codes', DiscountCodeController::class)->except(['show']);
    Route::get('organizations', [AdminOrganizationController::class, 'index'])->name('organizations.index');
    Route::get('organizations/{organization}', [AdminOrganizationController::class, 'show'])->name('organizations.show');
    Route::post('organizations/{organization}/override-plan', [AdminOrganizationController::class, 'overridePlan'])->name('organizations.override-plan');
    Route::delete('organizations/{organization}', [AdminOrganizationController::class, 'destroy'])->name('organizations.destroy');

    Route::get('section-variants', [SectionVariantController::class, 'index'])->name('section-variants.index');
    Route::patch('section-variants/{sectionVariant}', [SectionVariantController::class, 'update'])->name('section-variants.update');
    Route::get('section-variants/{sectionVariant}/preview', [SectionVariantPreviewController::class, 'show'])->name('section-variants.preview');

    Route::get('plan-change-requests', [PlanChangeRequestController::class, 'index'])->name('plan-change-requests.index');
    Route::post('plan-change-requests/{planChangeRequest}/reject', [PlanChangeRequestController::class, 'reject'])->name('plan-change-requests.reject');
    Route::post('plan-change-requests/{planChangeRequest}/retry-approve', [PlanChangeRequestController::class, 'retryApprove'])->name('plan-change-requests.retry-approve');
});

// Called by Midtrans, not a logged-in tenant - outside the auth group entirely, protected by
// signature verification + a live status re-fetch instead (see MidtransWebhookController).
Route::post('webhooks/midtrans', MidtransWebhookController::class)->name('webhooks.midtrans');

require __DIR__.'/auth.php';

// Public tenant sites, served at {slug}.{tenancy.domain} (e.g. pcm-ambulu.website-mu.id).
// Guarded so the route is never *registered* - not just non-matching - when TENANT_DOMAIN
// is unset, which keeps local `php artisan serve` (no wildcard subdomain to route) behaving
// exactly like today with zero special-casing. See OrganizationSiteController for the lookup.
if ($tenantDomain = config('tenancy.domain')) {
    // 'tenant' middleware group (bootstrap/app.php), not the default 'web' group: these
    // routes are pure reads with no login/forms, so they skip session/CSRF entirely and
    // run against a SELECT-only DB connection - see UseReadOnlyConnection's docblock.
    Route::domain('{organization_slug}.'.$tenantDomain)->withoutMiddleware('web')->middleware('tenant')->group(function () {
        Route::get('/', [OrganizationSiteController::class, 'show'])->name('tenant.home');
        Route::get('/berita/{post_slug}', [OrganizationSiteController::class, 'post'])->name('tenant.posts.show');
        Route::get('/pengumuman/{announcement}', [OrganizationSiteController::class, 'announcement'])->name('tenant.announcements.show');
        Route::get('/agenda/{agenda}', [OrganizationSiteController::class, 'agenda'])->name('tenant.agendas.show');
        Route::get('/donasi/{program_slug}', [OrganizationSiteController::class, 'donationProgram'])->name('tenant.donations.show');

        // Catch-all for any other builder page (e.g. /donasi, /laporan-keuangan) - must stay
        // last in this group so it never shadows the specific routes above.
        Route::get('/{page_slug}', [OrganizationSiteController::class, 'showPage'])->name('tenant.pages.show');
    });
}
