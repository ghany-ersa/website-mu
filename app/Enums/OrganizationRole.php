<?php

namespace App\Enums;

enum OrganizationRole: string
{
    case Owner = 'owner';
    case Editor = 'editor';

    public function label(): string
    {
        return match ($this) {
            self::Owner => 'Owner',
            self::Editor => 'Editor',
        };
    }

    /**
     * Roles allowed to manage organization members (add/remove/change role).
     */
    public function canManageMembers(): bool
    {
        return $this === self::Owner;
    }
}
