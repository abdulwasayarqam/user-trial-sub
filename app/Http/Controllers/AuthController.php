<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class AuthController extends Controller
{
    //
    public function loadRegister()
    {
        return view('register');
    }

    public function userRegister(Request $request)
    {
        $isExists = User::where(['email' => $request->email])->first();

        if($isExists){
            return redirect()->back()->with('error','Email already exists!');
        }

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password)
        ]);

        return redirect()->back()->with('success','Registration Successfully!');
    }

    public function loadLogin()
    {
        return view('login');
    }

    public function userLogin(Request $request)
    {
        $userCredentials = $request->only('email','password');

        if(Auth::attempt($userCredentials)){
            return redirect('/dashboard');
        }

        return back()->with('error','Username & Password is incorrect!');

    }

    public function dashboard()
    {
        return view('dashboard');
    }

    public function logout(Request $request)
    {
        try{

            $request->session()->flush();
            Auth::logout();
            return response()->json(['success' => true]);

        }
        catch(\Exception $e){
            return response()->json(['success' => false]);
        }
    }
}
