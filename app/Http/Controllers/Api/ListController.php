<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Task;
use App\Models\TaskList;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ListController extends Controller
{
    /**
     * GET /api/lists/{type}
     * Return every list of a type, ordered by position, with item counts and
     * money totals so the overview cards can render without a second request.
     */
    public function index(string $type)
    {
        abort_unless(in_array($type, TaskList::TYPES, true), 404);

        $lists = TaskList::query()
            ->where('list_type', $type)
            ->orderBy('position')
            ->withCount('tasks')
            ->addSelect(['items_total' => Task::selectRaw('COALESCE(SUM(amount * COALESCE(quantity, 1)), 0)')
                ->whereColumn('list_id', 'lists.id')])
            ->addSelect(['done_total' => Task::selectRaw('COALESCE(SUM(amount * COALESCE(quantity, 1)), 0)')
                ->whereColumn('list_id', 'lists.id')->where('done', true)])
            ->get();

        return response()->json($lists);
    }

    /**
     * POST /api/lists
     * Create a new list at the end of its type's order.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'list_type' => ['required', Rule::in(TaskList::TYPES)],
            'title' => ['required', 'string', 'max:255'],
            'budget' => ['nullable', 'numeric', 'min:0'],
        ]);

        // New lists go to the top (newest-first): push existing ones down by one.
        $list = DB::transaction(function () use ($data) {
            TaskList::where('list_type', $data['list_type'])->increment('position');
            $data['position'] = 0;
            return TaskList::create($data);
        });

        return response()->json($list, 201);
    }

    /**
     * POST /api/lists/{list}/duplicate
     * Replicate a list (title + " (Copy)") and all of its tasks, placed at the
     * top of its type. Done-state of items is preserved, mirroring the old app.
     */
    public function duplicate(TaskList $list)
    {
        $copy = DB::transaction(function () use ($list) {
            TaskList::where('list_type', $list->list_type)->increment('position');

            $copy = $list->replicate(['position']);
            $copy->title = $list->title . ' (Copy)';
            $copy->position = 0;
            $copy->save();

            $pos = 0;
            foreach ($list->tasks()->get() as $task) {
                $new = $task->replicate();
                $new->list_id = $copy->id;
                $new->position = $pos++;
                $new->save();
            }

            return $copy;
        });

        return response()->json($copy, 201);
    }

    /**
     * PATCH /api/lists/{list}
     * Rename a list / change its budget.
     */
    public function update(Request $request, TaskList $list)
    {
        $data = $request->validate([
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'budget' => ['sometimes', 'nullable', 'numeric', 'min:0'],
        ]);

        $list->update($data);

        return response()->json($list);
    }

    /**
     * DELETE /api/lists/{list}
     * Delete a list and (via FK cascade) all its tasks.
     */
    public function destroy(TaskList $list)
    {
        $list->delete();

        return response()->json(['ok' => true]);
    }

    /**
     * PATCH /api/lists/reorder
     * Persist a new ordering of lists. Body: { ids: [3, 1, 2, ...] } in the
     * desired order; positions are rewritten to match the array index.
     */
    public function reorder(Request $request)
    {
        $data = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'exists:lists,id'],
        ]);

        DB::transaction(function () use ($data) {
            foreach ($data['ids'] as $position => $id) {
                TaskList::where('id', $id)->update(['position' => $position]);
            }
        });

        return response()->json(['ok' => true]);
    }
}
