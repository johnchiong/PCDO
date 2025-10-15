<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Amortization Schedule</title>
    <style>
        @page {
            size: 8.5in 13in;
            /* long bond paper (legal size) */
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

        th,
        td {
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

        .total-row td {
            font-weight: bold;
            text-align: right;
            background-color: #eaeaea;
        }

        .conforme {
            text-align: center;
            margin-left: 400px;
        }

        .signature-section {
            margin-top: 60px;
            width: 100%;
        }

        .signature-table {
            width: 100%;
            border-collapse: collapse;
            text-align: center;
        }

        .signature-block {
            width: 33.33%;
            vertical-align: bottom;
            padding-top: 40px;
        }

        .sig-line {
            border-bottom: 1px solid #000;
            width: 80%;
            margin: 0 auto 5px auto;
            height: 30px;
        }

        .signature-block p {
            margin-top: 5px;
            font-weight: bold;
        }
    </style>
</head>

<body>
    <h2>Amortization Schedule</h2>

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

    {{-- Amortization Table --}}
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Due Date</th>
                <th>Installment</th>
                <th>Date Paid</th>
                <th>Amount Paid</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @php
                $totalInstallment = 0;
            @endphp

            @foreach($schedules as $i => $s)
                @php
                    $totalInstallment += $s->installment ?? 0;
                @endphp
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ \Carbon\Carbon::parse($s->due_date)->format('Y-m-d') }}</td>
                    <td>{{ number_format($s->installment, 2) }}</td>
                    <td>{{ $s->date_paid ? \Carbon\Carbon::parse($s->date_paid)->format('Y-m-d') : '' }}</td>
                    <td>{{ $s->amount_paid ? number_format($s->amount_paid, 2) : '' }}</td>
                    <td>{{ $s->status }}</td>
                </tr>
            @endforeach

            {{-- ✅ Total Row --}}
            <tr class="total-row">
                <td colspan="2">Total</td>
                <td>{{ number_format($totalInstallment, 2) }}</td>
                <td colspan="3"></td>
            </tr>
        </tbody>
    </table>

    {{-- Signature Section --}}
    <div class="signature-section">
        <table class="signature-table">
            <tr>
                <td class="signature-block">
                    <div class="sig-line"></div>
                    <p>Prepared by:</p>
                </td>
                <td class="signature-block">
                    <div class="sig-line"></div>
                    <p>Noted by:</p>
                </td>
                <td class="signature-block">
                    <div class="sig-line"></div>
                    <p>Conforme:</p>
                </td>
            </tr>
        </table>
    </div>
</body>

</html>