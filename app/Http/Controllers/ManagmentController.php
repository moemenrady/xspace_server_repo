<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ManagmentController extends Controller
{
  public function create(){
    return view("managment.create");
  }
}
