<?php

namespace App\Http\Controllers;

use App\Models\ListItem;
use App\Models\UserList;
use Illuminate\Http\Request;

class ListItemController extends Controller
{
    public function store(Request $request)
    {

        $validated = $request->validate([
            'list_id' => 'required|exists:user_lists,id',
            'item_name' => 'required|string',
            'price' => 'numeric',
            'quantity' => 'nullable|integer',
            'note' => 'nullable|string',
            'date' => 'nullable|date',
            'checked' => 'boolean',
        ]);

        ListItem::create($validated);

        return back();
    }

    public function update(Request $request, $id)
    {
        $item = ListItem::findOrFail($id);

        $validated = $request->validate([
            'item_name' => 'string',
            'price' => 'numeric',
            'quantity' => 'nullable|integer',
            'note' => 'nullable|string',
            'date' => 'nullable|date',
            'checked' => 'boolean',
        ]);

        $item->update($validated);

        $list = UserList::with('items')->findOrFail($item->list_id);

        if ($request->has('fromForm')) {
            return back();
        }

        return response()->json(['list' => $list]);
    }

    public function destroy($id)
    {
        ListItem::findOrFail($id)->delete();
        return back();
    }
}
