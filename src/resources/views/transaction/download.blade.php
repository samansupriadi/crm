<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f3f4f6;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }

        .invoice {
            background-color: #ffffff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .flex {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .text-gray-600 {
            color: #718096;
        }

        .font-bold {
            font-weight: bold;
        }

        .mt-4 {
            margin-top: 16px;
        }

        .mt-8 {
            margin-top: 32px;
        }

        .border-t {
            border-top: 1px solid #e2e8f0;
        }

        .py-2 {
            padding-top: 8px;
            padding-bottom: 8px;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 16px;
        }

        .table th,
        .table td {
            padding: 8px;
            border-bottom: 1px solid #cbd5e0;
        }

        .float-right {
            float: right;
        }

        .signature {
            width: 200px;
            height: 50px;
            background-color: #f3f4f6;
            border: 1px solid #d1d5db;
            border-radius: 4px;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .signature img {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
        }

        .company-header {
            text-align: center;
            margin-bottom: 20px;
        }
    </style>
</head>

<body>


    <div class="container">
        <div class="invoice">
            <div class="company-header">
                <h1 class="text-2xl font-bold">Your Company Name</h1>
            </div>
            <div class="flex justify-between items-center">
                <h2 class="text-2xl font-bold">Invoice</h2>
                <span class="text-gray-600">TRX-{{ $datas['kode_transaksi'] }}</span>
            </div>
            <div class="mt-4">
                <p class="text-sm text-gray-600">Issued to:</p>
                <p class="font-bold">{{ $datas['donor_name'] }}</p>
                <p>{{ $datas['telephone'] }}</p>
                <p>NPWP : {{ $datas['npwp'] }}</p>
                <p>{{ $datas['alamat'] }}</p>
                <p> {{ $datas['kota'] }}</p>
                <p>{{ $datas['pos'] }}</p>
            </div>
            <div class="mt-8">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Nama Program</th>
                            <th>Nominal Donasi</th>
                            <th>Keterangan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($datas['program'] as $item)
                            <tr>
                                <td>{{ $item['program']['program_name'] }}</td>
                                <td>{{ 'Rp ' . number_format($item['nominal'], 2, ',', '.') }}</td>
                                <td>{{ $item['description'] }}</td>
                            </tr>
                        @endforeach

                    </tbody>
                </table>
            </div>
            <div class="mt-4 border-t border-gray-300 pt-4">
                <p class="text-sm text-gray-600">Total</p>
                <p class="font-bold"> {{ $datas['total'] }} </p>
            </div>
            <div class="mt-8 flex justify-between">
                <div>
                    <p class="text-sm text-gray-600 mb-4">{{ $datas['tanggal_transaksi'] }}</p>
                </div>
                <div class="signature">
                    <img src="https://dummyimage.com/150x50/000/fff" alt="Signature Placeholder">
                </div>
                <div>
                    <p class="mt-4">{{ $datas['petugas'] }}</p>
                </div>
            </div>
        </div>
    </div>
</body>

</html>
