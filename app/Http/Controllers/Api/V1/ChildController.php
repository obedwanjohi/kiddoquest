<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Child;
use App\Models\Mission;
use App\Services\Learning\ChildSnapshotService;
use App\Services\Learning\SyncService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ChildController extends ApiController
{
    public function index(Request $request): JsonResponse
    {
        $children = $this->guardian($request)->children()->orderBy('created_at')->get();

        return response()->json(['children' => $children->map(fn (Child $child) => $this->childArray($child))]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $this->validated($request);

        $child = $this->guardian($request)->children()->create([
            'name'              => $data['name'],
            'avatar'            => $data['avatar'] ?? 'lion',
            'favorite_color'    => $data['favorite_color'] ?? 'purple',
            'birthdate'         => $data['birthdate'] ?? null,
            'recommended_level' => $data['level'] ?? Child::recommendLevel($data['birthdate'] ?? null),
            'total_stars'       => 0,
            'star_coins'        => 0,
        ]);

        return response()->json(['child' => $this->childArray($child)], 201);
    }

    public function show(Request $request, int $child): JsonResponse
    {
        $model = $this->find($request, $child);

        return $model
            ? response()->json(['child' => $this->childArray($model)])
            : $this->fail('child_not_found', 'That child does not belong to this account.', 404);
    }

    public function update(Request $request, int $child): JsonResponse
    {
        $model = $this->find($request, $child);

        if (! $model) {
            return $this->fail('child_not_found', 'That child does not belong to this account.', 404);
        }

        $data = $this->validated($request, partial: true);

        $model->fill(array_filter([
            'name'              => $data['name'] ?? null,
            'avatar'            => $data['avatar'] ?? null,
            'favorite_color'    => $data['favorite_color'] ?? null,
            'birthdate'         => $data['birthdate'] ?? null,
            'recommended_level' => $data['level'] ?? null,
        ], static fn ($value) => $value !== null));

        $model->save();

        return response()->json(['child' => $this->childArray($model)]);
    }

    public function destroy(Request $request, int $child): JsonResponse
    {
        $model = $this->find($request, $child);

        if (! $model) {
            return $this->fail('child_not_found', 'That child does not belong to this account.', 404);
        }

        $model->delete();

        return response()->json(['ok' => true]);
    }

    /**
     * The authoritative state of one child. A second device calls this to catch
     * up on everything the first device already synced.
     */
    public function snapshot(Request $request, int $child, ChildSnapshotService $snapshots, SyncService $sync): JsonResponse
    {
        $model = $this->find($request, $child);

        if (! $model) {
            return $this->fail('child_not_found', 'That child does not belong to this account.', 404);
        }

        return response()->json([
            'snapshot' => $snapshots->for($model),
            'cursor'   => $sync->cursorFor($model),
        ]);
    }

    /** Parent-set daily limit, in minutes. Zero means no limit. */
    public function screenTime(Request $request, int $child): JsonResponse
    {
        $model = $this->find($request, $child);

        if (! $model) {
            return $this->fail('child_not_found', 'That child does not belong to this account.', 404);
        }

        $data = $request->validate(['minutes' => ['required', 'integer', 'min:0', 'max:300']]);

        $model->daily_time_limit_minutes = (int) $data['minutes'];
        $model->save();

        return response()->json(['child' => $this->childArray($model)]);
    }

    /** Tomorrow's focus mission, as set by a parent. */
    public function focusMission(Request $request, int $child): JsonResponse
    {
        $model = $this->find($request, $child);

        if (! $model) {
            return $this->fail('child_not_found', 'That child does not belong to this account.', 404);
        }

        $data = $request->validate([
            'mission_id' => ['nullable', 'integer', Rule::exists(Mission::class, 'id')],
        ]);

        $model->assigned_mission_id = $data['mission_id'] ?? null;
        $model->save();

        return response()->json(['child' => $this->childArray($model)]);
    }

    protected function find(Request $request, int $childId): ?Child
    {
        return $this->guardian($request)->children()->find($childId);
    }

    protected function validated(Request $request, bool $partial = false): array
    {
        $required = $partial ? 'sometimes' : 'required';

        return $request->validate([
            'name'           => [$required, 'string', 'max:60'],
            'avatar'         => ['sometimes', 'string', Rule::in(Child::avatarIdentifiers())],
            'favorite_color' => ['sometimes', 'string', 'max:24'],
            'birthdate'      => ['sometimes', 'nullable', 'date', 'before:today'],
            'level'          => ['sometimes', 'string', 'max:24'],
        ]);
    }
}
