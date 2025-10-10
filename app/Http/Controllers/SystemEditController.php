<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SystemEditController extends Controller
{
    public function create(){
      return view("managment.changes.create");
    }
}
