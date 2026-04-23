<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

use App\Models\Kategori;
use App\Models\Faq;
use App\Models\Informasi;
use App\Models\Berita;
use App\Models\Produk;
use App\Models\Banner;
use App\Models\Member;

use Illuminate\Support\Str;

class PublikController extends Controller {

  public function index() {
    $expiration = env('REDIS_TIME', 86400);
    $banners = Cache::remember('banner_data', $expiration, function () {
        return Banner::where('status', 1)->get();
    });

    return view('publik.main', compact('banners'));
    // return redirect()->away('https://app.abpoutsourching.com/');
  }

  public function berita() {

    $expiration = env('REDIS_TIME', 86400);
    $beritas = Cache::remember('beritas_data', $expiration, function () {
        return Berita::all();
    });
    
    return view('publik.berita', compact('beritas'));
    // return redirect()->away('https://app.abpoutsourching.com/');
  }
   

}
