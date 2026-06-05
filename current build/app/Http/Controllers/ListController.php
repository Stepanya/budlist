<?php

namespace App\Http\Controllers;

use App\Models\UserList;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ListController extends Controller
{
    public function getBudgetLists()
    {
        $result = UserList::where('type', 'budget')->where('state', '1')->with('items')->orderBy('created_at', 'DESC')->get();
        return view('budlist.list', ['lists' => $result, 'title' => 'Budget', 'type' => 'budget']);
    }

    public function getLoanLists()
    {
        $result = UserList::where('type', 'loan')->where('state', '1')->with('items')->orderBy('created_at', 'DESC')->get();
        return view('budlist.list', ['lists' => $result, 'title' => 'Loan', 'type' => 'loan']);
    }

    public function getShoppingLists()
    {
        $result = UserList::where('type', 'shopping')->where('state', '1')->with('items')->orderBy('created_at', 'DESC')->get();
        return view('budlist.list', ['lists' => $result, 'title' => 'Shopping', 'type' => 'shopping']);
    }

    public function getArchivedLists($type)
    {
        $label = ucfirst($type);
        $result = UserList::where('type', $type)->where('state', '0')->with('items')->orderBy('created_at', 'DESC')->get();

        return view('budlist.list', [
            'lists' => $result,
            'title' => "Archived {$label}",
            'type' => $type,
            'archived' => true,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|string',
            'title' => 'required|string',
            'budget' => 'nullable|numeric',
            'state' => 'string|in:1,0',
        ]);

        $result = UserList::create($validated);

        return redirect("/budlist/{$result->type}/{$result->id}");
    }

    public function show(Request $request, $id)
    {
        // Get the sorting parameter from the request
        // $sortBy = $request->query('sort', 'date'); // Default to 'date' if no sort param is provided
        $sortBy = $request->query('sort', 'date'); // Default to 'date' if no sort param is provided
        
        // Retrieve the list and its associated items with sorting
        $result = UserList::with([
            'items' => function ($query) use ($sortBy) {
                // $query->where('item_name', 'like', '%kath%');
                if ($sortBy === 'title') {
                    $query->orderBy('item_name', 'DESC');
                } elseif ($sortBy === 'amount') {
                    $query->orderByRaw('(price * COALESCE(quantity, 1)) DESC'); // Sort by total price
                } elseif ($sortBy === 'date') {
                    $query->orderByRaw('COALESCE(date, "9999-12-31") DESC'); // Sort by newest items first
                } else {
                    $query->orderBy('checked')->orderBy('id'); // Default sorting by checked status & ID
                }
            }
        ])->findOrFail($id);

        // Calculate the sum of all prices, accounting for quantity
        $totalPrice = $result->items->sum(fn($item) => $item->price * ($item->quantity ?? 1));

        // Calculate the sum of checked items
        $checkedPrice = $result->items->where('checked', true)->sum(fn($item) => $item->price * ($item->quantity ?? 1));

        // Calculate the sum of unchecked items
        $uncheckedPrice = $result->items->where('checked', false)->sum(fn($item) => $item->price * ($item->quantity ?? 1));
        
        // Return the view with the sorted list
        return view('budlist.list-item', [
            'list' => $result,
            'title' => $result->title,
            'type' => $result->type,
            'id' => $id,
            'totalPrice' => $totalPrice,
            'checkedPrice' => $checkedPrice,
            'uncheckedPrice' => $uncheckedPrice,
            'sortBy' => $sortBy, // Pass sort parameter to the view
        ]);
    }

    public function update(Request $request, $id)
    {
        $list = UserList::findOrFail($id);

        $validated = $request->validate([
            'type' => 'string',
            'title' => 'string',
            'budget' => 'nullable|numeric',
            'state' => 'string|in:1,0',
        ]);

        $list->update($validated);

        return back();
    }

    public function destroy($id)
    {
        UserList::findOrFail($id)->delete();
        return back();
    }

    public function duplicate($type, $id)
    {
        $source = UserList::where('type', $type)->with('items')->findOrFail($id);

        $newList = DB::transaction(function () use ($source) {
            $copy = $source->replicate();
            $copy->title = "{$source->title} (Copy)";
            $copy->save();

            foreach ($source->items as $item) {
                $newItem = $item->replicate();
                $newItem->list_id = $copy->id;
                $newItem->save();
            }

            return $copy;
        });

        return redirect("/budlist/{$newList->type}/{$newList->id}");
    }

    public function archive($type, $id)
    {
        $list = UserList::where('type', $type)->findOrFail($id);
        $list->update(['state' => '0']);

        return back();
    }

    public function unarchive($type, $id)
    {
        $list = UserList::where('type', $type)->findOrFail($id);
        $list->update(['state' => '1']);

        return back();
    }
}
