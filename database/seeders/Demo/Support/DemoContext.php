<?php

namespace Database\Seeders\Demo\Support;

use App\Models\DemoBatch;
use App\Models\User;
use App\Support\Demo\DemoBatchTracker;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class DemoContext
{
    /** @var array<string, User> */
    public array $members = [];

    /** @var array<string, User> */
    public array $admins = [];

    /** @var list<string> */
    public array $checklist = [];

    public int $listingCount = 0;

    public int $orderCount = 0;

    public int $escrowCount = 0;

    public int $ticketCount = 0;

    public int $kycCount = 0;

    public int $transactionCount = 0;

    public DemoBatchTracker $tracker;

    public DemoTimeline $timeline;

    public function __construct(?DemoBatchTracker $tracker = null, ?DemoTimeline $timeline = null)
    {
        $this->tracker = $tracker ?? app(DemoBatchTracker::class);
        $this->timeline = $timeline ?? DemoTimeline::fromNow();
    }

    public function startBatch(string $name, string $source = 'demo:seed'): DemoBatch
    {
        if ($this->tracker->batch()) {
            return $this->tracker->batch();
        }

        return $this->tracker->start($name, $source);
    }

    public function track(?Model $model): ?Model
    {
        if ($model) {
            $this->tracker->track($model);
        }

        return $model;
    }

    public function stamp(Model $model, Carbon $at, array $extra = []): void
    {
        $this->timeline->stamp($model, $at, $extra);
        $this->track($model);
    }

    public function registerMember(string $key, User $user): void
    {
        $this->track($user);
        $this->members[$key] = $user;
    }

    public function registerAdmin(string $key, User $user): void
    {
        $this->track($user);
        $this->admins[$key] = $user;
    }

    public function member(string $key): User
    {
        return $this->members[$key];
    }

    public function admin(string $key): User
    {
        return $this->admins[$key];
    }

    public function members(): Collection
    {
        return collect($this->members);
    }

    public function adminId(string $key = 'super'): int
    {
        return $this->admins[$key]->id;
    }

    public function ref(string $prefix): string
    {
        return strtoupper($prefix).'-'.Str::upper(Str::random(8));
    }

    public function note(string $line): void
    {
        $this->checklist[] = $line;
    }
}
