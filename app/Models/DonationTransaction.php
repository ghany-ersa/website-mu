<?php

namespace App\Models;

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
