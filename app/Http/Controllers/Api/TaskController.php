<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Task;
use App\Models\TaskList;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TaskController extends Controller
{
    /**
     * GET /api/lists/{list}/tasks
     * Return the items of a list, ordered by position.
     */
    public function index(TaskList $list)
    {
        return response()->json($list->tasks()->get());
    }

    /**
     * POST /api/tasks
     * Add a new item to a list, at the end of that list's order.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'list_id' => ['required', 'integer', 'exists:lists,id'],
            'text' => ['required', 'string', 'max:255'],
            'amount' => ['nullable', 'numeric'],
            'quantity' => ['nullable', 'integer', 'min:1'],
            'note' => ['nullable', 'string'],
            'due_date' => ['nullable', 'date'],
            'done' => ['nullable', 'boolean'],
        ]);

        // findOrFail goes through the owner scope: adding to a list you don't own 404s.
        $list = TaskList::findOrFail($data['list_id']);

        $data['position'] = (int) Task::where('list_id', $list->id)->max('position') + 1;

        $task = Task::create($data);

        return response()->json($task, 201);
    }

    /**
     * PATCH /api/tasks/{task}
     * Update any field of an item (toggle done, rename, edit money, etc.).
     */
    public function update(Request $request, Task $task)
    {
        $data = $request->validate([
            'text' => ['sometimes', 'required', 'string', 'max:255'],
            'amount' => ['sometimes', 'nullable', 'numeric'],
            'quantity' => ['sometimes', 'nullable', 'integer', 'min:1'],
            'note' => ['sometimes', 'nullable', 'string'],
            'due_date' => ['sometimes', 'nullable', 'date'],
            'done' => ['sometimes', 'boolean'],
        ]);

        $task->update($data);

        return response()->json($task);
    }

    /**
     * DELETE /api/tasks/{task}
     */
    public function destroy(Task $task)
    {
        $task->delete();

        return response()->json(['ok' => true]);
    }

    /**
     * PATCH /api/lists/{list}/tasks/reorder
     * Persist a new ordering of items within a list. Body: { ids: [...] }.
     * Only ids that actually belong to {list} are touched.
     */
    public function reorder(Request $request, TaskList $list)
    {
        $data = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer'],
        ]);

        DB::transaction(function () use ($data, $list) {
            foreach ($data['ids'] as $position => $id) {
                $list->tasks()->where('id', $id)->update(['position' => $position]);
            }
        });

        return response()->json(['ok' => true]);
    }
}
