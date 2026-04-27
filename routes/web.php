<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Auth\MemberLoginController;
use App\Http\Controllers\Auth\MemberRegisterController;
use App\Http\Controllers\Auth\MemberForgotPasswordController;
use App\Http\Controllers\Auth\MemberResetPasswordController;
use App\Http\Controllers\Auth\SocialLoginController;



use App\Http\Controllers\PublikController;
use App\Http\Controllers\Member\PrediksiMember;
use App\Http\Controllers\Member\DashboardMember;

use App\Http\Controllers\CacheController;
use App\Http\Controllers\ChecklistController;
use App\Middleware\CheckMaintenanceMode;




// Route::get('/', function () {
//     return view('welcome');
// });

Route::middleware(CheckMaintenanceMode::class)->group(function () {

    Route::get('/', [PublikController::class, 'index'])->name('publik');
    Route::get('/faq', [PublikController::class, 'faq'])->name('faq');
    Route::get('/news', [PublikController::class, 'berita'])->name('berita');
    Route::get('/news/{id}', [PublikController::class, 'berita_detail'])->name('berita.detail');
    Route::get('/catalog', [PublikController::class, 'katalog'])->name('katalog');

    Route::get('/login', function () {
        return redirect()->route('member.login');
    })->name('login');

    Route::get('/test-email', function () {
        \Illuminate\Support\Facades\Mail::raw('Ini hanya test email', function ($message) {
            $message->to('gilaprediksi88@gmail.com')
                ->subject('Test Email');
        });
        return 'Email dikirim';
    });

    Route::middleware('auth')->get('/backend/clear-cache', [CacheController::class, 'clearAll']);

    Route::get('/produk/{id}/varians', [DashboardMember::class, 'getVarians']);
    Route::post('/cek-poin', [DashboardMember::class, 'cekPoin'])->name('cek-poin');
});

Route::get('/maintenance', function () {
    return view('maintenance');
});





// Login Member
Route::prefix('member')->group(function () {


    Route::get('/login/{provider}', [SocialLoginController::class, 'redirectToProvider'])->name('member.social.login');

    Route::get('/login/{provider}/callback', [SocialLoginController::class, 'handleProviderCallback'])->name('member.social.login.callback');

    Route::get('/login', [MemberLoginController::class, 'showLoginForm'])->name('member.login');
    Route::post('/login', [MemberLoginController::class, 'login'])->name('member.login.submit');

    // Register Member
    Route::get('/register', [MemberRegisterController::class, 'showRegisterForm'])->name('member.register');
    Route::post('/register', [MemberRegisterController::class, 'register'])->name('member.register.submit');

    // Forgot Password
    Route::get('password/reset', [MemberForgotPasswordController::class, 'showLinkRequestForm'])->name('member.password.request');
    Route::post('password/email', [MemberForgotPasswordController::class, 'sendResetLinkEmail'])->name('member.password.email');

    // Reset Password
    Route::get('password/reset/{token}', [MemberResetPasswordController::class, 'showResetForm'])->name('member.password.reset');
    Route::post('password/reset', [MemberResetPasswordController::class, 'reset'])->name('member.password.update');

    // Logout & Dashboard (dengan middleware)
    Route::get('/logout', [MemberLoginController::class, 'logout'])->name('member.logout');

    Route::get('/dashboard', [DashboardMember::class, 'index'])->middleware('auth:member')->name('member.dashboard');
    Route::get('/profil', [DashboardMember::class, 'profilMember'])->middleware('auth:member')->name('member.profil');
    Route::post('/profil_update', [DashboardMember::class, 'updateProfil'])->middleware('auth:member')->name('member.profil_update');
});

Route::get('/checklist', [ChecklistController::class, 'index'])->name('checklist.index')->middleware('auth:member');
Route::post('/checklist/inputdata', [ChecklistController::class, 'inputdata'])->name('checklist.inputdata')->middleware('auth:member');
Route::post('/checklist/submit', [ChecklistController::class, 'submit'])->name('checklist.submit')->middleware('auth:member');
Route::get('/checklist/success', [ChecklistController::class, 'success'])->name('checklist.success')->middleware('auth:member');



// Route::get('/member/profile', [ProfileController::class, 'index'])
//   ->middleware('auth:member');

// if (Auth::guard('member')->check()) {
//   // Member terautentikasi
// }

Route::get('/debug-permission', function () {
    if (!auth()->check()) {
        return 'Not logged in';
    }

    $user = auth()->user();

    return [
        'user_id' => $user->id,
        'user_email' => $user->email,
        'has_view_users' => $user->can('view users'),
        'all_permissions' => $user->getAllPermissions()->pluck('name')->toArray(),
        'user_roles' => $user->getRoleNames()->toArray(),
        'guard_name' => $user->guard_name ?? 'no guard',
        'auth_guard' => auth()->getDefaultDriver(),
    ];
})->middleware('auth');
