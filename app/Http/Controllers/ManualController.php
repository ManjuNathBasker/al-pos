<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ManualController extends Controller
{
    /**
     * Display the User Manual documentation page.
     */
    public function index()
    {
        return view('manual.index');
    }
}
