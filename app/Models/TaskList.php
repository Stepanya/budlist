<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TaskList extends Model
{
    use HasFactory;

    /** Maps to the `lists` table (class is named TaskList because `List` is reserved-ish in PHP). */
    protected $table = 'lists';

    protected $fillable = ['list_type', 'title', 'budget', 'position'];

    protected $casts = [
        'budget' => 'decimal:2',
        'position' => 'integer',
    ];

    /** The three valid list types. */
    public const TYPES = ['budget', 'loan', 'shopping'];

    /** Items belonging to this list, always ordered by position. */
    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class, 'list_id')->orderBy('position');
    }
}
