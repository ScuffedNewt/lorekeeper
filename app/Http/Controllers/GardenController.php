<?php namespace App\Http\Controllers;

class GardenController extends Controller
{
    public function getIndex()
    {
        return view('gardens.index');
    }
}
