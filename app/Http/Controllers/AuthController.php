<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AuthController extends Controller
{
    // ----- VIEWS -----
    // Login
    public function loginView() {
        $data = [
            'title' => 'Login | '.config('app.name'),
        ];
        return view('auth.login', $data);
    }

    // Register
    public function registerView() {
        $data = [
            'title' => 'Register | '.config('app.name'),
        ];
        return view('auth.register', $data);
    }

    // ----- IDK HOW TO CALL THIS BUT LIKE THE FUNCTIONS I GUESS -----
    // Login

    // Register
}
