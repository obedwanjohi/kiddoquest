<?php

namespace App\Jobs;

use App\Models\Child;
use App\Services\Learning\SyncService;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

/**
 * Turns a child's stored events into progress, attempts and daily statistics.
 *
 * One job per child is the whole scaling story: two children never touch the
 * same rows, so throughput grows by adding workers rather than by making the
 * database cleverer. The job is unique per child, so a burst of syncs from a
 * phone and a television collapses into one run.
 */
class ProjectLearningEventsJob implements ShouldQueue, ShouldBeUnique
{
    use Queueable;

    public int $tries = 3;

    public int $backoff = 10;

    public function __construct(public int $childId)
    {
        $this->onQueue(config('kiddoquest.sync.queue', 'sync-projection'));
    }

    public function uniqueId(): string
    {
        return 'project-events-' . $this->childId;
    }

    /** Do not hold the lock longer than a slow run could take. */
    public int $uniqueFor = 300;

    public function handle(SyncService $sync): void
    {
        $child = Child::find($this->childId);

        if (! $child) {
            return;
        }

        // Keep going while there is a backlog, in bounded slices, so one very
        // busy child cannot occupy a worker indefinitely.
        for ($slice = 0; $slice < 10; $slice++) {
            if ($sync->project($child) === 0) {
                return;
            }
        }
    }
}
