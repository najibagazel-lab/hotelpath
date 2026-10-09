<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function create() { return view('auth.login'); }
    public function store(Request $request)
    {
        $data = $request->validate(['username' => ['required','string'], 'password' => ['required']]);
        if (Auth::attempt($data) && auth()->user()->active) { $request->session()->regenerate(); return redirect()->intended('/'); }
        Auth::logout();
        return back()->withErrors(['username' => 'Identifiants invalides ou compte désactivé.'])->onlyInput('username');
    }
    public function destroy(Request $request)
    {
        Auth::logout(); $request->session()->invalidate(); $request->session()->regenerateToken();
        return redirect()->route('login');
    }

    public function editPassword()
    {
        return view('auth.password');
    }

    public function updatePassword(Request $request)
    {
        $data = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'string', 'confirmed'],
        ], [
            'current_password.current_password' => 'Current password is incorrect.',
        ]);

        $request->user()->update(['password' => Hash::make($data['password'])]);

        return redirect()->route('password.edit')->with('success', 'Your password has been changed.');
    }
}
