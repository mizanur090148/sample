<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\LoginRequest;
use Session;
use Auth;

class AuthController extends Controller
{
    public function login()
    {
    	return view('backend.pages.login');
    }

    public function loginPost(LoginRequest $request)
    {
      	/*$field = filter_var($request->usernameOrEmail, FILTER_VALIDATE_EMAIL) ? 'email' : 'personal_code';
        $request->merge([$field => $request->usernameOrEmail]);*/

        try {
        	$credentials = $request->only('email', 'password');        	
        	if (Auth::attempt($credentials)) {
        		return redirect('dashboard');
        	}   			
 		} catch (Exception $e) {dd($e->getMessage());
 			Session::flash('error', 'Sorry!! Your credentials mismatch');
 		} 		
 		return redirect()->back();
    }
    public function logout()
    {
    	Auth::logout();
    	Session::flush();
    	return redirect('/');
    }
}
