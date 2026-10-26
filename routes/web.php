<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\KasirController;
use App\Http\Controllers\KategoriController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\ModalController;
use App\Http\Controllers\PengeluaranController;
use App\Http\Controllers\PenjualanController;
use App\Http\Controllers\ProdukController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\StokController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

/*
| Halaman (GET tanpa /data) merender kerangka Blade; seluruh data diambil
| dan disimpan lewat endpoint JSON sehingga tidak ada reload penuh.
| Level: 1 = admin, 2 = kasir.
*/

Route::get('/', function () {
    return redirect()->route('login');
});

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Admin & kasir
    Route::middleware('level:1,2')->group(function () {
        Route::get('/kasir', [KasirController::class, 'index'])->name('kasir.index');
        Route::get('/kasir/menu', [KasirController::class, 'menu'])->name('kasir.menu');
        Route::post('/kasir/checkout', [KasirController::class, 'checkout'])->name('kasir.checkout');
        Route::get('/kasir/riwayat', [KasirController::class, 'riwayat'])->name('kasir.riwayat');
        Route::get('/kasir/tahan', [KasirController::class, 'tahanIndex'])->name('kasir.tahan');
        Route::post('/kasir/tahan', [KasirController::class, 'tahanSimpan'])->name('kasir.tahan.simpan');
        Route::delete('/kasir/tahan/{id}', [KasirController::class, 'tahanHapus'])->name('kasir.tahan.hapus');
        Route::get('/kasir/nota/{id}', [KasirController::class, 'nota'])->name('kasir.nota');
        Route::get('/kasir/tutup', [KasirController::class, 'tutup'])->name('kasir.tutup');
        Route::get('/kasir/tutup/data', [KasirController::class, 'tutupData'])->name('kasir.tutup.data');

        Route::get('/pengeluaran', [PengeluaranController::class, 'index'])->name('pengeluaran.index');
        Route::get('/pengeluaran/data', [PengeluaranController::class, 'data'])->name('pengeluaran.data');
        Route::post('/pengeluaran', [PengeluaranController::class, 'store'])->name('pengeluaran.store');
        Route::put('/pengeluaran/{id}', [PengeluaranController::class, 'update'])->name('pengeluaran.update');
        Route::delete('/pengeluaran/{id}', [PengeluaranController::class, 'destroy'])->name('pengeluaran.destroy');

        Route::get('/profil', [UserController::class, 'profil'])->name('user.profil');
        Route::post('/profil', [UserController::class, 'updateProfil'])->name('user.update_profil');
    });

    // Admin saja
    Route::middleware('level:1')->group(function () {
        Route::get('/dashboard/data', [DashboardController::class, 'data'])->name('dashboard.data');

        Route::get('/penjualan', [PenjualanController::class, 'index'])->name('penjualan.index');
        Route::get('/penjualan/data', [PenjualanController::class, 'data'])->name('penjualan.data');
        Route::get('/penjualan/{id}', [PenjualanController::class, 'show'])->name('penjualan.show');
        Route::delete('/penjualan/{id}', [PenjualanController::class, 'destroy'])->name('penjualan.destroy');

        Route::get('/produk', [ProdukController::class, 'index'])->name('produk.index');
        Route::get('/produk/data', [ProdukController::class, 'data'])->name('produk.data');
        Route::post('/produk', [ProdukController::class, 'store'])->name('produk.store');
        Route::put('/produk/{id}', [ProdukController::class, 'update'])->name('produk.update');
        Route::delete('/produk/{id}', [ProdukController::class, 'destroy'])->name('produk.destroy');

        Route::get('/kategori', [KategoriController::class, 'index'])->name('kategori.index');
        Route::get('/kategori/data', [KategoriController::class, 'data'])->name('kategori.data');
        Route::post('/kategori', [KategoriController::class, 'store'])->name('kategori.store');
        Route::put('/kategori/{id}', [KategoriController::class, 'update'])->name('kategori.update');
        Route::delete('/kategori/{id}', [KategoriController::class, 'destroy'])->name('kategori.destroy');

        Route::get('/stok', [StokController::class, 'index'])->name('stok.index');
        Route::get('/stok/mutasi', [StokController::class, 'mutasi'])->name('stok.mutasi');
        Route::post('/stok', [StokController::class, 'store'])->name('stok.store');

        Route::get('/modal', [ModalController::class, 'index'])->name('modal.index');
        Route::get('/modal/data', [ModalController::class, 'data'])->name('modal.data');
        Route::post('/modal', [ModalController::class, 'store'])->name('modal.store');
        Route::put('/modal/{id}', [ModalController::class, 'update'])->name('modal.update');
        Route::delete('/modal/{id}', [ModalController::class, 'destroy'])->name('modal.destroy');

        Route::get('/laporan', [LaporanController::class, 'index'])->name('laporan.index');
        Route::get('/laporan/data', [LaporanController::class, 'data'])->name('laporan.data');

        Route::get('/user', [UserController::class, 'index'])->name('user.index');
        Route::get('/user/data', [UserController::class, 'data'])->name('user.data');
        Route::post('/user', [UserController::class, 'store'])->name('user.store');
        Route::put('/user/{id}', [UserController::class, 'update'])->name('user.update');
        Route::delete('/user/{id}', [UserController::class, 'destroy'])->name('user.destroy');

        Route::get('/pengaturan', [SettingController::class, 'index'])->name('setting.index');
        Route::post('/pengaturan', [SettingController::class, 'update'])->name('setting.update');
    });
});
