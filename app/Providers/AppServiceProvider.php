<?php

namespace App\Providers;

use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Carbon::setLocale('id');

        // Nama toko untuk judul halaman & sidebar, dibaca sekali per request.
        View::composer(['layouts.base', 'layouts.panel', 'auth.*'], function ($view) {
            static $nama;
            if ($nama === null) {
                try {
                    $nama = Setting::toko()->nama_usaha ?: config('app.name');
                } catch (\Throwable $e) {
                    $nama = config('app.name');
                }
            }
            $view->with('namaToko', $nama);
        });
        //
    }
}
