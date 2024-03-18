<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Tabungan</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }

        .container {
            max-width: 800px;
            margin: 20px auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        h1 {
            text-align: center;
            margin-bottom: 20px;
        }

        .user-info {
            margin-bottom: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }

        th {
            background-color: #f4f4f4;
        }

        .debit {
            color: green;
        }

        .kredit {
            color: red;
        }

        .total-row td {
            font-weight: bold;
        }

        .signature {
            text-align: center;
            margin-top: 20px;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }
    </style>
</head>

<body>
    <div class="container">
        <h1><u>CETAK TABUNGAN</u></h1>
        <div class="user-info">
            <table>
                <tr>
                    <td>Program:</td>
                    <td>{{ $datas['program'] }}</td>
                </tr>
                <tr>
                    <td>Donatur:</td>
                    <td>{{ $datas['donatur'] }}</td>
                </tr>
                <tr>
                    <td>Phone:</td>
                    <td>{{ $datas['phone'] }}</td>
                </tr>
                <tr>
                    <td>Alamat:</td>
                    <td>{{ $datas['alamat'] }}</td>
                </tr>
            </table>
        </div>

        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>Tanggal</th>
                    <th>No. Transakasi</th>
                    <th>Nominal</th>
                    <th>Keterangan</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($datas['tabungan'] as $item)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $item['tanggal_kuitansi'] }}</td>
                        <td>{{ $item['kode_transakasi'] }}</td>
                        <td>{{ $item['nominal'] }}</td>
                        <td>{{ $item['desc'] }}</td>
                        <td>{{ $item['status'] }}</td>
                    </tr>
                @endforeach
                <tr class="total-row">
                    <td colspan="5" style="text-align: right;">Total:</td>
                    <td>{{ $datas['total_tabungan'] }}</td>
                </tr>
            </tbody>
        </table>
        <div class="signature">
            <p>Signature:</p>
            <img src="https://dummyimage.com/150x50/000/fff" alt="Signature Placeholder">
        </div>
    </div>
</body>

</html>
