<?php

namespace App\Support\Admin;

use App\Enums\ContactInquiryPriority;
use App\Enums\ContactInquiryStatus;
use App\Models\ContactInquiry;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

final class LeadDashboardMetrics
{
    public const PERIOD_TODAY = 'today';

    public const PERIOD_7D = '7d';

    public const PERIOD_30D = '30d';

    public const PERIOD_MONTH = 'month';

    public const PERIOD_ALL = 'all';

    /**
     * @return array<string, string>
     */
    public static function periodOptions(): array
    {
        return [
            self::PERIOD_TODAY => 'Today',
            self::PERIOD_7D => 'Last 7 Days',
            self::PERIOD_30D => 'Last 30 Days',
            self::PERIOD_MONTH => 'This Month',
            self::PERIOD_ALL => 'All Time',
        ];
    }

    public function __construct(
        private readonly string $period = self::PERIOD_30D,
    ) {}

    public static function fromFilters(?array $filters): self
    {
        $period = is_string($filters['period'] ?? null) ? $filters['period'] : self::PERIOD_30D;

        if (! array_key_exists($period, self::periodOptions())) {
            $period = self::PERIOD_30D;
        }

        return new self($period);
    }

    /**
     * @param  Builder<ContactInquiry>  $query
     * @return Builder<ContactInquiry>
     */
    public function applyCreatedAtPeriod(Builder $query): Builder
    {
        $now = CarbonImmutable::now();

        return match ($this->period) {
            self::PERIOD_TODAY => $query->whereDate('created_at', $now->toDateString()),
            self::PERIOD_7D => $query->where('created_at', '>=', $now->subDays(7)->startOfDay()),
            self::PERIOD_30D => $query->where('created_at', '>=', $now->subDays(30)->startOfDay()),
            self::PERIOD_MONTH => $query
                ->where('created_at', '>=', $now->startOfMonth())
                ->where('created_at', '<=', $now->endOfMonth()),
            default => $query,
        };
    }

    /**
     * @return Builder<ContactInquiry>
     */
    public function periodQuery(): Builder
    {
        return $this->applyCreatedAtPeriod(ContactInquiry::query());
    }

    /**
     * @return array{
     *     total: int,
     *     new: int,
     *     follow_ups_scheduled: int,
     *     overdue_follow_ups: int,
     *     won: int,
     *     lost: int
     * }
     */
    public function kpis(): array
    {
        $new = ContactInquiryStatus::New->value;
        $won = ContactInquiryStatus::Won->value;
        $lost = ContactInquiryStatus::Lost->value;

        $periodRow = $this->periodQuery()
            ->toBase()
            ->selectRaw('COUNT(*) as total')
            ->selectRaw('SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as new_count', [$new])
            ->selectRaw('SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as won_count', [$won])
            ->selectRaw('SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as lost_count', [$lost])
            ->first();

        $followUpRow = ContactInquiry::query()
            ->toBase()
            ->selectRaw('SUM(CASE WHEN follow_up_at IS NOT NULL THEN 1 ELSE 0 END) as scheduled')
            ->selectRaw('SUM(CASE WHEN follow_up_at IS NOT NULL AND follow_up_at < ? THEN 1 ELSE 0 END) as overdue', [now()])
            ->first();

        return [
            'total' => (int) ($periodRow->total ?? 0),
            'new' => (int) ($periodRow->new_count ?? 0),
            'follow_ups_scheduled' => (int) ($followUpRow->scheduled ?? 0),
            'overdue_follow_ups' => (int) ($followUpRow->overdue ?? 0),
            'won' => (int) ($periodRow->won_count ?? 0),
            'lost' => (int) ($periodRow->lost_count ?? 0),
        ];
    }

    /**
     * @return array<string, int>
     */
    public function statusCounts(): array
    {
        $rows = $this->periodQuery()
            ->select('status', DB::raw('COUNT(*) as aggregate'))
            ->groupBy('status')
            ->pluck('aggregate', 'status');

        $counts = [];

        foreach (ContactInquiryStatus::cases() as $status) {
            $counts[$status->value] = (int) ($rows[$status->value] ?? 0);
        }

        return $counts;
    }

