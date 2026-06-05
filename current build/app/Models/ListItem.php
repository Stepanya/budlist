<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ListItem extends Model
{
    use HasFactory;

    protected $fillable = ['list_id', 'item_name', 'price', 'quantity', 'note', 'date', 'checked'];

    public function list()
    {
        return $this->belongsTo(UserList::class);
    }
}
