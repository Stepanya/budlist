<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class BudlistController extends Controller
{
    public function index() {
        return view('budlist.index');
    }
    
    public function budget() {
        return view('budlist.list');
    }

    public function loans() {
        return view('budlist.list');
    }
    
    public function shopping() {
        return view('budlist.list');
    }
}
