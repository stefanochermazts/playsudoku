<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SimpleTestController extends Controller
{
    public function test()
    {
        return '<h1>Test Controller Works</h1><p>This means the routing is working.</p>';
    }
    
    public function livewireTest()
    {
        return view('test-livewire');
    }
}
