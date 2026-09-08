<?php

namespace App\Http\Controllers\Api\V1;

use App\Jobs\ProjectLearningEventsJob;
use App\Services\Learning\ChildSnapshotService;
use App\Services\Learning\SyncService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * The one endpoint the child loop depends on, and the only one that runs hot.
 *
 * A device posts whatever is in its outbox and gets back which events landed
 * plus the server's authoritative view of the child. Everything about it is
 * designed to be safe to retry.
 */
class SyncController extends ApiController
{
    public function store(Request $request, SyncService $sync, ChildSnapshotService $snapshots): JsonResponse
    {
        $max = (int) config('kiddoquest.sync.max_events_per_request', 200);

        $data = $request->validate([
            'events'            => ['present', 'array', "max:{$max}"],
            'events.*.id'       => ['required', 'string', 'max:64'],
            'events.*.type'     => ['required', 'string', 'max:40'],
            'events.*.seq'      => ['nullable', 'integer', 'min:0'],
            'events.*.client_ts'=> ['nullable', 'string', 'max:40'],
            'events.*.payload'  => ['nullable', 'array'],
            'cursor'            => ['nullable', 'string', 'max:64'],
        ]);

        $guardian = $this->guardian($request);
        $child = $this->child($request);

        $result = $sync->ingest($guardian, $this->deviceId($request), $child, $data['events']);

        if ($child) {
            // With a real queue behind it, projection happens on a worker and the
            // snapshot in this response may lag by a second. Without one (local
            // development, or a small deployment) the job runs inline and the
            // snapshot is already current. Either way the contract is the same.
            ProjectLearningEventsJob::dispatch($child->id);

            $child->refresh();
        }

        return response()->json([
            'accepted' => $result['accepted'],
            'rejected' => $result['rejected'],
            'stored'   => $result['stored'],
            'cursor'   => $child ? $sync->cursorFor($child) : null,
            'snapshot' => $child ? $snapshots->for($child) : null,
        ]);
    }
}
