<?php

namespace App\Models;

use App\Enums\PublishStatus;
use App\Models\Concerns\InvalidatesTenantPageCache;
use Database\Factories\AgendaFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

#[Fillable([
    'organization_id',
    'title',
    'poster',
    'starts_at',
    'repeats_weekly',
    'time_label',
    'location',
    'speaker',
    'description',
    'registration_url',
    'status',
])]
class Agenda extends Model
{
    /** @use HasFactory<AgendaFactory> */
    use HasFactory;

    use InvalidatesTenantPageCache;

    /**
     * Indexed by Carbon's dayOfWeek (0 = Sunday). "Ahad" rather than Carbon's id-locale
     * "Minggu": it's what Muhammadiyah organizations write on their own kajian schedules.
     */
    public const DAY_NAMES = ['Ahad', 'Senin', 'Selasa', 'Rabu', 'Kamis', "Jum'at", 'Sabtu'];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => PublishStatus::class,
            'starts_at' => 'datetime',
            'repeats_weekly' => 'boolean',
        ];
    }

    /**
     * The starts_at to store for a weekly agenda: the next date on $dayOfWeek, at midnight.
     * Only its weekday is read back - the time lives in time_label as free text (see the
     * add_speaker_and_weekly_schedule migration) - so which week it lands in doesn't matter;
     * the next one just keeps it near "now" for ordering against one-off agendas.
     */
    public static function weeklyAnchor(int $dayOfWeek): Carbon
    {
        $date = now()->startOfDay();

        return $date->dayOfWeek === $dayOfWeek ? $date : $date->next($dayOfWeek);
    }

    public function dayName(): string
    {
        return self::DAY_NAMES[$this->starts_at->dayOfWeek];
    }

    /**
     * When this agenda next happens: starts_at itself for a one-off, or the coming date of a
     * weekly one - today counts all day, since a free-text time_label ("Ba'da Isya") can't be
     * compared against the clock to tell whether today's session is over.
     */
    public function nextOccurrence(): Carbon
    {
        return $this->repeats_weekly
            ? self::weeklyAnchor($this->starts_at->dayOfWeek)
            : $this->starts_at;
    }

    /**
     * The time as shown to visitors: the tenant's own wording for a weekly agenda, the clock
     * time for a one-off.
     */
    public function timeLabel(): ?string
    {
        return $this->repeats_weekly
            ? (filled($this->time_label) ? $this->time_label : null)
            : $this->starts_at->format('H:i');
    }

    /**
     * The time line on the detail page: "Dimulai Pukul 18:30" for a one-off, the tenant's own
     * wording ("Ba'da Maghrib") for a weekly agenda - empty when none was given. The day/date
     * is deliberately left out: the badge beside it already shows it.
     */
    public function scheduleLabel(): string
    {
        if (! $this->repeats_weekly) {
            return 'Dimulai Pukul '.$this->starts_at->format('H:i');
        }

        return $this->timeLabel() ?? '';
    }

    /**
     * The item shape both agenda section variants render, and what TemplateSandboxService
     * freezes into a template's sample `items` - one place, so a weekly agenda can't show a
     * meaningless anchor date in one of them. `day_name` is set only for weekly agendas and is
     * what the views branch on; date_* are null then, so nothing falls back to printing the
     * anchor's day/month.
     *
     * @return array<string, mixed>
     */
    public function sectionItem(): array
    {
        return [
            'title' => $this->title,
            'poster' => $this->poster,
            'day_name' => $this->repeats_weekly ? $this->dayName() : null,
            'date_day' => $this->repeats_weekly ? null : $this->starts_at->format('d'),
            'date_month' => $this->repeats_weekly ? null : $this->starts_at->translatedFormat('M'),
            'date_year' => $this->repeats_weekly ? null : $this->starts_at->format('Y'),
            'location' => $this->location,
            'speaker' => $this->speaker,
            'time' => $this->timeLabel(),
        ];
    }

    /**
     * @return BelongsTo<Organization, $this>
     */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    /**
     * @param  Builder<Agenda>  $query
     * @return Builder<Agenda>
     */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', PublishStatus::Published);
    }
}
