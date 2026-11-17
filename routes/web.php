<?php

use App\Http\Controllers\Api\BukuController as ApiBukuController;
use App\Http\Controllers\Siswa\PeminjamanController;
use App\Http\Controllers\Admin\LaporanController;
use App\Http\Controllers\Admin\TransaksiController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\BukuController;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

// Halaman utama
Route::get('/', function () {
    return view('welcome');
});

// Daftar dan detail buku
Route::get('/buku', [BukuController::class, 'index'])
    ->name('buku.index');

// GATE: KHUSUS ADMIN DAN PETUGAS
Route::middleware(['auth', 'can:kelola-buku'])->group(function () {
    Route::get('/buku/create', [BukuController::class, 'index'])
        ->name('buku.create');
});

Route::get('/buku/{id}', [BukuController::class, 'show'])
    ->name('buku.show');

// ENDPOINT AJAX BUKU (MENGGUNAKAN SESSION WEB LANGSUNG)
Route::prefix('ajax')->group(function () {
    Route::get('/buku', [ApiBukuController::class, 'index']);

    Route::middleware(['auth', 'can:kelola-buku'])->group(function () {
        Route::post('/buku', [ApiBukuController::class, 'store']);
        Route::put('/buku/{id}', [ApiBukuController::class, 'update']);
        Route::delete('/buku/{id}', [ApiBukuController::class, 'destroy']);
    });
});

// Dashboard umum
Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'role:admin|petugas|siswa'])
  ->name('dashboard');

// ADMIN DAN PETUGAS
Route::middleware(['auth', 'role:admin|petugas'])->group(function () {
    Route::get('/admin/dashboard', function () {
        return view('dashboard');
    })->middleware('role:admin')
      ->name('admin.dashboard');

    Route::get('/petugas/dashboard', function () {
        return view('dashboard');
    })->middleware('role:petugas')
      ->name('petugas.dashboard');

    Route::get('/admin/pengajuan-peminjaman',
        [TransaksiController::class, 'pengajuan'])
        ->middleware('permission:memproses-persetujuan-peminjaman')
        ->name('admin.pengajuan');

    Route::post('/admin/pengajuan-peminjaman/{id}/setujui',
        [TransaksiController::class, 'setujui'])
        ->middleware('permission:memproses-persetujuan-peminjaman')
        ->name('admin.pengajuan.setujui');

    Route::post('/admin/pengajuan-peminjaman/{id}/tolak',
        [TransaksiController::class, 'tolak'])
        ->middleware('permission:memproses-persetujuan-peminjaman')
        ->name('admin.pengajuan.tolak');

    Route::get('/admin/peminjaman',
        [TransaksiController::class, 'peminjaman'])
        ->middleware('permission:kelola-peminjaman')
        ->name('admin.peminjaman');

    Route::get('/admin/pengembalian',
        [TransaksiController::class, 'pengembalian'])
        ->middleware('permission:kelola-pengembalian')
        ->name('admin.pengembalian');

    Route::get('/admin/laporan',
        [LaporanController::class, 'index'])
        ->middleware('permission:lihat-laporan')
        ->name('admin.laporan');

    Route::post('/admin/pengembalian/{id}/catat',
        [TransaksiController::class, 'catatPengembalian'])
        ->name('admin.pengembalian.catat');
});

// HAPUS BUKU: HANYA ADMIN DAN PETUGAS
Route::delete('/buku/{id}', [ApiBukuController::class, 'destroy'])
    ->middleware(['auth', 'can:kelola-buku'])
    ->name('buku.destroy');

// KHUSUS ADMIN
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::resource('/admin/users', UserController::class)
        ->names('admin.users')
        ->except(['show']);
});

// KHUSUS SISWA
Route::middleware(['auth', 'role:siswa'])->group(function () {
    Route::get('/siswa/dashboard', function () {
        return view('dashboard');
    })->name('siswa.dashboard');

    Route::get('/siswa/peminjaman',
        [PeminjamanController::class, 'index'])
        ->middleware('permission:mengajukan-peminjaman')
        ->name('siswa.peminjaman.index');

    Route::post('/siswa/peminjaman/{id}',
        [PeminjamanController::class, 'store'])
        ->middleware('permission:mengajukan-peminjaman')
        ->name('siswa.peminjaman.store');

    Route::get('/siswa/riwayat-peminjaman',
        [PeminjamanController::class, 'riwayat'])
        ->middleware('permission:lihat-status-peminjaman-sendiri|lihat-riwayat-peminjaman-sendiri')
        ->name('siswa.peminjaman.riwayat');
});

// PROFIL PENGGUNA
Route::middleware(['auth'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])
        ->name('profile.edit');

    Route::patch('/profile', [ProfileController::class, 'update'])
        ->name('profile.update');

    Route::delete('/profile', [ProfileController::class, 'destroy'])
        ->name('profile.destroy');
});

// Autentikasi Breeze
require __DIR__.'/auth.php';