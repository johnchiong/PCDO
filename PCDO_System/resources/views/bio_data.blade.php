<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Bio-Data</title>
    <style>
        @page {
            size: 8.5in 13in; /* Legal / long bond */
            margin: 10mm;
        }

        body {
            font-family: "Arial", sans-serif;
            font-size: 11px;
            margin: 0;
            padding: 15px;
        }

        h2 {
            text-align: center;
            text-transform: uppercase;
            background-color: #000;
            color: #fff;
            padding: 6px;
            font-size: 15px;
            margin-bottom: 8px;
        }

        .section-title {
            background-color: #e0e0e0;
            font-weight: bold;
            padding: 4px 6px;
            margin-top: 10px;
            border: 1px solid #000;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 11px;
            margin-bottom: 5px;
        }

        td {
            border: 1px solid #000;
            padding: 4px 6px;
            vertical-align: top;
        }

        .no-border td {
            border: none;
        }

        .label {
            font-weight: bold;
            width: 25%;
        }
    </style>
</head>

<body>

    <h2>Bio-Data</h2>

    {{-- PERSONAL DATA --}}
    <div class="section-title">PERSONAL DATA</div>
    <table>
        <tr>
            <td colspan="4"><strong>Position Desired:</strong> {{ $position_desired ?? '' }}</td>
        </tr>
        <tr>
            <td><strong>Given Name:</strong> {{ $given_name ?? '' }}</td>
            <td><strong>Surname:</strong> {{ $surname ?? '' }}</td>
            <td><strong>M.I.:</strong> {{ $middle_initial ?? '' }}</td>
            <td><strong>Date:</strong> {{ $date ?? '' }}</td>
        </tr>
        <tr>
            <td colspan="2"><strong>Present Address:</strong> {{ $present_address ?? '' }}</td>
            <td colspan="2"><strong>Tel. No.:</strong> {{ $present_tel ?? '' }}</td>
        </tr>
        <tr>
            <td colspan="2"><strong>Permanent Address:</strong> {{ $permanent_address ?? '' }}</td>
            <td colspan="2"><strong>Tel. No.:</strong> {{ $permanent_tel ?? '' }}</td>
        </tr>
        <tr>
            <td><strong>Citizenship:</strong> {{ $citizenship ?? '' }}</td>
            <td><strong>Date of Birth:</strong> {{ $birth_date ?? '' }}</td>
            <td><strong>Place of Birth:</strong> {{ $birth_place ?? '' }}</td>
            <td><strong>Religion:</strong> {{ $religion ?? '' }}</td>
        </tr>
        <tr>
            <td><strong>Age:</strong> {{ $age ?? '' }}</td>
            <td><strong>Sex:</strong> {{ $sex ?? '' }}</td>
            <td><strong>Civil Status:</strong> {{ $civil_status ?? '' }}</td>
            <td><strong>Height / Weight:</strong> {{ $height ?? '' }} {{ $weight ? '/ '.$weight : '' }}</td>
        </tr>
        <tr>
            <td colspan="2"><strong>Name of Spouse:</strong> {{ $spouse ?? '' }}</td>
            <td><strong>Occupation:</strong> {{ $spouse_occupation ?? '' }}</td>
            <td><strong>Children / Ages:</strong> {{ $children ?? '' }}</td>
        </tr>
        <tr>
            <td><strong>Father:</strong> {{ $father ?? '' }}</td>
            <td><strong>Occupation:</strong> {{ $father_occupation ?? '' }}</td>
            <td colspan="2"><strong>Address:</strong> {{ $father_address ?? '' }}</td>
        </tr>
        <tr>
            <td><strong>Mother:</strong> {{ $mother ?? '' }}</td>
            <td><strong>Occupation:</strong> {{ $mother_occupation ?? '' }}</td>
            <td colspan="2"><strong>Address:</strong> {{ $mother_address ?? '' }}</td>
        </tr>
        <tr>
            <td colspan="2"><strong>Languages / Dialects:</strong> {{ $languages ?? '' }}</td>
            <td colspan="2"><strong>In case of emergency, notify:</strong> {{ $emergency_person ?? '' }}</td>
        </tr>
        <tr>
            <td colspan="2"><strong>Address:</strong> {{ $emergency_address ?? '' }}</td>
            <td colspan="2"><strong>Tel. No.:</strong> {{ $emergency_tel ?? '' }}</td>
        </tr>
    </table>

    {{-- EDUCATION --}}
    <div class="section-title">EDUCATION</div>
    <table>
        <tr>
            <td><strong>Level</strong></td>
            <td><strong>School & Address</strong></td>
            <td><strong>Degree / Course</strong></td>
            <td><strong>Year Graduated</strong></td>
        </tr>
        <tr>
            <td>Elementary</td>
            <td>{{ $school_elem ?? '' }}</td>
            <td>{{ $degree_elem ?? '' }}</td>
            <td>{{ $grad_elem ?? '' }}</td>
        </tr>
        <tr>
            <td>High School</td>
            <td>{{ $school_hs ?? '' }}</td>
            <td>{{ $degree_hs ?? '' }}</td>
            <td>{{ $grad_hs ?? '' }}</td>
        </tr>
        <tr>
            <td>Vocational</td>
            <td>{{ $school_voc ?? '' }}</td>
            <td>{{ $degree_voc ?? '' }}</td>
            <td>{{ $grad_voc ?? '' }}</td>
        </tr>
        <tr>
            <td>College</td>
            <td>{{ $school_college ?? '' }}</td>
            <td>{{ $degree_college ?? '' }}</td>
            <td>{{ $grad_college ?? '' }}</td>
        </tr>
        <tr>
            <td>Others / Skills</td>
            <td colspan="3">{{ $skills ?? '' }}</td>
        </tr>
    </table>

    {{-- EMPLOYMENT HISTORY --}}
    <div class="section-title">EMPLOYMENT HISTORY</div>
    <table>
        <tr>
            <td width="5%">#</td>
            <td width="40%">Company / Address</td>
            <td width="25%">Position</td>
            <td width="20%">Period</td>
            <td width="10%">Earnings</td>
        </tr>
        @for($i = 1; $i <= 5; $i++)
        <tr>
            <td>{{ $i }}</td>
            <td>{{ ${"job_company_$i"} ?? '' }}</td>
            <td>{{ ${"job_occupation_$i"} ?? '' }}</td>
            <td>{{ ${"job_period_$i"} ?? '' }}</td>
            <td>{{ ${"job_earnings_$i"} ?? '' }}</td>
        </tr>
        @endfor
    </table>

    {{-- CHARACTER REFERENCES --}}
    <div class="section-title">CHARACTER REFERENCES</div>
    <table>
        <tr>
            <td><strong>Name</strong></td>
            <td><strong>Occupation</strong></td>
            <td><strong>Address</strong></td>
            <td><strong>Tel. No.</strong></td>
        </tr>
        @for($i = 1; $i <= 3; $i++)
        <tr>
            <td>{{ ${"ref_name_$i"} ?? '' }}</td>
            <td>{{ ${"ref_occupation_$i"} ?? '' }}</td>
            <td>{{ ${"ref_address_$i"} ?? '' }}</td>
            <td>{{ ${"ref_tel_$i"} ?? '' }}</td>
        </tr>
        @endfor
    </table>

</body>
</html>
