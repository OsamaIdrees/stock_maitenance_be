<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LoginController extends Controller
{
    //
    public function Login(Request $request){
        $login_name = $request['login_name'];
        $login_password = $request['login_password'];
        $query = DB::table('user_info')->where([['login_name',$login_name],['login_password',$login_password]])->pluck('login_name')->first();
        if($query){
            return response()->json(['success'=>true,'login_name'=>$login_name,'message'=>'Login Successfull']);
        }
        else{
            return  response()->json(['success'=>false,'message'=>'Invalid  Credintials']);
        }
    } 
}
