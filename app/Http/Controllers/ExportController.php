<?php

namespace App\Http\Controllers;

use App\Models\Pemilih;
use App\Models\Pemilu;
use App\Models\Suara;
use Barryvdh\DomPDF\Facade\Pdf;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Writer\XLSX\Writer;

class ExportController extends Controller
{
    // ─── VOTER EXPORTS ───────────────────────────────────────────────

    /**
     * Export voter list as Excel.
     */
    public function votersExcel()
    {
        $voters = Pemilih::orderBy('id')->get();
        $filename = 'voters-' . now()->format('Y-m-d') . '.xlsx';
        $tempFile = tempnam(sys_get_temp_dir(), 'export_') . '.xlsx';

        $writer = new Writer();
        $writer->openToFile($tempFile);

        $writer->addRow(Row::fromValues(['ID', 'NIK', 'Nama', 'Status', 'Registered At']));

        foreach ($voters as $voter) {
            $writer->addRow(Row::fromValues([
                $voter->id,
                $voter->nik,
                $voter->nama_pemilih ?? '-',
                $voter->registration_status,
                $voter->registered_at?->format('Y-m-d H:i') ?? '-',
            ]));
        }

        $writer->close();

        return response()->download($tempFile, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend(true);
    }

    /**
     * Export voter list as PDF.
     */
    public function votersPdf()
    {
        $voters = Pemilih::orderBy('id')->get();

        $pdf = Pdf::loadView('exports.voters', [
            'voters' => $voters,
            'exportDate' => now()->format('d M Y H:i'),
        ]);

        $pdf->setPaper('a4', 'landscape');

        return $pdf->download('voters-' . now()->format('Y-m-d') . '.pdf');
    }

    // ─── ELECTION RESULTS EXPORTS ────────────────────────────────────

    /**
     * Export election results as Excel.
     */
    public function electionResultsExcel(Pemilu $pemilu)
    {
        $pemilu->load('kandidats');
        $tally = $this->computeTally($pemilu);
        $filename = 'results-' . str_replace(' ', '-', $pemilu->name) . '-' . now()->format('Y-m-d') . '.xlsx';
        $tempFile = tempnam(sys_get_temp_dir(), 'export_') . '.xlsx';

        $writer = new Writer();
        $writer->openToFile($tempFile);

        $writer->addRow(Row::fromValues(["Election Results: {$pemilu->name}"]));
        $writer->addRow(Row::fromValues(["Status: {$pemilu->status}"]));
        $writer->addRow(Row::fromValues(['Exported: ' . now()->format('d M Y H:i')]));
        $writer->addRow(Row::fromValues(['']));

        $writer->addRow(Row::fromValues(['No. Urut', 'Candidate', 'Verified Votes', 'Pending Votes', 'Percentage']));

        $totalVerified = array_sum(array_column($tally, 'votes'));

        foreach ($tally as $result) {
            $percentage = $totalVerified > 0
                ? round(($result['votes'] / $totalVerified) * 100, 1) . '%'
                : '0%';

            $writer->addRow(Row::fromValues([
                $result['nomor_urut'],
                $result['nama_kandidat'],
                $result['votes'],
                $result['pending'],
                $percentage,
            ]));
        }

        $writer->addRow(Row::fromValues(['']));
        $writer->addRow(Row::fromValues(['Total Verified Votes', '', $totalVerified]));
        $writer->addRow(Row::fromValues(['Total Pending Votes', '', array_sum(array_column($tally, 'pending'))]));

        $writer->close();

        return response()->download($tempFile, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend(true);
    }

    /**
     * Export election results as PDF.
     */
    public function electionResultsPdf(Pemilu $pemilu)
    {
        $pemilu->load('kandidats');
        $tally = $this->computeTally($pemilu);
        $totalVerified = array_sum(array_column($tally, 'votes'));
        $totalPending = array_sum(array_column($tally, 'pending'));
        $voterCount = Pemilih::count();
        $totalVotes = Suara::where('pemilu_id', $pemilu->id)->count();
        $turnout = $voterCount > 0 ? round(($totalVotes / $voterCount) * 100, 1) : 0;

        $pdf = Pdf::loadView('exports.election-results', [
            'pemilu' => $pemilu,
            'tally' => $tally,
            'totalVerified' => $totalVerified,
            'totalPending' => $totalPending,
            'voterCount' => $voterCount,
            'totalVotes' => $totalVotes,
            'turnout' => $turnout,
            'exportDate' => now()->format('d M Y H:i'),
        ]);

        $pdf->setPaper('a4', 'portrait');

        return $pdf->download('results-' . str_replace(' ', '-', $pemilu->name) . '-' . now()->format('Y-m-d') . '.pdf');
    }

    // ─── AUDIT EXPORTS ───────────────────────────────────────────────

    /**
     * Export audit log as Excel.
     */
    public function auditExcel(Pemilu $pemilu)
    {
        $suaras = Suara::where('pemilu_id', $pemilu->id)
            ->with(['zkpProof', 'nullifier'])
            ->orderByDesc('created_at')
            ->get();

        $filename = 'audit-' . str_replace(' ', '-', $pemilu->name) . '-' . now()->format('Y-m-d') . '.xlsx';
        $tempFile = tempnam(sys_get_temp_dir(), 'export_') . '.xlsx';

        $writer = new Writer();
        $writer->openToFile($tempFile);

        $writer->addRow(Row::fromValues(["Audit Log: {$pemilu->name}"]));
        $writer->addRow(Row::fromValues(['Exported: ' . now()->format('d M Y H:i')]));
        $writer->addRow(Row::fromValues(['']));

        $writer->addRow(Row::fromValues([
            'Vote ID',
            'Vote Hash',
            'Nullifier Hash',
            'Status',
            'Proof Status',
            'Verified At',
            'Submitted At',
        ]));

        foreach ($suaras as $suara) {
            $writer->addRow(Row::fromValues([
                $suara->id,
                $suara->vote_hash,
                $suara->nullifier?->nullifier_hash ?? '-',
                $suara->status,
                $suara->zkpProof?->status_valid ?? 'BELUM_DIVERIFIKASI',
                $suara->zkpProof?->verified_at?->format('Y-m-d H:i:s') ?? '-',
                $suara->created_at?->format('Y-m-d H:i:s') ?? '-',
            ]));
        }

        $writer->addRow(Row::fromValues(['']));
        $writer->addRow(Row::fromValues(['Total Votes', $suaras->count()]));
        $writer->addRow(Row::fromValues(['Verified', $suaras->where('status', 'TERVERIFIKASI')->count()]));
        $writer->addRow(Row::fromValues(['Pending', $suaras->where('status', 'MASUK')->count()]));
        $writer->addRow(Row::fromValues(['Rejected', $suaras->where('status', 'DITOLAK')->count()]));

        $writer->close();

        return response()->download($tempFile, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend(true);
    }

    /**
     * Export audit log as PDF.
     */
    public function auditPdf(Pemilu $pemilu)
    {
        $suaras = Suara::where('pemilu_id', $pemilu->id)
            ->with(['zkpProof', 'nullifier'])
            ->orderByDesc('created_at')
            ->get();

        $stats = [
            'total' => $suaras->count(),
            'verified' => $suaras->where('status', 'TERVERIFIKASI')->count(),
            'pending' => $suaras->where('status', 'MASUK')->count(),
            'rejected' => $suaras->where('status', 'DITOLAK')->count(),
        ];

        $pdf = Pdf::loadView('exports.audit', [
            'pemilu' => $pemilu,
            'suaras' => $suaras,
            'stats' => $stats,
            'exportDate' => now()->format('d M Y H:i'),
        ]);

        $pdf->setPaper('a4', 'landscape');

        return $pdf->download('audit-' . str_replace(' ', '-', $pemilu->name) . '-' . now()->format('Y-m-d') . '.pdf');
    }

    // ─── HELPERS ─────────────────────────────────────────────────────

    private function computeTally(Pemilu $pemilu): array
    {
        $verifiedVotes = Suara::where('pemilu_id', $pemilu->id)
            ->where('status', 'TERVERIFIKASI')
            ->with('zkpProof')
            ->get();

        $tally = [];
        foreach ($verifiedVotes as $suara) {
            $signals = json_decode($suara->zkpProof?->public_signals, true);
            if ($signals && isset($signals[3])) {
                $kandidatId = $signals[3];
                $tally[$kandidatId] = ($tally[$kandidatId] ?? 0) + 1;
            }
        }

        $pendingVotes = Suara::where('pemilu_id', $pemilu->id)
            ->where('status', 'MASUK')
            ->with('zkpProof')
            ->get();

        $pendingTally = [];
        foreach ($pendingVotes as $suara) {
            $signals = json_decode($suara->zkpProof?->public_signals, true);
            if ($signals && isset($signals[3])) {
                $kandidatId = $signals[3];
                $pendingTally[$kandidatId] = ($pendingTally[$kandidatId] ?? 0) + 1;
            }
        }

        $results = [];
        foreach ($pemilu->kandidats as $kandidat) {
            $results[] = [
                'id' => $kandidat->id,
                'nomor_urut' => $kandidat->nomor_urut,
                'nama_kandidat' => $kandidat->nama_kandidat,
                'votes' => $tally[(string) $kandidat->id] ?? 0,
                'pending' => $pendingTally[(string) $kandidat->id] ?? 0,
            ];
        }

        usort($results, fn($a, $b) => $b['votes'] - $a['votes']);

        return $results;
    }
}
