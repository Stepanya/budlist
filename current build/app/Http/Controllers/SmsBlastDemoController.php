<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SmsBlastDemoController extends Controller
{
    public function index() {
        return view('sms-blast');
    }

    public function send(Request $request) {
        return response()->json($request);
    }
}
