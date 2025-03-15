<!DOCTYPE html>
<html>
<head>
	<title>Laporan Unit Bisnis | Dian Istana</title>
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

            body {
                margin-left: 5em;
                margin-right: 5em;
                margin-top: 5em;
                margin-bottom: 5em;
                color: #fff;
                background-color: grey;
            }

        }

            /* print styles */
        @media print {

            body {
                margin: 0;
                color: grey;
                background-color: #fff;
            }

        }
    </style>
</head>
<!--<body onload="window.print();">-->
<body>
    <div class="row">
    <div class="col-md-12">
    <div class="card">
    <div class="card-header">
        @php
            $units = \App\Models\UnitBisnis::where('id', $unit);
            if($units->count() > 0) {
                $unit = $units->first();
                $nama_unit = $unit->name_unit;
            } else {
                $nama_unit = 'no-found';
            }
        @endphp
        <h4>Laporan Unit Bisnis | Dian Istana</h4>
        <p>Periode : {{ date('d F Y', strtotime($awal) ) }} - {{ date('d F Y', strtotime($akhir)) }}<br>Filter berdasarkan tanggal {{ $pilih }}<br>{{  $unit == '0' ? 'All Unit' : $nama_unit }} - {{ ($payment == '0' ? 'All type': $payment == '1') ? 'Free' : 'Charge' }} - {{ $payment_status }}</p>
    </div>
    <div class="card-body">
    <table class="table table-bordered table-striped">
    <thead>
        <tr>
            <th>No</th>
            <th>Date</th>
            <th>Payment Status</th>
            <th>User</th>
            <th>Facility</th>
            <th>Invoice</th>
            <th>Book Detail</th>
            <th>Price</th>
            <th>Package</th>
            <th>Paid At</th>
        </tr>
    </thead>
    <tbody> 
        @foreach($data as $index =>  $d)
            @php
                $users = \App\Models\User::where('id', $d->user_id);
                if($users->count() > 0) {
                    $user = $users->first();
                    $pengguna = $user->name;
                    $level = $user->level;
                    $blok = $user->blok;
                    $norumah = $user->nomor_rumah;
                } else {
                
                    $pengguna = "not found";
                    $level = "";
                    $blok = "";
                    $norumah = "";
                }
                
                $units = \App\Models\UnitBisnis::where('id', $d->business_unit_id);
                if($units->count() > 0) {
                    $unit = $units->first();
                    $fasilitas = $unit->name_unit;
                } else {
                    $fasilitas = 'not-found';
                }
            
            @endphp
        
            <tr>
            <td>{{ $index + 1 }}</td>
            <td>{{ date('d-m-Y', strtotime($d->created_at)) }} <br> {{ date('H:i:s', strtotime($d->created_at))}}</td>
            <td>
                @if($d->payment_status == 'PENDING')
                    <span class="badge text-warning">PENDING</span>
                @elseif($d->payment_status == 'PAID') 
                    <span class="badge text-success"><i class="fa fa-check"></i> PAID</span>
                @elseif($d->payment_status == 'CANCELLED') 
                    <span class="badge text-danger"><i class="fa fa-trash"></i> CANCELLED</span>
                @endif
            </td>
            <td>{{ $pengguna }}<br>( {{ $level }} ) {{ $blok }} - {{ $norumah }}</td>
            <td>{{ $fasilitas }}</td>
            <td>{{ $d->invoice }}</td>
            <td>{{ date('d-m-Y', strtotime($d->booking_date)) }}<br>{{ $d->start_time }} - {{ $d->finish_time }}</td>
            <td>{{ $d->total_price == 0 ? 'Free' : number_format($d->total_price) }}</td>
            <td>{{ $d->package_name }}</td>
            <td>{{ date('d-m-Y', strtotime($d->paid_at)) }}</td>
        
        @endforeach
    </tbody>
    </table>
    </div>
    </div>
    </div>
    </div>

</body>
</html>