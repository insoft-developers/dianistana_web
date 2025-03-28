<!DOCTYPE html>
<html>
<head>
	<title>Print Laporan Unit Bisnis</title>
    <link rel="icon" type="image/x-icon" href="{{ asset('assets/template/main/img/dianlogo.png') }}">
    <link href="{{ asset('') }}assets/template/src/bootstrap/css/bootstrap.min.css" rel="stylesheet" type="text/css" />
    <style>
         .img-detail{
            width: 141px;
            height: 176px;
            object-fit: cover;
            border-radius: 5px;
        }



        @media screen {
            .btn-pdf{
                position: absolute;
                color: white;
                top: 19px;
                background: red;
                font-weight: bold;
                margin-left: 86px;
            }
            .btn-print{
                position: absolute;
                color: white;
                top: 19px;
                background: orange;
                font-weight: bold;
            }
            .btn-excel{
                position: absolute;
                color: white;
                top: 19px;
                background: green;
                font-weight: bold;
                margin-left: 152px;
            }
            body {
                margin-left: 15em;
                margin-right: 15em;
                margin-top: 5em;
                margin-bottom: 5em;
                color: #fff;
                background-color: rgb(216, 216, 216);
                font-family: 'Courier New', Courier, monospace;
            }
            table td{
                font-size: 13px;
            }

            .logo-atas{
                width: 40px;
                height: 40px;
                position: absolute;
                left: 311px;
                top: 15px;
            }

        }

            /* print styles */
            @media print {

                body {
                    margin: 0;
                    color: #000;
                    background-color: #fff;
                }
                table td{
                    font-size: 13px;
                }
                
                .logo-atas{
                    width: 40px;
                    height: 40px;
                    position: absolute;
                    left: 240px;
                    top: 15px;
                }
                table.print-friendly tr td, table.print-friendly tr th {
                    page-break-inside: avoid;
                }

            }
    </style>
</head>
{{-- <body onload="window.print();"> --}}
<body>
    
    <div class="row">
    <div class="col-md-12">
    <div class="card">
    <div class="card-body">
    <table class="table table-striped">
        @php
            $pays = Request::segment(5);
            if($pays == 'BC') {
                $pay = 'BCA';
            } 
            else if($pays == 'BR') {
                $pay = 'BRI';
            }
            else if($pays == 'BT') {
                $pay = 'BTN';
            }
            else if($pays == 'DA') {
                $pay = 'DANA';
            }
            else {
                $pay = 'OTHERS';
            }
        
        @endphp
        <thead>
            <tr>
            <th colspan="8"><center><img class="logo-atas" src="{{ asset('assets/template/main/img/dianlogo.png') }}"><h4>DIAN ISTANA<br>Laporan Kas Masuk Unit Bisnis</h4><br>Tanggal : {{ date('d F Y', strtotime($awal)) }} s.d {{ date('d F Y', strtotime($akhir)) }}<br> Paid By : {{ Request::segment(5) == 0 ? "ALL METHOD ": $pay }}<br>Unit: {{ Request::segment(6) == 0 ? "ALL UNIT " : $units }}
            </center></th>
            </tr>
            <tr>
                <th>No</th>
                <th>Invoice/Time</th>
                <th>Kepemilikan</th>
                <th>User</th>
                <th>Description</th>
                <th>Amount</th>
                <th>Date</th>

            </tr>
        </thead>
        <tbody>
            @php
            $no= 0;
            $total = 0;
            @endphp
            @foreach($data as $key)
            @php
            $no++;
            $total = $total + $key->total_price;
            $users = \App\Models\User::where('id', $key->user_id);
            if($users->count() > 0) {
                $user = $users->first();
                $user_name = $user->name;
                $penyelia = $user->penyelia;
                $info = $user->blok.'-'.$user->nomor_rumah;
                
            } else {
                $user_name = 'no-data';
                $penyelia = '';
                $info = "";
            }
            @endphp
            <tr>
            <td>{{ $no }}</td>
            <td>{{ $key->invoice }}<br>{{ $key->paid_at == NULL ? '' : date('H:i:s', strtotime($key->updated_at)) }}</td>
            
            <td>{{ $key->kepemilikan }}</td>
            <td>{{ $user_name }} | {{ $info }}</td>
            <td>{{ $key->name_unit }}<br>{{ date('d-m-Y', strtotime($key->booking_date))  }}<br>{{ $key->start_time }} - {{ $key->finish_time }}</td>
            <td style="text-align: right;white-space:nowrap;">IDR {{ number_format($key->total_price) }}</td>
            <td style="white-space:nowrap;">{{ date('d-m-Y', strtotime($key->paid_at)) }}</td>
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

    <button id="btn-print" class="btn btn-print">Print</button>
    <button id="btn-pdf" class="btn btn-pdf">PDF</button>
    <button id="btn-excel" class="btn btn-excel">Excel</button>

    <script src="https://code.jquery.com/jquery-3.7.1.js" integrity="sha256-eKhayi8LEQwp4NKxN+CfCh+3qOVUtJn3QNZ0TciWLP4=" crossorigin="anonymous"></script>
    <script>
        

        $("#btn-print").click(function(){
            window.print();
        });

        $("#btn-pdf").click(function(){
            var awal = "{{ $awal }}";
            var akhir = "{{ $akhir }}";
            var payment = "{{ $payment }}";
            var unit = "{{ $unit }}";
            window.open("{{ url('backdata/print_unit_report_pdf') }}"+"/"+awal+"/"+akhir+"/"+payment+"/"+unit , "_blank");
        })

        $("#btn-excel").click(function(){
            var awal = "{{ $awal }}";
            var akhir = "{{ $akhir }}";
            var payment = "{{ $payment }}";
            var unit = "{{ $unit }}";
            window.location = "{{ url('backdata/print_unit_report_excel') }}"+"/"+awal+"/"+akhir+"/"+payment+"/"+unit;
        })
    </script>

</body>
</html>