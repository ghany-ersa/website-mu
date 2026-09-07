<?php

namespace App\Models;

use App\Models\Concerns\InvalidatesTenantPageCache;
use Database\Factories\DonationTransactionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['donation_program_id', 'donor_name', 'amount', 'donated_at'])]
class DonationTransaction extends Model
{
    /** @use HasFactory<DonationTransactionFactory> */
    use HasFactory;

    use InvalidatesTenantPageCache;

    /**
     * Resolved through the parent donation program - a transaction changes what
     * DonationProgram::collectedAmount()/progressPercent()/status() report on the tenant site's
     * donasi-progress section, even though this model has no organization_id of its own.
     */
    public function tenantOrganizationId(): ?int
    {
        return $this->donationProgram?->organization_id;
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'donated_at' => 'date',
        ];
    }

    /**
     * @return BelongsTo<DonationProgram, $this>
     */
    public function donationProgram(): BelongsTo
    {
        return $this->belongsTo(DonationProgram::class);
    }
}
