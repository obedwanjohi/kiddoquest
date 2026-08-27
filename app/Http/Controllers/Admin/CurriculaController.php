<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Curriculum;
use Illuminate\Http\Request;

class CurriculaController extends Controller
{
    public function index(Request $request)
    {
        $showTrashed = $request->boolean('trashed');

        $query = Curriculum::withCount('levels')
            ->orderBy('sort_order')
            ->orderBy('name');

        $curricula = $showTrashed
            ? $query->onlyTrashed()->get()
            : $query->get();

        $trashedCount = Curriculum::onlyTrashed()->count();

        return view('admin.curricula.index', compact('curricula', 'showTrashed', 'trashedCount'));
    }

    public function create()
    {
        return view('admin.curricula.create');
    }

    public function store(Request $request)
    {
        $validated = $this->validateCurriculum($request);
        $validated['sort_order'] = $validated['sort_order'] ?? 0;

        $curriculum = Curriculum::create($validated);

        return redirect()
            ->route('admin.curricula.index')
            ->with('success', "Curriculum \"{$curriculum->name}\" created successfully!");
    }

    public function show(Curriculum $curriculum)
    {
        $curriculum->load(['levels' => function ($q) {
            $q->withCount('subjects')->orderBy('sort_order');
        }]);

        return view('admin.curricula.show', compact('curriculum'));
    }

    public function edit(Curriculum $curriculum)
    {
        return view('admin.curricula.edit', compact('curriculum'));
    }

    public function update(Request $request, Curriculum $curriculum)
    {
        $validated = $this->validateCurriculum($request, $curriculum);

        $curriculum->update($validated);

        return redirect()
            ->route('admin.curricula.index')
            ->with('success', "Curriculum \"{$curriculum->name}\" updated successfully!");
    }

    /**
     * Soft-delete (send to trash). Reversible via restore().
     */
    public function destroy(Curriculum $curriculum)
    {
        $name = $curriculum->name;
        $curriculum->delete();

        return redirect()
            ->route('admin.curricula.index')
            ->with('success', "Curriculum \"{$name}\" moved to trash.");
    }

    public function restore(int $id)
    {
        $curriculum = Curriculum::onlyTrashed()->findOrFail($id);
        $curriculum->restore();

        return redirect()
            ->route('admin.curricula.index', ['trashed' => 1])
            ->with('success', "Curriculum \"{$curriculum->name}\" restored.");
    }

    /**
     * Permanently delete. Blocked while the curriculum still owns levels,
     * to avoid orphaning them.
     */
    public function forceDelete(int $id)
    {
        $curriculum = Curriculum::onlyTrashed()->withCount('levels')->findOrFail($id);

        if ($curriculum->levels_count > 0) {
            return redirect()
                ->route('admin.curricula.index', ['trashed' => 1])
                ->with('error', "Cannot permanently delete \"{$curriculum->name}\" — it still has {$curriculum->levels_count} level(s). Move or delete those first.");
        }

        $name = $curriculum->name;
        $curriculum->forceDelete();

        return redirect()
            ->route('admin.curricula.index', ['trashed' => 1])
            ->with('success', "Curriculum \"{$name}\" permanently deleted.");
    }

    private function validateCurriculum(Request $request, ?Curriculum $curriculum = null): array
    {
        $slugRule = 'nullable|string|max:255|unique:curricula,slug';
        if ($curriculum) {
            $slugRule .= ',' . $curriculum->id;
        }

        return $request->validate([
            'name' => 'required|string|max:255',
            'slug' => $slugRule,
            'code' => 'nullable|string|max:50',
            'description' => 'nullable|string|max:1000',
            'icon' => 'nullable|string|max:10',
            'color' => 'nullable|string|max:20',
            'status' => 'required|in:draft,published,archived',
            'sort_order' => 'nullable|integer|min:0',
        ]);
    }
}
