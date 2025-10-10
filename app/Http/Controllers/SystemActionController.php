<?php

namespace App\Http\Controllers;

use App\Models\SystemAction;
use Illuminate\Http\Request;

class SystemActionController extends Controller
{
public function index(Request $request){
  return view("managment.system_actions");
}


}
