{{-- Event structured data for Google/rich results - appended after the agenda body markup,
     mirroring _article-jsonld.blade.php. This is what lets a kajian or a public acara show up
     in Google's event listings with its date, venue and poster rather than as a plain link.

     `location` is a Place with a plain-text address: agendas store the venue as one free-text
     string (agendas.location), so there is no structured street/city to split it into, and a
     bare name is valid for Place. It is omitted entirely when blank rather than emitted empty,
     which would fail validation. --}}
@php
    $eventData = array_filter([
        '@context' => 'https://schema.org',
        '@type' => 'Event',
        'name' => $agenda->title,
        // A weekly agenda's starts_at is only an anchor (see Agenda::nextOccurrence()), so
        // Google gets the coming session rather than a date that may already be past - as a
        // bare date, since its time is free text ("Ba'da Maghrib") with no clock time to send.
        'startDate' => $agenda->repeats_weekly
            ? $agenda->nextOccurrence()->toDateString()
            : $agenda->starts_at->toIso8601String(),
        'eventStatus' => 'https://schema.org/EventScheduled',
        'eventAttendanceMode' => 'https://schema.org/OfflineEventAttendanceMode',
        'description' => $description,
        'image' => $agenda->poster ? [$agenda->poster] : null,
        'url' => $url,
        'location' => $agenda->location ? [
            '@type' => 'Place',
            'name' => $agenda->location,
            'address' => $agenda->location,
        ] : null,
        'performer' => $agenda->speaker ? ['@type' => 'Person', 'name' => $agenda->speaker] : null,
        'organizer' => [
            '@type' => 'Organization',
            'name' => $organization->name,
        ],
    ], fn ($value) => $value !== null && $value !== '');
@endphp
<script type="application/ld+json">
{!! json_encode($eventData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP) !!}
</script>
