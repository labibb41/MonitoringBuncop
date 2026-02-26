<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
class IncubatorController extends Controller
{
    public function index()
    {
        return view('incubator.index');
    }
}