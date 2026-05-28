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
    $totalPemilu = \App\Models\Pemilu::count();
    $activePemilu = \App\Models\Pemilu::where('status', 'BERJALAN')->count();
    $totalPemilih = \App\Models\Pemilih::count();
    $totalSuara = \App\Models\Suara::count();
    $verifiedSuara = \App\Models\Suara::where('status', 'TERVERIFIKASI')->count();
    $pendingSuara = \App\Models\Suara::where('status', 'MASUK')->count();

    $recentElections = \App\Models\Pemilu::withCount(['kandidats', 'suaras'])
        ->latest()
        ->take(5)
        ->get()
        ->map(fn($p) => [
            'id' => $p->id,
            'name' => $p->name,
            'status' => $p->status,
            'tanggal_mulai' => $p->tanggal_mulai?->format('d M Y'),
            'tanggal_selesai' => $p->tanggal_selesai?->format('d M Y'),
            'kandidats_count' => $p->kandidats_count,
            'suaras_count' => $p->suaras_count,
        ]);

    $recentVotes = \App\Models\Suara::with('pemilu:id,name')
        ->latest('waktu_suara')
        ->take(10)
        ->get()
        ->map(fn($s) => [
            'id' => $s->id,
            'pemilu_name' => $s->pemilu?->name,
            'status' => $s->status,
            'waktu_suara' => $s->waktu_suara?->diffForHumans(),
        ]);

    $turnoutPercentage = $totalPemilih > 0 ? round(($totalSuara / $totalPemilih) * 100, 1) : 0;

    return Inertia::render('Dashboard', [
        'stats' => [
            'totalPemilu' => $totalPemilu,
            'activePemilu' => $activePemilu,
            'totalPemilih' => $totalPemilih,
            'totalSuara' => $totalSuara,
            'verifiedSuara' => $verifiedSuara,
            'pendingSuara' => $pendingSuara,
            'turnoutPercentage' => $turnoutPercentage,
        ],
        'recentElections' => $recentElections,
        'recentVotes' => $recentVotes,
    ]);
})->middleware(['auth', 'verified'])->name('dashboard');

use App\Http\Controllers\MerkleTreeController;
use App\Http\Controllers\PemiluController;
use App\Http\Controllers\KandidatController;
use App\Http\Controllers\PemilihController;
use App\Http\Controllers\SuaraController;
use App\Http\Controllers\VoterAuthController;
use App\Http\Controllers\VoterRegistrationController;
use App\Http\Controllers\AuditController;
use App\Http\Controllers\PublicAuditController;
use App\Http\Controllers\ExportController;

// Public Audit Routes (no authentication required)
Route::prefix('audit')->name('public.audit.')->group(function () {
    Route::get('/', [PublicAuditController::class, 'index'])->name('index');
    Route::get('/{pemilu}', [PublicAuditController::class, 'show'])->name('show');
});

// Voter Routes
Route::prefix('voter')->name('voter.')->group(function () {
    Route::get('login', [VoterAuthController::class, 'create'])->name('login');
    Route::post('login', [VoterAuthController::class, 'store']);
    Route::post('logout', [VoterAuthController::class, 'destroy'])->name('logout');

    // Self-Registration Routes (public, no auth required)
    Route::get('register', [VoterRegistrationController::class, 'create'])->name('register');
    Route::post('register/check-nik', [VoterRegistrationController::class, 'checkNik'])->name('register.check');
    Route::post('register', [VoterRegistrationController::class, 'store'])->name('register.store');

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
        Route::post('pemilih/bulk-import', [PemilihController::class, 'bulkImport'])->name('pemilih.bulk-import');
        Route::post('pemilu/{pemilu}/generate-tree', [MerkleTreeController::class, 'generate'])->name('pemilu.generate-tree');

        // Audit & Verification Routes
        Route::get('pemilu/{pemilu}/audit', [AuditController::class, 'index'])->name('pemilu.audit');
        Route::post('pemilu/{pemilu}/verify/{suara}', [AuditController::class, 'verifySingle'])->name('pemilu.verify-single');
        Route::post('pemilu/{pemilu}/verify-all', [AuditController::class, 'verifyAll'])->name('pemilu.verify-all');
        Route::post('pemilu/{pemilu}/audit-tree', [AuditController::class, 'auditTree'])->name('pemilu.audit-tree');

        // Export Routes
        Route::prefix('export')->name('export.')->group(function () {
            Route::get('voters/excel', [ExportController::class, 'votersExcel'])->name('voters.excel');
            Route::get('voters/pdf', [ExportController::class, 'votersPdf'])->name('voters.pdf');
            Route::get('pemilu/{pemilu}/results/excel', [ExportController::class, 'electionResultsExcel'])->name('results.excel');
            Route::get('pemilu/{pemilu}/results/pdf', [ExportController::class, 'electionResultsPdf'])->name('results.pdf');
            Route::get('pemilu/{pemilu}/audit/excel', [ExportController::class, 'auditExcel'])->name('audit.excel');
            Route::get('pemilu/{pemilu}/audit/pdf', [ExportController::class, 'auditPdf'])->name('audit.pdf');
        });
    });
});

require __DIR__ . '/auth.php';
