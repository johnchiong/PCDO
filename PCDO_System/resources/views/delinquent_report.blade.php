<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Delinquency Report</title>
    <style>
        @page {
            size: 8.5in 13in;
            margin: 10mm;
        }

        body {
            font-family: "Arial", sans-serif;
            font-size: 11px;
            margin: 0;
            padding: 20px;
        }

        h2 {
            text-align: center;
            margin-bottom: 15px;
            text-transform: uppercase;
            font-size: 15px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }

        th, td {
            border: 1px solid #000;
            padding: 5px 6px;
            text-align: left;
            vertical-align: middle;
        }

        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }

        .info-table td {
            border: none;
            padding: 4px 8px;
        }

        .info-table td.label {
            font-weight: bold;
            width: 25%;
        }
         
    </style>
</head>
<body>
    <h2>Delinquency Report</h2>

    {{-- Cooperative Information --}}
    <table class="info-table">
        <tr>
            <td class="label">Cooperative Name:</td>
            <td>{{ $coop->name ?? 'N/A' }}</td>
            <td class="label">Address:</td>
            <td>{{ $address ?? 'N/A' }}</td>
        </tr>
        <tr>
            <td class="label">Chairman:</td>
            <td>{{ $chairman ?? 'N/A' }}</td>
            <td class="label">Treasurer:</td>
            <td>{{ $treasurer ?? 'N/A' }}</td>
        </tr>
        <tr>
            <td class="label">Manager:</td>
            <td>{{ $manager ?? 'N/A' }}</td>
            <td class="label">Contact No.:</td>
            <td>{{ $contact ?? 'N/A' }}</td>
        </tr>
        <tr>
            <td class="label">Program Name:</td>
            <td>{{ $coopProgram->program->name ?? 'N/A' }}</td>
            <td class="label">Project:</td>
            <td>{{ $coopProgram->project ?? 'N/A' }}</td>
        </tr>
        <tr>
            <td class="label">Loan Amount:</td>
            <td>{{ number_format($coopProgram->loan_amount, 2) }}</td>
            <td class="label">Term (Months):</td>
            <td>{{ $coopProgram->program->term_months ?? 'N/A' }}</td>
        </tr>
        <tr>
            <td class="label">Start Date:</td>
            <td>{{ \Carbon\Carbon::parse($coopProgram->start_date)->format('Y-m-d') }}</td>
            <td class="label">Grace Period:</td>
            <td>{{ $coopProgram->with_grace }} month(s)</td>
        </tr>
    </table>

    {{-- Delinquency Table --}}
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Due Date</th>
                <th>Date Paid</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($delinquents as $i => $d)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ \Carbon\Carbon::parse($d->due_date)->format('Y-m-d') }}</td>
                    <td>{{ $d->date_paid ? \Carbon\Carbon::parse($d->date_paid)->format('Y-m-d') : 'N/A' }}</td>
                    <td>{{ ucfirst($d->status) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" style="text-align: center;">No delinquent records found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
