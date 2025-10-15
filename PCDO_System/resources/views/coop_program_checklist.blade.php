<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Checklist of Documents</title>
    <style>
        @page {
            size: 8.5in 13in;
            margin: 15mm;
        }

        body {
            font-family: "Arial", sans-serif;
            font-size: 12px;
            margin: 0;
            padding: 15px;
            line-height: 1.4;
        }

        h2 {
            text-align: center;
            text-transform: uppercase;
            margin-bottom: 15px;
            font-size: 14px;
            letter-spacing: 0.5px;
        }

        ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        li {
            display: flex;
            align-items: center;
            margin-bottom: 6px;
        }

        input[type=checkbox] {
            transform: scale(1.1);
            margin-right: 10px;
            flex-shrink: 0;
        }

        .task-text {
            flex: 1;
        }

        .attachment {
            margin-left: 25px;
            margin-top: 3px;
        }

        .section {
            margin-bottom: 10px;
        }

        .remarks {
            margin-top: 20px;
            border-top: 1px solid #000;
            padding-top: 10px;
        }

        .signature-section {
            margin-top: 20px;
            width: 100%;
        }

        .signature-section table {
            width: 100%;
            border-collapse: collapse;
        }

        .signature-section td {
            width: 50%;
            text-align: center;
            vertical-align: bottom;
            padding-top: 15px;
        }

        .sig-line {
            border-top: 1px solid #000;
            width: 80%;
            margin: 0 auto 5px auto;
        }

        .page-break {
            page-break-before: always;
            margin-top: 50px;
        }

        img,
        embed {
            display: block;
            margin: 0 auto;
            max-width: 100%;
            height: auto;
        }
    </style>
</head>

<body>
    <h2>CHECKLIST OF DOCUMENTS</h2>

    <div class="section">
        <p><strong>Cooperative:</strong> {{ $coopProgram->cooperative->name ?? 'N/A' }}</p>
        <p><strong>Program:</strong> {{ $coopProgram->program->name ?? 'N/A' }}</p>
    </div>

    <ul>
        @foreach ($checklists as $item)
            <li>
                <input type="checkbox" {{ strtolower($item->status) === 'complete' ? 'checked' : '' }}>
                <span class="task-text">{{ $item->task_name }}</span>
            </li>
        @endforeach
    </ul>

    <div class="remarks">
        <p><strong>Remarks:</strong></p>
        <br>
        <hr style="border: none; border-top: 1px solid #000;">
    </div>

    <div class="signature-section">
        <table>
            <tr>
                <td>
                    <div class="sig-line"></div>
                    <p>Checked by:</p>
                </td>
                <td>
                    <div class="sig-line"></div>
                    <p>Received by:</p>
                </td>
            </tr>
        </table>
    </div>
</body>

</html>