<?php

namespace App\Http\Controllers;

use App\Models\Pemilih;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;

class PemilihController extends Controller
{
    public function index()
    {
        $pemilihs = Pemilih::latest()->paginate(10);
        return Inertia::render('Admin/Pemilih/Index', [
            'pemilihs' => $pemilihs,
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/Pemilih/Create');
    }

    /**
     * Register a single voter (pre-approve NIK for self-registration).
     */
    public function store(Request $request)
    {
        $request->validate([
            'nik' => 'required|string|unique:pemilih,nik|size:16',
            'nama_pemilih' => 'nullable|string|max:150',
        ]);

        $token = Str::random(64);

        Pemilih::create([
            'nik' => $request->nik,
            'nama_pemilih' => $request->nama_pemilih,
            'registration_status' => 'APPROVED',
            'registration_token' => $token,
        ]);

        return redirect()->route('admin.pemilih.index')->with([
            'success' => 'Voter NIK approved. They can now self-register at the registration page.',
        ]);
    }

    /**
     * Bulk import NIKs from CSV for pre-approval.
     */
    public function bulkImport(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:2048',
        ]);

        $file = $request->file('csv_file');
        $content = file_get_contents($file->getRealPath());
        $lines = array_filter(array_map('trim', explode("\n", $content)));

        $imported = 0;
        $skipped = 0;
        $errors = [];

        foreach ($lines as $index => $line) {
            // Skip header row if it contains non-numeric data
            if ($index === 0 && !preg_match('/^\d{16}/', $line)) {
                continue;
            }

            // Parse CSV: expected format is "NIK,Name" or just "NIK"
            $parts = str_getcsv($line);
            $nik = trim($parts[0] ?? '');
            $nama = trim($parts[1] ?? '');

            // Validate NIK
            if (strlen($nik) !== 16 || !ctype_digit($nik)) {
                $errors[] = "Row " . ($index + 1) . ": Invalid NIK '{$nik}'";
                $skipped++;
                continue;
            }

            // Check if already exists
            if (Pemilih::where('nik', $nik)->exists()) {
                $skipped++;
                continue;
            }

            Pemilih::create([
                'nik' => $nik,
                'nama_pemilih' => $nama ?: null,
                'registration_status' => 'APPROVED',
                'registration_token' => Str::random(64),
            ]);

            $imported++;
        }

        $message = "{$imported} voters imported successfully.";
        if ($skipped > 0) {
            $message .= " {$skipped} skipped (duplicate or invalid).";
        }

        return redirect()->route('admin.pemilih.index')->with([
            'success' => $message,
            'import_errors' => $errors,
        ]);
    }

    public function destroy(Pemilih $pemilih)
    {
        $pemilih->delete();
        return redirect()->route('admin.pemilih.index')->with('success', 'Voter deleted.');
    }
}
