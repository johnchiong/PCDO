<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Cooperative Details</title>
    <style>
        @page {
            size: 8.5in 13in;
            margin: 10mm;
        }

        body {
            font-family: "Arial", sans-serif;
            font-size: 15px;
            margin: 20px;
            padding: 20px;
        }

        h2 {
            text-align: center;
            margin-bottom: 45px;
            text-transform: uppercase;
            font-size: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }

        th,
        td {
            border: 1px solid #000;
            padding: 11px 12px;
            text-align: left;
            vertical-align: middle;
        }

        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }

        .info-table td {
            border: none;
            padding: 10x 14px;
        }

        .info-table td.label {
            font-weight: bold;
            width: 25%;
        }

        .signature-section {
            margin-top: 30px;
        }

        .signature-row {
            display: flex;
            justify-content: space-between;
            font-weight: bold;
        }

        .signature {
            width: 30%;
            text-align: center;
        }

        .conforme {
            text-align: center;
            margin-left: 400px;
        }

        .Prepared {
            text-align: left;
            margin-left: 10px;
        }

        .Noted {
            text-align: center;
            margin-left: 400px;
        }
    </style>
</head>

<body>
    <h2>Cooperative Details</h2>

    <table class="info-table">
        <tr>
            <td class="label">Cooperative Name:</td>
            <td>{{ $coopProgram->cooperative->name ?? 'N/A' }}</td>
            <td class="label">Program Name:</td>
            <td>{{ $coopProgram->program->name ?? 'N/A' }}</td>
        </tr>
        <tr>
            <td class="label">Start Date:</td>
            <td>{{ optional($coopProgram->start_date)->format('F d, Y') ?? 'N/A' }}</td>
            <td class="label">Completed At:</td>
            <td>{{ optional($coopProgram->updated_at)->format('F d, Y') ?? 'N/A' }}</td>
        </tr>
        <tr>
            <td class="label">Region:</td>
            <td>{{ optional($coopDetail->region)->name ?? 'N/A' }}</td>
            <td class="label">Province:</td>
            <td>{{ optional($coopDetail->province)->name ?? 'N/A' }}</td>
        </tr>
        <tr>
            <td class="label">City:</td>
            <td>{{ optional($coopDetail->city)->name ?? 'N/A' }}</td>
            <td class="label">Barangay:</td>
            <td>{{ optional($coopDetail->barangay)->name ?? 'N/A' }}</td>
        </tr>
        <tr>
            <td class="label">Asset Size:</td>
            <td>{{ $coopDetail->asset_size ?? 'N/A' }}</td>
            <td class="label">Cooperative Type:</td>
            <td>{{ $coopDetail->coop_type ?? 'N/A' }}</td>
        </tr>
        <tr>
            <td class="label">Status Category:</td>
            <td>{{ $coopDetail->status_category ?? 'N/A' }}</td>
            <td class="label">Bond of Membership:</td>
            <td>{{ $coopDetail->bond_of_membership ?? 'N/A' }}</td>
        </tr>
        <tr>
            <td class="label">Area of Operation:</td>
            <td>{{ $coopDetail->area_of_operation ?? 'N/A' }}</td>
            <td class="label">Citizenship:</td>
            <td>{{ $coopDetail->citizenship ?? 'N/A' }}</td>
        </tr>
        <tr>
            <td class="label">Members Count:</td>
            <td>{{ $coopDetail->members_count ?? 0 }}</td>
            <td class="label">Total Assets:</td>
            <td>{{ number_format($coopDetail->total_asset ?? 0, 2) }}</td>
        </tr>
        <tr>
            <td class="label">Net Surplus:</td>
            <td>{{ number_format($coopDetail->net_surplus ?? 0, 2) }}</td>
            <td></td>
            <td></td>
        </tr>
    </table>
</body>

</html>