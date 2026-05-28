<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Election Results — {{ $pemilu->name }}</title>
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

        .header h2 {
            font-size: 14px;
            color: #333;
            margin: 5px 0 0;
            font-weight: normal;
        }

        .header p {
            color: #666;
            margin: 5px 0 0;
            font-size: 10px;
        }

        .stats {
            display: table;
            width: 100%;
            margin-bottom: 20px;
        }

        .stat-box {
            display: table-cell;
            width: 25%;
            text-align: center;
            padding: 10px;
            border: 1px solid #e5e7eb;
        }

        .stat-box .value {
            font-size: 18px;
            font-weight: bold;
            color: #064e3b;
        }

        .stat-box .label {
            font-size: 9px;
            color: #666;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        th {
            background-color: #064e3b;
            color: white;
            padding: 8px 12px;
            text-align: left;
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        td {
            padding: 10px 12px;
            border-bottom: 1px solid #e5e7eb;
        }

        tr:nth-child(even) {
            background-color: #f9fafb;
        }

        .winner {
            background-color: #f0fdf4 !important;
        }

        .winner td {
            font-weight: bold;
        }

        .bar-container {
            width: 100%;
            height: 12px;
            background: #e5e7eb;
            border-radius: 6px;
            overflow: hidden;
        }

        .bar {
            height: 100%;
            background: #064e3b;
            border-radius: 6px;
        }

        .footer {
            margin-top: 25px;
            text-align: center;
            font-size: 9px;
            color: #999;
            border-top: 1px solid #e5e7eb;
            padding-top: 10px;
        }

        .badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 10px;
            font-size: 9px;
            font-weight: bold;
        }

        .badge-active {
            background: #d1fae5;
            color: #065f46;
        }

        .badge-draft {
            background: #f3f4f6;
            color: #4b5563;
        }

        .badge-done {
            background: #dbeafe;
            color: #1e40af;
        }
    </style>
</head>

<body>
    <div class="header">
        <h1>JUNED E-Voting — Election Results</h1>
        <h2>{{ $pemilu->name }}</h2>
        <p>
            Status:
            <span
                class="badge {{ $pemilu->status === 'BERJALAN' ? 'badge-active' : ($pemilu->status === 'DRAFT' ? 'badge-draft' : 'badge-done') }}">
                {{ $pemilu->status }}
            </span>
            &nbsp;|&nbsp; Exported: {{ $exportDate }}
        </p>
    </div>

    <div class="stats">
        <div class="stat-box">
            <div class="value">{{ $voterCount }}</div>
            <div class="label">Registered Voters</div>
        </div>
        <div class="stat-box">
            <div class="value">{{ $totalVotes }}</div>
            <div class="label">Total Votes</div>
        </div>
        <div class="stat-box">
            <div class="value">{{ $totalVerified }}</div>
            <div class="label">Verified Votes</div>
        </div>
        <div class="stat-box">
            <div class="value">{{ $turnout }}%</div>
            <div class="label">Turnout</div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 60px;">No.</th>
                <th>Candidate</th>
                <th style="width: 100px;">Verified</th>
                <th style="width: 100px;">Pending</th>
                <th style="width: 80px;">Percentage</th>
                <th style="width: 150px;">Chart</th>
            </tr>
        </thead>
        <tbody>
            @foreach($tally as $index => $result)
                <tr class="{{ $index === 0 && $result['votes'] > 0 ? 'winner' : '' }}">
                    <td>{{ $result['nomor_urut'] }}</td>
                    <td>
                        {{ $result['nama_kandidat'] }}
                        @if($index === 0 && $result['votes'] > 0)
                            <span style="color: #059669; font-size: 9px;">★ LEADING</span>
                        @endif
                    </td>
                    <td>{{ $result['votes'] }}</td>
                    <td>{{ $result['pending'] }}</td>
                    <td>{{ $totalVerified > 0 ? round(($result['votes'] / $totalVerified) * 100, 1) : 0 }}%</td>
                    <td>
                        <div class="bar-container">
                            <div class="bar"
                                style="width: {{ $totalVerified > 0 ? round(($result['votes'] / $totalVerified) * 100) : 0 }}%;">
                            </div>
                        </div>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <p>JUNED E-Voting System — Official Election Results Document</p>
        <p>This document is auto-generated. Verify results at the public audit portal.</p>
    </div>
</body>

</html>