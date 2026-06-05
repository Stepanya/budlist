<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

class Task extends Model
{
    use HasFactory;

    protected $fillable = ['list_id', 'text', 'amount', 'quantity', 'note', 'due_date', 'done', 'position'];

    protected $casts = [
        'amount' => 'decimal:2',
        'quantity' => 'integer',
        'due_date' => 'date',
        'done' => 'boolean',
        'position' => 'integer',
    ];

    /**
     * Constrain tasks to lists owned by the signed-in user, so a stray task id
     * from someone else's list can never be read or written over the API.
     */
    protected static function booted(): void
    {
        static::addGlobalScope('owner', function (Builder $query) {
            if (Auth::check()) {
                $query->whereIn('tasks.list_id', function ($sub) {
                    $sub->select('id')->from('lists')->where('user_id', Auth::id());
                });
            }
        });
    }

    /** The list this task belongs to. */
    public function list(): BelongsTo
    {
        return $this->belongsTo(TaskList::class, 'list_id');
    }
}
