<!DOCTYPE html>
<html>

<head>
    <title>Print Laporan Detail Kas</title>
    <link href="{{ asset('') }}assets/template/src/bootstrap/css/bootstrap.min.css" rel="stylesheet" type="text/css" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

    <style>
        .img-detail {
            width: 141px;
            height: 176px;
            object-fit: cover;
            border-radius: 5px;
        }

        table td,
        tfoot th,
        .table-title th {
            border: 1px solid black;
            border-collapse: collapse;
            padding: 3px;
        }

        @media screen {

            body {
                font-family: 'Courier New', Courier, monospace;
            }

            table td {
                font-size: 13px;
            }

            .logo-atas {
                width: 40px;
                height: 40px;
                position: absolute;
                left: 250px;
                top: -15px;
            }

            table.print-friendly tr td,
            table.print-friendly tr th {
                page-break-inside: avoid;
            }

        }

        /* print styles */
        @media print {

            body {
                margin: 0;
                color: #000;
                background-color: #fff;
            }

            table td {
                font-size: 13px;
            }

            .logo-atas {
                width: 40px;
                height: 40px;
                position: absolute;
                left: 200px;
                top: 15px;
            }

            table.print-friendly tr td,
            table.print-friendly tr th {
                page-break-inside: avoid;
            }

        }
    </style>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"
        integrity="sha512-GsLlZN/3F2ErC5ifS5QtgpiJtWd43JWSuIgh7mbzZ8zBps+dvLusV+eNQATqgA/HdeKFVgA5v3S/cIrLF7QnIg=="
        crossorigin="anonymous" referrerpolicy="no-referrer"></script>
</head>
{{-- <body onload="window.print();"> --}}

<body id="print-area">

    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <table class="table">
                        <thead>
                            <tr>
                                <th colspan="8">
                                    <center>
                                        <h4>DIAN ISTANA<br>Laporan Detail Kas Masuk</h4><br>Tanggal :
                                        {{ date('d F Y', strtotime($awal)) }} s.d {{ date('d F Y', strtotime($akhir)) }}
                                    </center>
                                </th>
                            </tr>
                            <tr class="table-title">
                                <th>No</th>
                                <th>Invoice/Time</th>
                                <th>Penyelia</th>
                                <th>User</th>
                                <th>Description</th>
                                <th>Amount</th>
                                <th>Last Payment</th>

                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $no = 0;
                                $total = 0;
                            @endphp
                            @foreach ($data as $key)
                                @php
                                    $no++;
                                    $total = $total + $key->amount;
                                    $users = \App\Models\User::where('id', $key->user_id);
                                    if ($users->count() > 0) {
                                        $user = $users->first();
                                        $user_name = $user->name;
                                        $penyelia = $user->penyelia;
                                        $info = $user->blok . '-' . $user->nomor_rumah;
                                        if ($user->last_payment_date == null) {
                                            $last_payment = '';
                                        } else {
                                            $last_payment =
                                                date('d-m-Y', strtotime($user->last_payment_date)) .
                                                '<br> ( ' .
                                                $user->last_payment_period .
                                                ' )';
                                        }
                                    } else {
                                        $user_name = 'no-data';
                                        $penyelia = '';
                                        $info = '';
                                        $last_payment = '';
                                    }
                                @endphp
                                <tr>
                                    <td>{{ $no }}</td>
                                    <td>{{ $key->invoice }}<br>{{ date('H:i:s', strtotime($key->paid_at)) }}</td>

                                    <td>{{ $penyelia }}</td>
                                    <td>{{ $user_name }} | {{ $info }}</td>
                                    <td>{{ $key->payment_name }}</td>
                                    <td style="text-align: right;white-space:nowrap;">IDR
                                        {{ number_format($key->amount) }}</td>
                                    <td style="white-space:nowrap;"><?= $last_payment ;?></td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <th colspan="2"></th>
                                <th colspan="3">TOTAL NILAI TRANSAKSI</th>
                                <th style="text-align: right;white-space:nowrap;">IDR {{ number_format($total) }}</th>
                                <th></th>
                            </tr>
                        </tfoot>
                    </table>


                </div>
            </div>
        </div>
    </div>
    <script>
        const element = document.querySelector('#print-area');
        const options = {
            filename: 'laporan detail kas.pdf',
            margin: 0.2,
            image: {
                type: 'jpeg',
                quality: 1
            },
            html2canvas: {
                scale: 2
            },
            jsPDF: {
                unit: 'in',
                format: 'letter',
                orientation: 'portrait'
            },
        };
        html2pdf().set(options).from(element).save();
    </script>
</body>

</html>
