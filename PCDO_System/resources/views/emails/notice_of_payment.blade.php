<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Notice of Payment</title>
    <style>
        @page {
        margin: 40px 50px;
        }

        body {
            font-family: 'DejaVu Sans', Arial, Helvetica, sans-serif;
            font-size: 14px;
            line-height: 1.6;
            color: #000;
            margin: 0;
            padding: 0;
        }
        .title {
            font-weight: bold;
            font-size: 18px;
            text-align: center;
            margin-top: 10px;
        }
        .info-table {
            width: 100%;
            border-collapse: collapse;
        }
        .info-table td {
            padding: 4px 8px;
        }
        .right {
            text-align: right;
        }
        .footer {
            margin-top: 40px;
            text-align: right;
        }
    </style>
</head>

<body>
<table width="100%" cellpadding="0" cellspacing="0" 
       style="background:#fff; border-spacing:0; padding:0; margin:0;">
    <tr>
        <td align="center">
            <table width="510" cellpadding="0" cellspacing="0" 
                   style="border-spacing:0; background:#fff;">
                <tr>
                    <td>
                        <table width="100%" cellspacing="0" cellpadding="0"
                            style="border-collapse:collapse; border-spacing:0;">
                            <tr>
                                <td width="90" align="center" style="padding:0; vertical-align:middle;">
                                    <img src="{{ isset($isPdf) && $isPdf ? public_path('img/province_of_palawan_logo.png') : 
                                    $message->embed(public_path('img/province_of_palawan_logo.png')) }}" 
                                         width="70" style="display:block;">
                                </td>

                                <td align="center" style="padding:0 10px; vertical-align:middle;">
                                    <strong>Republic of the Philippines</strong><br>
                                    Provincial Government of Palawan<br>
                                    PROVINCIAL COOPERATIVE DEVELOPMENT OFFICE<br>
                                    Capitol Bldg., Puerto Princesa City<br>
                                    pcdo.palawan@gmail.com<br>
                                    (048) 434-4173
                                </td>

                                <td width="90" align="center" style="padding:0; vertical-align:middle;">
                                    <img src="{{ isset($isPdf) && $isPdf ? public_path('img/pcdo_logo.png') : 
                                    $message->embed(public_path('img/pcdo_logo.png')) }}" 
                                         width="70" style="display:block;">
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <tr>
                    <td>
                        <div class="title">NOTICE OF PAYMENT</div>
                        <div style="text-align: right; margin-top: 5px;">
                            {{ $datetoday }}
                        </div>
                    </td>
                </tr>
                <tr>
                    <td style="padding: 20px 40px;">

                        <p>
                            <strong>The BOD Chairperson</strong><br>
                            {{ $coopProgram->cooperative->name ?? 'N/A' }}<br>
                            {{ $coopProgram->cooperative->details->city->name ?? '' }},
                            {{ $coopProgram->cooperative->details->province->name ?? '' }}
                        </p>

                        <p>Sir/Madam:</p>

                        <table class="info-table">
                            <tr>
                                <td><strong>{{ $coopProgram->program->name }} LOAN:</strong></td>
                                <td class="right"><strong>₱{{ number_format($coopProgram->loan_amount ?? 'Unknown') }}</strong></td>
                            </tr>
                            <tr>
                                <td><strong>Date Released:</strong></td>
                                <td class="right">{{ $datereleased }}</td>
                            </tr>
                            <tr>
                                <td><strong>Maturity Date:</strong></td>
                                <td class="right">{{ $maturitydate }}</td>
                            </tr>
                            <tr>
                                <td><strong>Monthly Amortization:</strong></td>
                                <td class="right">₱{{ number_format($monthlyDue, 2) }}</td>
                            </tr>
                            <tr>
                                <td><strong>Total Amount Paid:</strong></td>
                                <td class="right">₱{{ number_format($totalpaid, 2) }}</td>
                            </tr>
                            <tr>
                                <td><strong>Date of Last Payment:</strong></td>
                                <td class="right">{{ $lastpaymentDate }}</td>
                            </tr>
                            <tr>
                                <td><strong>Amount Paid:</strong></td>
                                <td class="right">₱{{ number_format($lastamountPaid, 2) }}</td>
                            </tr>
                            <tr>
                                <td><strong>Balance as of {{ $datetoday }}:</strong></td>
                                <td class="right">₱{{ number_format($balanceofToday, 2) }}</td>
                            </tr>
                        </table>

                        <p>For your Information.</p>

                        <div class="footer">
                            <p>Very truly yours,<br><br><br>
                            <strong>GINA S. SOCRATES</strong><br>
                            Prov'l. Cooperatives Dev't. Officer
                            </p>
                        </div>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</body>
</html>
