<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;


class MemberLoginController extends Controller {
  public function showLoginForm() {
    return view('auth.member_login'); // View login khusus member
  }

  // public function login(Request $request) {
  //   $credentials = $request->validate([
  //     'email' => 'required|email',
  //     'password' => 'required',
  //   ]);

  //   if (Auth::guard('member')->attempt($credentials)) {
  //     return redirect()->intended('/'); // Redirect setelah login
  //   }

  //   return back()->withErrors(['email' => 'Kredensial tidak valid']);
  // }

  public function login(Request $request)
  {
      

       if(env('RECAPTCHA_V2') == 1){
        $credentials = $request->validate([
          'email' => 'required|email',
          'password' => 'required',
          'recaptcha_token' => 'required'
        ]);
        // Verifikasi token dengan Google
        $response = Http::asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
            'secret' => env('RECAPTCHA_SECRET_KEY'),
            'response' => $request->recaptcha_token,
            'remoteip' => $request->ip(),
        ]);

        $result = $response->json();

        if (!($result['success'] ?? false) || ($result['score'] ?? 0) < 0.5) {
            return back()->withErrors(['email' => 'Verifikasi keamanan gagal.']);
        }
      }else{

        $credentials = $request->validate([
          'email' => 'required|email',
          'password' => 'required',
          // 'recaptcha_token' => 'required'
        ]);

      }

      // Autentikasi pengguna
      if (Auth::guard('member')->attempt([
          'email' => $request->email,
          'password' => $request->password,
          'status' => 1,
      ])) {
          return redirect()->intended('/');
      }

      return back()->withErrors(['email' => 'Kredensial tidak valid']);
  }

  public function logout() {
    Auth::guard('member')->logout();
    return redirect('/member/login');
  }

  public function dashboard() {
    return view('publik.member.dashboard');
  }
}