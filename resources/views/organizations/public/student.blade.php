{{--
    Public profile for a single pupil - served at {slug}.{tenant domain}/siswa/{student_slug}
    (see routes/web.php, OrganizationSiteController::student()). Shares the same <html> scaffold
    as the tenant homepage via _document.blade.php, but overrides its SEO tags to describe the
    pupil instead of the organization.
--}}
@include('organizations.pages._document', [
    'organization' => $organization,
    'page' => (object) ['sections' => collect()],
    'metaTitle' => $student->name.' - '.$organization->name,
    'metaDescription' => $student->bio
        ? \Illuminate\Support\Str::limit(strip_tags($student->bio), 160)
        : 'Profil dan daftar prestasi '.$student->name.' di '.$organization->name.'.',
    'metaImage' => $student->photo,
    'body' => view('organizations.public._student-body', [
        'organization' => $organization,
        'student' => $student,
    ])->render(),
])
