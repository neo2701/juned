<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Voter List</title>
    <style>
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 11px;
            color: #333;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #064e3b;
            padding-bottom: 15px;
        }

        .header h1 {
            font-size: 20px;
            color: #064e3b;
            margin: 0;
        }

        .header p {
            color: #666;
            margin: 5px 0 0;
            font-size: 10px;
        }

        .meta {
            margin-bottom: 15px;
            font-size: 10px;
            color: #666;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        th {
            background-color: #064e3b;
            color: white;
            padding: 8px 10px;
            text-align: left;
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        td {
            padding: 7px 10px;
            border-bottom: 1px solid #e5e7eb;
            font-size: 10px;
        }

        tr:nth-child(even) {
            background-color: #f9fafb;
        }

        .status-registered {
            color: #059669;
            font-weight: bold;
        }

        .status-approved {
            color: #d97706;
            font-weight: bold;
        }

        .footer {
            margin-top: 20px;
            text-align: center;
            font-size: 9px;
            color: #999;
            border-top: 1px solid #e5e7eb;
            padding-top: 10px;
        }

        .summary {
            margin-top: 15px;
            padding: 10px;
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
            border-radius: 4px;
        }

        .summary p {
            margin: 3px 0;
            font-size: 10px;
        }
    </style>
</head>

<body>
    <div class="header">
        <h1>JUNED E-Voting — Voter List</h1>
        <p>Exported: {{ $exportDate }}</p>
    </div>

    <div class="summary">
        <p><strong>Total Voters:</strong> {{ $voters->count() }}</p>
        <p><strong>Registered:</strong> {{ $voters->where('registration_status', 'REGISTERED')->count() }}</p>
        <p><strong>Awaiting Registration:</strong> {{ $voters->where('registration_status', 'APPROVED')->count() }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>NIK</th>
                <th>Name</th>
                <th>Status</th>
                <th>Registered At</th>
            </tr>
        </thead>
        <tbody>
            @foreach($voters as $voter)
                <tr>
                    <td>{{ $voter->id }}</td>
                    <td style="font-family: monospace;">{{ $voter->nik }}</td>
                    <td>{{ $voter->nama_pemilih ?? '-' }}</td>
                    <td
                        class="{{ $voter->registration_status === 'REGISTERED' ? 'status-registered' : 'status-approved' }}">
                        {{ $voter->registration_status === 'REGISTERED' ? 'Registered' : 'Awaiting' }}
                    </td>
                    <td>{{ $voter->registered_at?->format('d M Y') ?? '-' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <p>JUNED E-Voting System — This document is auto-generated and confidential.</p>
    </div>
</body>

</html>