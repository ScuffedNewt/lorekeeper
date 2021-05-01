<?php namespace App\Http\Controllers;

use Illuminate\Http\Request;

class GardenController extends Controller
{
    public function getIndex()
    {
        return view('gardens.index');
    }
}
