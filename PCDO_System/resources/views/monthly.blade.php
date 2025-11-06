<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Monthly Report - {{ $date }}</title>
    <style>
        @page { margin: 40px 40px; }
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            color: #222;
        }

        h1, h2, h3 {
            margin: 0;
            padding: 0;
        }

        h1 {
            text-align: center;
            font-size: 20px;
            margin-bottom: 10px;
        }

        h2 {
            margin-top: 25px;
            font-size: 16px;
            border-bottom: 1px solid #aaa;
            padding-bottom: 5px;
        }

        h3 {
            margin-top: 15px;
            font-size: 14px;
            color: #444;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
            margin-bottom: 20px;
            page-break-inside: auto;
        }

        th, td {
            border: 1px solid #ccc;
            padding: 6px 8px;
            text-align: left;
            vertical-align: top;
        }

        th {
            background: #f2f2f2;
            font-weight: bold;
        }

        .text-center { text-align: center; }
        .text-right { text-align: right; }

        .partial { color: orange; }
        .unpaid { color: red; }
        .paid { color: #2b6; font-weight: bold; }
        .resolved { color: #555; }

        .page-break {
            page-break-after: always;
        }

        small {
            color: #555;
        }

        .section {
            margin-bottom: 30px;
        }
    </style>
</head>
<body>
    <h1>Monthly Report</h1>
    <p style="text-align:center;"><strong>{{ $date }}</strong></p>

    {{-- 🟦 1️⃣ Registered Cooperatives --}}
    <div class="section">
        <h2>Registered Cooperatives ({{ $registeredCoops->count() }})</h2>
        @if($registeredCoops->count())
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Cooperative Name</th>
                    <th>Registered Date</th>
                </tr>
            </thead>
            <tbody>
                @foreach($registeredCoops as $index => $coop)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $coop->name }}</td>
                    <td>{{ \Carbon\Carbon::parse($coop->created_at)->format('M d, Y') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @else
            <p><i>No cooperatives registered this month.</i></p>
        @endif
    </div>

    {{-- 🟩 2️⃣ Programs --}}
    @foreach($programs as $program)
        <div class="section">
            <h2>Program: {{ $program['program_name'] }}</h2>

            {{-- 🔹 A. Cooperatives with Amortization --}}
            <h3>A. With Amortization Schedule ({{ count($program['has_amortization']) }})</h3>
            @if(count($program['has_amortization']))
                @foreach($program['has_amortization'] as $coop)
                    <h4>{{ $coop['cooperative_name'] }} 
                        <small>({{ $coop['program_status'] }})</small>
                    </h4>
                    <p>Loan Amount: ₱{{ $coop['loan_amount'] }} | Grace Period: {{ $coop['with_grace'] }} months</p>
                    <table>
                        <thead>
                            <tr>
                                <th>Term</th>
                                <th>Due Date</th>
                                <th>Date Paid</th>
                                <th>Status</th>
                                <th class="text-right">Installment</th>
                                <th class="text-right">Amount Paid</th>
                                <th class="text-right">Penalty</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($coop['payments'] as $payment)
                                <tr>
                                    <td>{{ $payment['term'] }}</td>
                                    <td>{{ $payment['due_date'] }}</td>
                                    <td>{{ $payment['date_paid'] ?? '—' }}</td>
                                    <td class="
                                        @if($payment['status'] === 'Paid') paid
                                        @elseif($payment['status'] === 'Partial Paid') partial
                                        @elseif($payment['status'] === 'Resolved') resolved
                                        @else unpaid @endif
                                    ">
                                        {{ $payment['status'] }}
                                    </td>
                                    <td class="text-right">₱{{ $payment['installment'] }}</td>
                                    <td class="text-right">{{ $payment['amount_paid'] ? '₱'.$payment['amount_paid'] : '—' }}</td>
                                    <td class="text-right">{{ $payment['penalty'] ? '₱'.$payment['penalty'] : '—' }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="8"><i>No payments this month.</i></td></tr>
                            @endforelse
                        </tbody>
                    </table>
                    <p>
                        <strong>Summary:</strong>
                        Paid: {{ $coop['stats']['paid_this_month'] }},
                        Partial: {{ $coop['stats']['partial_this_month'] }},
                    </p>
                @endforeach
            @else
                <p><i>No cooperatives with amortization schedules.</i></p>
            @endif

            {{-- 🔹 B. Checklist-only cooperatives --}}
            <h3>B. Checklist-only ({{ count($program['checklist_only']) }})</h3>
            @if(count($program['checklist_only']))
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Cooperative Name</th>
                            <th>Status</th>
                            <th>Has Checklist?</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($program['checklist_only'] as $index => $coop)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $coop['cooperative_name'] }}</td>
                                <td>{{ $coop['program_status'] }}</td>
                                <td>{{ $coop['has_checklist'] ? 'Yes' : 'No' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <p><i>No checklist-only cooperatives.</i></p>
            @endif
        </div>

        {{-- 📄 Add page break after each program --}}
        @if(!$loop->last)
            <div class="page-break"></div>
        @endif
    @endforeach

</body>
</html>
