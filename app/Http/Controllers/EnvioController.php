<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class EnvioController extends Controller
{
    public function index(){
        return view('envios.index');
    }
}
