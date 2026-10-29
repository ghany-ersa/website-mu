{{-- A school's activities, drawn with the same bento grid as program-unggulan/sekolahmu - the
     two differ only in which `type` of Program they pull, so this delegates rather than
     duplicating the layout (a hand-kept copy is what the old `layanan` section was, before it
     was folded into program-unggulan's `source`).

     `programType` is what tells the shared partial to read `kegiatan` rows. --}}
@include('templates.sections.program-unggulan.sekolahmu', [
    'section' => $section,
    'programType' => 'kegiatan',
])
