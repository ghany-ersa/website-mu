<?php

use App\Http\Controllers\Admin\TemplateController;
use App\Http\Controllers\MediaController;
use App\Http\Controllers\OrganizationAgendaController;
use App\Http\Controllers\OrganizationAnnouncementController;
use App\Http\Controllers\OrganizationBrandController;
use App\Http\Controllers\OrganizationBuilderController;
use App\Http\Controllers\OrganizationController;
use App\Http\Controllers\OrganizationGalleryController;
use App\Http\Controllers\OrganizationMemberController;
use App\Http\Controllers\OrganizationNetworkController;
use App\Http\Controllers\OrganizationOfficerController;
use App\Http\Controllers\OrganizationPageController;
use App\Http\Controllers\OrganizationPostController;
use App\Http\Controllers\OrganizationProgramController;
use App\Http\Controllers\OrganizationSectionController;
use App\Http\Controllers\OrganizationSeoController;
use App\Http\Controllers\OrganizationSiteController;
use App\Http\Controllers\TemplatePreviewController;
use App\Http\Controllers\TemplateUseController;
use App\Models\Template;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    $templates = Template::where('is_active', true)
        ->orderBy('name')
        ->get();

    return view('welcome', ['templates' => $templates]);
});

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

        Route::get('organizations/{organization}/seo', [OrganizationSeoController::class, 'edit'])
            ->name('organizations.seo.edit');
        Route::patch('organizations/{organization}/seo', [OrganizationSeoController::class, 'update'])
            ->name('organizations.seo.update');
    });

    Route::patch('organizations/{organization}/sections/{section}', [OrganizationSectionController::class, 'update'])
        ->name('organizations.sections.update');
    Route::delete('organizations/{organization}/sections/{section}', [OrganizationSectionController::class, 'destroy'])
        ->name('organizations.sections.destroy');
    Route::post('organizations/{organization}/sections/{section}/duplicate', [OrganizationSectionController::class, 'duplicate'])
        ->name('organizations.sections.duplicate');
});

Route::middleware('auth')->prefix('admin')->name('admin.')->group(function () {
    Route::resource('templates', TemplateController::class)->except(['show']);
});

require __DIR__.'/auth.php';

// Public tenant sites, served at {slug}.{tenancy.domain} (e.g. pcm-ambulu.website-mu.id).
// Guarded so the route is never *registered* — not just non-matching — when TENANT_DOMAIN
// is unset, which keeps local `php artisan serve` (no wildcard subdomain to route) behaving
// exactly like today with zero special-casing. See OrganizationSiteController for the lookup.
if ($tenantDomain = config('tenancy.domain')) {
    Route::domain('{organization_slug}.'.$tenantDomain)->group(function () {
        Route::get('/', [OrganizationSiteController::class, 'show'])->name('tenant.home');
    });
}
