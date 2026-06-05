<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserList extends Model
{
    use HasFactory;

    protected $fillable = ['type', 'title', 'budget', 'state'];

    public function items()
    {
        return $this->hasMany(ListItem::class, 'list_id');
    }
}
