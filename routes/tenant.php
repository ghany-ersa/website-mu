<?php

use App\Http\Controllers\OrganizationSiteController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public tenant site routes
|--------------------------------------------------------------------------
|
| Every path an organization's public site serves, included by the host-scoped group in
| routes/web.php rather than declared inline there - so the planned custom-domain group can
| include this same file instead of duplicating the list, and a path added here works on
| every host an organization is served on. The organization itself is resolved before any of
| these actions run, by ResolveTenantOrganization in the 'tenant' middleware group.
|
*/

Route::get('/', [OrganizationSiteController::class, 'show'])->name('tenant.home');
Route::get('/berita-lebih-banyak', [OrganizationSiteController::class, 'loadMoreBerita'])->name('tenant.posts.load-more');
Route::get('/galeri-lebih-banyak', [OrganizationSiteController::class, 'loadMoreGaleri'])->name('tenant.galleries.load-more');
Route::get('/berita/{post_slug}', [OrganizationSiteController::class, 'post'])->name('tenant.posts.show');
Route::get('/pengumuman/{announcement}', [OrganizationSiteController::class, 'announcement'])->name('tenant.announcements.show');
Route::get('/agenda/{agenda}', [OrganizationSiteController::class, 'agenda'])->name('tenant.agendas.show');
Route::get('/donasi/{program_slug}', [OrganizationSiteController::class, 'donationProgram'])->name('tenant.donations.show');
Route::get('/siswa/{student_slug}', [OrganizationSiteController::class, 'student'])->name('tenant.students.show');

// Catch-all for any other builder page (e.g. /donasi, /laporan-keuangan) - must stay
// last in this file so it never shadows the specific routes above.
Route::get('/{page_slug}', [OrganizationSiteController::class, 'showPage'])->name('tenant.pages.show');
