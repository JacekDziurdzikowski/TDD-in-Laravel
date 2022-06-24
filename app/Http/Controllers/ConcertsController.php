<?php

namespace App\Http\Controllers;

use App\Models\Concert;
use Illuminate\Http\Request;
use Tests\CreatesApplication;

class ConcertsController extends Controller
{

    public function show($id)
    {
        $concert = Concert::published()->findOrFail($id);
        return view('concerts.show', ['concert' => $concert]);
    }
}
