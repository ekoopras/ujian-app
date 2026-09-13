<!DOCTYPE html>
<html>

<head>
    <title>Daftar Email Siswa</title>
    <style>
        body {
            font-family: sans-serif;
            font-size: 12px;
            color: #333;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        th,
        td {
            border: 1px solid #999;
            padding: 8px;
            text-align: left;
        }

        th {
            background-color: #1A365D;
            color: white;
        }
    </style>
</head>

<body>

    <div class="header">
        <h2>AKUN UJIAN SMPN 1 TASIKMADU</h2>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 5%;">No Absen</th>
                <th>Nama Siswa</th>
                <th>Kelas</th>
                <th>Email</th>
                <th>password</th>
            </tr>
        </thead>
        <tbody>
            @foreach($records as $index => $user)
            <tr>
                <td>{{ str_pad($user->nomor_absen, 2, '0', STR_PAD_LEFT) }}</td>
                <td>{{ $user->name }}</td>
                <td>{{ $user->kelase?->name ?? '-' }}</td>
                <td>{{ $user->email }}</td>
                <td>spensatajaya</td>
            </tr>
            @endforeach
        </tbody>
    </table>

</body>

</html>