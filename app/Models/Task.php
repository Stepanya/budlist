<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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

    /** The list this task belongs to. */
    public function list(): BelongsTo
    {
        return $this->belongsTo(TaskList::class, 'list_id');
    }
}
