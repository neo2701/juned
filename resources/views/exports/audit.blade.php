<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Audit Log — {{ $pemilu->name }}</title>
    <style>
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 10px;
            color: #333;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #064e3b;
            padding-bottom: 15px;
        }

        .header h1 {
            font-size: 18px;
            color: #064e3b;
            margin: 0;
        }

        .header h2 {
            font-size: 12px;
            color: #333;
            margin: 5px 0 0;
            font-weight: normal;
        }

        .header p {
            color: #666;
            margin: 5px 0 0;
            font-size: 9px;
        }

        .summary {
            margin-bottom: 15px;
            padding: 10px;
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 4px;
        }

        .summary-grid {
            display: table;
            width: 100%;
        }

        .summary-item {
            display: table-cell;
            text-align: center;
            padding: 5px;
        }

        .summary-item .value {
            font-size: 16px;
            font-weight: bold;
            color: #064e3b;
        }

        .summary-item .label {
            font-size: 8px;
            color: #666;
            text-transform: uppercase;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        th {
            background-color: #064e3b;
            color: white;
            padding: 6px 8px;
            text-align: left;
            font-size: 8px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        td {
            padding: 5px 8px;
            border-bottom: 1px solid #e5e7eb;
            font-size: 9px;
        }

        tr:nth-child(even) {
            background-color: #f9fafb;
        }

        .status-verified {
            color: #059669;
            font-weight: bold;
        }

        .status-pending {
            color: #d97706;
            font-weight: bold;
        }

        .status-rejected {
            color: #dc2626;
            font-weight: bold;
        }

        .hash {
            font-family: monospace;
            font-size: 8px;
            word-break: break-all;
        }

        .footer {
            margin-top: 20px;
            text-align: center;
            font-size: 8px;
            color: #999;
            border-top: 1px solid #e5e7eb;
            padding-top: 10px;
        }
    </style>
</head>

<body>
    <div class="header">
        <h1>JUNED E-Voting — Audit Log</h1>
        <h2>{{ $pemilu->name }}</h2>
        <p>Exported: {{ $exportDate }}</p>
    </div>

    <div class="summary">
        <div class="summary-grid">
            <div class="summary-item">
                <div class="value">{{ $stats['total'] }}</div>
                <div class="label">Total Votes</div>
            </div>
            <div class="summary-item">
                <div class="value" style="color: #059669;">{{ $stats['verified'] }}</div>
                <div class="label">Verified</div>
            </div>
            <div class="summary-item">
                <div class="value" style="color: #d97706;">{{ $stats['pending'] }}</div>
                <div class="label">Pending</div>
            </div>
            <div class="summary-item">
                <div class="value" style="color: #dc2626;">{{ $stats['rejected'] }}</div>
                <div class="label">Rejected</div>
            </div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Vote Hash</th>
                <th>Nullifier</th>
                <th>Status</th>
                <th>Proof</th>
                <th>Verified At</th>
                <th>Submitted</th>
            </tr>
        </thead>
        <tbody>
            @foreach($suaras as $suara)
                <tr>
                    <td>{{ $suara->id }}</td>
                    <td class="hash">{{ Str::limit($suara->vote_hash, 20) }}</td>
                    <td class="hash">{{ Str::limit($suara->nullifier?->nullifier_hash ?? '-', 20) }}</td>
                    <td
                        class="{{ $suara->status === 'TERVERIFIKASI' ? 'status-verified' : ($suara->status === 'MASUK' ? 'status-pending' : 'status-rejected') }}">
                        {{ $suara->status }}
                    </td>
                    <td>{{ $suara->zkpProof?->status_valid ?? 'N/A' }}</td>
                    <td>{{ $suara->zkpProof?->verified_at?->format('d/m/Y H:i') ?? '-' }}</td>
                    <td>{{ $suara->created_at?->format('d/m/Y H:i') ?? '-' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <p>JUNED E-Voting System — Cryptographic Audit Trail</p>
        <p>Each vote is verified using zero-knowledge proofs (Groth16) and recorded in a Merkle tree.</p>
    </div>
</body>

</html>