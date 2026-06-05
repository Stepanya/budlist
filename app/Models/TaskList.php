<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;

class TaskList extends Model
{
    use HasFactory;

    /** Maps to the `lists` table (class is named TaskList because `List` is reserved-ish in PHP). */
    protected $table = 'lists';

    protected $fillable = ['user_id', 'list_type', 'title', 'budget', 'position'];

    protected $casts = [
        'budget' => 'decimal:2',
        'position' => 'integer',
    ];

    /** The three valid list types. */
    public const TYPES = ['budget', 'loan', 'shopping'];

    /**
     * Scope every web query to the signed-in user, and stamp new lists with
     * their owner automatically. CLI (e.g. the importer, which uses the query
     * builder directly) runs with no auth, so the scope is inert there.
     */
    protected static function booted(): void
    {
        static::addGlobalScope('owner', function (Builder $query) {
            if (Auth::check()) {
                $query->where($query->getModel()->getTable() . '.user_id', Auth::id());
            }
        });

        static::creating(function (TaskList $list) {
            if (empty($list->user_id) && Auth::check()) {
                $list->user_id = Auth::id();
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** Items belonging to this list, always ordered by position. */
    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class, 'list_id')->orderBy('position');
    }
}
