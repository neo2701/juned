<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

use App\Http\Controllers\MerkleTreeController;
use App\Http\Controllers\PemiluController;
use App\Http\Controllers\KandidatController;
use App\Http\Controllers\PemilihController;
use App\Http\Controllers\SuaraController;
use App\Http\Controllers\VoterAuthController;
use App\Http\Controllers\AuditController;

// Voter Routes
Route::prefix('voter')->name('voter.')->group(function () {
    Route::get('login', [VoterAuthController::class, 'create'])->name('login');
    Route::post('login', [VoterAuthController::class, 'store']);
    Route::post('logout', [VoterAuthController::class, 'destroy'])->name('logout');

    // Protected Voter Routes
    Route::middleware([\App\Http\Middleware\VoterMiddleware::class])->group(function () {
        Route::get('dashboard', function () {
            $elections = \App\Models\Pemilu::where('status', 'BERJALAN')
                ->with('kandidats')
                ->get();

            return Inertia::render('Voter/Dashboard', [
                'elections' => $elections,
            ]);
        })->name('dashboard');

        // Voter API Routes
        Route::get('api/merkle-tree/{pemilu}', [MerkleTreeController::class, 'show'])->name('api.merkle-tree');
        Route::get('api/elections', [SuaraController::class, 'elections'])->name('api.elections');
        Route::post('api/vote', [SuaraController::class, 'store'])->name('api.vote');
    });
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Admin Routes
    Route::prefix('admin')->name('admin.')->group(function () {
        Route::resource('pemilu', PemiluController::class);
        Route::resource('pemilu.kandidat', KandidatController::class)->except(['show']);
        Route::resource('pemilih', PemilihController::class)->except(['show', 'edit', 'update']);
        Route::post('pemilu/{pemilu}/generate-tree', [MerkleTreeController::class, 'generate'])->name('pemilu.generate-tree');

        // Audit & Verification Routes
        Route::get('pemilu/{pemilu}/audit', [AuditController::class, 'index'])->name('pemilu.audit');
        Route::post('pemilu/{pemilu}/verify/{suara}', [AuditController::class, 'verifySingle'])->name('pemilu.verify-single');
        Route::post('pemilu/{pemilu}/verify-all', [AuditController::class, 'verifyAll'])->name('pemilu.verify-all');
        Route::post('pemilu/{pemilu}/audit-tree', [AuditController::class, 'auditTree'])->name('pemilu.audit-tree');
    });
});

require __DIR__ . '/auth.php';