    /**
     * @return array<string, int>
     */
    public function priorityCounts(): array
    {
        $rows = $this->periodQuery()
            ->select('priority', DB::raw('COUNT(*) as aggregate'))
            ->groupBy('priority')
            ->pluck('aggregate', 'priority');

        $counts = [];

        foreach (ContactInquiryPriority::cases() as $priority) {
            $counts[$priority->value] = (int) ($rows[$priority->value] ?? 0);
        }

        return $counts;
    }

    /**
     * @return Collection<string, int>
     */
    public function sourceCounts(): Collection
    {
        return $this->periodQuery()
            ->select(DB::raw("COALESCE(NULLIF(source, ''), 'unknown') as source_key"), DB::raw('COUNT(*) as aggregate'))
            ->groupBy('source_key')
            ->orderByDesc('aggregate')
            ->pluck('aggregate', 'source_key')
            ->map(fn ($count): int => (int) $count);
    }

    /**
     * @return Collection<int, object{name: string, source: string|null, aggregate: int}>
     */
    public function interestBreakdown(int $limit = 8): Collection
    {
        return $this->periodQuery()
            ->whereNotNull('service_interested')
            ->where('service_interested', '!=', '')
            ->select(
                'service_interested as name',
                'source',
                DB::raw('COUNT(*) as aggregate')
            )
            ->groupBy('service_interested', 'source')
            ->orderByDesc('aggregate')
            ->limit($limit)
            ->get()
            ->map(fn ($row): object => (object) [
                'name' => (string) $row->name,
                'source' => $row->source,
                'aggregate' => (int) $row->aggregate,
            ]);
    }

    /**
     * @return Collection<int, object{label: string, aggregate: int, user_id: int|null}>
     */
    public function assignmentWorkload(): Collection
    {
        $assigned = $this->periodQuery()
            ->whereNotNull('assigned_to')
            ->select('assigned_to', DB::raw('COUNT(*) as aggregate'))
            ->groupBy('assigned_to')
            ->orderByDesc('aggregate')
            ->get();

        $names = User::query()
            ->whereIn('id', $assigned->pluck('assigned_to'))
            ->pluck('name', 'id');

        $rows = $assigned->map(fn ($row): object => (object) [
            'label' => (string) ($names[$row->assigned_to] ?? 'Unknown user'),
            'aggregate' => (int) $row->aggregate,
            'user_id' => (int) $row->assigned_to,
        ]);

        $unassigned = (clone $this->periodQuery())->whereNull('assigned_to')->count();

        if ($unassigned > 0) {
            $rows->push((object) [
                'label' => 'Unassigned',
                'aggregate' => $unassigned,
                'user_id' => null,
            ]);
        }

        return $rows->sortByDesc('aggregate')->values();
    }

    /**
     * @return Collection<int, ContactInquiry>
     */
    public function recentLeads(int $limit = 8): Collection
    {
        return $this->periodQuery()
            ->with('assignee:id,name')
            ->latest('created_at')
            ->limit($limit)
            ->get();
    }

    /**
     * @return Collection<int, ContactInquiry>
     */
    public function overdueFollowUps(int $limit = 8): Collection
    {
        return ContactInquiry::query()
            ->with('assignee:id,name')
            ->whereNotNull('follow_up_at')
            ->where('follow_up_at', '<', now())
            ->orderBy('follow_up_at')
            ->limit($limit)
            ->get();
    }

    /**
     * @return Collection<int, ContactInquiry>
     */
    public function upcomingFollowUps(int $limit = 8): Collection
    {
        return ContactInquiry::query()
            ->with('assignee:id,name')
            ->whereNotNull('follow_up_at')
            ->where('follow_up_at', '>=', now())
            ->orderBy('follow_up_at')
            ->limit($limit)
            ->get();
    }
}
