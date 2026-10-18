{{-- A school's activities, drawn with the same bento grid as program-unggulan/sekolahmu - the
     two differ only in which `type` of Program they pull, so this delegates rather than
     duplicating the layout (which is what layanan/standar does against program-unggulan, and
     which has to be kept in sync by hand).

     `programType` is what tells the shared partial to read `kegiatan` rows. --}}
@include('templates.sections.program-unggulan.sekolahmu', [
    'section' => $section,
    'programType' => 'kegiatan',
])
