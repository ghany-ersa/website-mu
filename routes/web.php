<?php

use App\Http\Controllers\Admin\OrganizationController as AdminOrganizationController;
use App\Http\Controllers\Admin\PlanChangeRequestController;
use App\Http\Controllers\Admin\PlanController as AdminPlanController;
use App\Http\Controllers\Admin\TemplateController;
use App\Http\Controllers\MediaController;
use App\Http\Controllers\OrganizationAgendaController;
use App\Http\Controllers\OrganizationAnnouncementController;
use App\Http\Controllers\OrganizationBrandController;
use App\Http\Controllers\OrganizationBuilderController;
use App\Http\Controllers\OrganizationController;
use App\Http\Controllers\OrganizationEditController;
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
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\TemplatePreviewController;
use App\Http\Controllers\TemplateUseController;
use App\Models\Plan;
use App\Models\Template;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    $templates = Template::where('is_active', true)
        ->orderBy('name')
        ->get();

    $plans = Plan::with('limits')
        ->where('is_active', true)
        ->orderBy('price_monthly')
        ->get();

    return view('welcome', ['templates' => $templates, 'plans' => $plans]);
});

Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap');

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

        Route::get('organizations/{organization}/plan', [OrganizationPlanController::class, 'edit'])
            ->name('organizations.plan.edit');
        Route::post('organizations/{organization}/plan', [OrganizationPlanController::class, 'store'])
            ->name('organizations.plan.store');
        Route::post('organizations/{organization}/plan/{planChangeRequest}/confirm-payment', [OrganizationPlanController::class, 'confirmPayment'])
            ->name('organizations.plan.confirm-payment');
    });

    Route::patch('organizations/{organization}/sections/{section}', [OrganizationSectionController::class, 'update'])
        ->name('organizations.sections.update');
    Route::delete('organizations/{organization}/sections/{section}', [OrganizationSectionController::class, 'destroy'])
        ->name('organizations.sections.destroy');
    Route::post('organizations/{organization}/sections/{section}/duplicate', [OrganizationSectionController::class, 'duplicate'])
        ->name('organizations.sections.duplicate');
});

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::resource('templates', TemplateController::class)->except(['show']);
    Route::resource('plans', AdminPlanController::class)->except(['show']);
    Route::get('organizations', [AdminOrganizationController::class, 'index'])->name('organizations.index');

    Route::get('plan-change-requests', [PlanChangeRequestController::class, 'index'])->name('plan-change-requests.index');
    Route::post('plan-change-requests/{planChangeRequest}/approve', [PlanChangeRequestController::class, 'approve'])->name('plan-change-requests.approve');
    Route::post('plan-change-requests/{planChangeRequest}/reject', [PlanChangeRequestController::class, 'reject'])->name('plan-change-requests.reject');
});

require __DIR__.'/auth.php';

// Public tenant sites, served at {slug}.{tenancy.domain} (e.g. pcm-ambulu.website-mu.id).
// Guarded so the route is never *registered* — not just non-matching — when TENANT_DOMAIN
// is unset, which keeps local `php artisan serve` (no wildcard subdomain to route) behaving
// exactly like today with zero special-casing. See OrganizationSiteController for the lookup.
if ($tenantDomain = config('tenancy.domain')) {
    Route::domain('{organization_slug}.'.$tenantDomain)->group(function () {
        Route::get('/', [OrganizationSiteController::class, 'show'])->name('tenant.home');
        Route::get('/berita/{post_slug}', [OrganizationSiteController::class, 'post'])->name('tenant.posts.show');
        Route::get('/pengumuman/{announcement}', [OrganizationSiteController::class, 'announcement'])->name('tenant.announcements.show');
        Route::get('/agenda/{agenda}', [OrganizationSiteController::class, 'agenda'])->name('tenant.agendas.show');
    });
}
