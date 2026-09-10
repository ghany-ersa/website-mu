<?php

namespace App\Enums;

/**
 * Groups organization types in the "buat organisasi" picker (see organizations/create.blade.php,
 * which builds one <optgroup> per category from label()).
 *
 * Deliberately only TWO categories. Muhammadiyah and Aisyiyah were previously split across
 * separate 'persyarikatan' and 'ortom' groups, which is correct persyarikatan taxonomy but wrong
 * as a picker: it forced someone signing up to know that Aisyiyah is an organisasi otonom while
 * Muhammadiyah is the persyarikatan, and produced two dropdown groups of one item each. What a
 * user actually needs to answer is far simpler - "are you the organization, or one of its amal
 * usaha?" - so both now sit under one 'Organisasi' group.
 *
 * The 'ortom' case is kept as a deprecated alias so existing organization_types rows (and any
 * code path still passing the old value) resolve instead of throwing on cast - see label(), which
 * gives it the same text. The 2026_09_10 merge_ortom_into_organisasi migration rewrites those
 * rows; the case can be removed once no database in use still carries it.
 */
enum OrganizationCategory: string
{
    case Organisasi = 'persyarikatan';

    /** @deprecated Merged into self::Organisasi - retained only so stored 'ortom' rows still cast. */
    case Ortom = 'ortom';

    case Aum = 'aum';

    public function label(): string
    {
        return match ($this) {
            self::Organisasi, self::Ortom => 'Organisasi',
            self::Aum => 'Amal Usaha Muhammadiyah',
        };
    }
}
