<?php

namespace App\Services\Samples;

/**
 * Stand-in portrait photography for `struktur-pengurus` items across every seeded template.
 *
 * Why this exists: struktur-pengurus/{standar,modern}.blade.php render each person as an
 * `aspect-square` card and, when `photo` is empty, draw a blank grey box - so a template preview
 * of a 16-person redaksi was 16 empty squares. The officers themselves come from real rosters
 * (Samples::timRedaksi(), CmsSampleDataSeeder::nurulHudaOfficerSamples()), but none of those
 * organizations has published headshots this app may hotlink, so the templates need placeholders.
 *
 * Assigned strictly BY INDEX, never by name. These are real people, and picking a face to match
 * a name would mean guessing someone's gender or appearance from their name alone and attaching a
 * stranger's photo to them - wrong in a way a neutral rotation is not. The rotation is
 * deliberately mixed so a roster doesn't read as uniformly one gender, and the ordering carries
 * no claim about any individual. A takmir or redaksi replaces these with real headshots through
 * the officers CMS; that upload wins immediately (the section auto-binds to Officer::photo for a
 * real organization - see the partials' `$organization` branch).
 *
 * Square crops (w=400&h=400) because the card is aspect-square; every URL was checked to return
 * 200. Same Unsplash-with-query-params convention as the HERO_IMAGE/ABOUT_IMAGE constants in the
 * sibling Samples classes.
 */
class PortraitPhotos
{
    /**
     * @var array<int, string>
     */
    private const PORTRAITS = [
        'photo-1507003211169-0a1dd7228f2d',
        'photo-1573496359142-b8d87734a5a2',
        'photo-1560250097-0b93528c311a',
        'photo-1580489944761-15a19d654956',
        'photo-1472099645785-5658abf4ff4e',
        'photo-1438761681033-6461ffad8d80',
        'photo-1568602471122-7832951cc4c5',
        'photo-1544005313-94ddf0286df2',
        'photo-1519085360753-af0119f7cbe7',
        'photo-1487412720507-e7ab37603c6f',
        'photo-1506794778202-cad84cf45f1d',
        'photo-1489424731084-a5d8b219a5bb',
        'photo-1500648767791-00dcc994a43e',
        'photo-1531123897727-8f129e1688ce',
        'photo-1463453091185-61582044d556',
        'photo-1534528741775-53994a69daeb',
    ];

    /**
     * The portrait for the person at $index in a roster. Wraps around, so a roster longer than
     * the rotation repeats rather than falling back to an empty square.
     */
    public static function at(int $index): string
    {
        $id = self::PORTRAITS[$index % count(self::PORTRAITS)];

        return "https://images.unsplash.com/{$id}?auto=format&fit=crop&w=400&h=400&q=80";
    }

    /**
     * Adds a `photo` to each {name, role} entry that doesn't already have one, keeping the list's
     * own order. Used by the template seeders so the roster they write into structure['items']
     * matches the shape struktur-pengurus renders - see that partial's $organization branch for
     * the same {name, role, photo} triple.
     *
     * @param  array<int, array<string, mixed>>  $people
     * @return array<int, array<string, mixed>>
     */
    public static function applyTo(array $people): array
    {
        return array_map(
            fn (array $person, int $index) => $person + ['photo' => self::at($index)],
            $people,
            array_keys($people),
        );
    }
}
