<!DOCTYPE html>
<html>

<head>
    <title>Bulk Update User Data</title>
    <link rel="icon" type="image/x-icon" href="{{ asset('assets/template/main/img/dianlogo.png') }}">
    <link href="{{ asset('') }}assets/template/src/bootstrap/css/bootstrap.min.css" rel="stylesheet" type="text/css" />
    <style>
        body {
            margin-left: 15em;
            margin-right: 15em;
            margin-top: 5em;
            margin-bottom: 5em;
            color: #fff;
            background-color: #000;
        }
    </style>
</head>

<body onload="window.print();">

    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="listTable" class="table table-striped table-bordered table-hover table-custom"
                            style="width:100%">
                            <thead>
                                <tr>
                                    <th style="background-color:whitesmoke;font-weight:bold;">No</th>
                                    <th style="background-color:whitesmoke;font-weight:bold;">user_id</th>
                                    <th style="background-color:whitesmoke;font-weight:bold;">name</th>
                                    <th style="background-color:whitesmoke;font-weight:bold;">blok</th>
                                    <th style="background-color:whitesmoke;font-weight:bold;">nomor_rumah</th>
                                    <th style="background-color:whitesmoke;font-weight:bold;">birthday</th>
                                    <th style="background-color:whitesmoke;font-weight:bold;">username</th>
                                    <th style="background-color:whitesmoke;font-weight:bold;">email</th>
                                    <th style="background-color:whitesmoke;font-weight:bold;">password</th>
                                    <th style="background-color:whitesmoke;font-weight:bold;">jenis_kelamin</th>
                                    <th style="background-color:whitesmoke;font-weight:bold;">no_hp</th>
                                    <th style="background-color:whitesmoke;font-weight:bold;">level</th>
                                    <th style="background-color:whitesmoke;font-weight:bold;">is_active</th>
                                    <th style="background-color:whitesmoke;font-weight:bold;">penyelia</th>
                                    <th style="background-color:whitesmoke;font-weight:bold;">daya_listrik</th>
                                    <th style="background-color:whitesmoke;font-weight:bold;">luas_tanah</th>
                                    <th style="background-color:whitesmoke;font-weight:bold;">iuran_bulanan</th>
                                    <th style="background-color:whitesmoke;font-weight:bold;">whatsapp_emergency</th>
                                    <th style="background-color:whitesmoke;font-weight:bold;">keterangan</th>
                                    <th style="background-color:whitesmoke;font-weight:bold;">alamat_surat_menyurat</th>
                                    <th style="background-color:whitesmoke;font-weight:bold;">nomor_telepon_rumah</th>
                                    <th style="background-color:whitesmoke;font-weight:bold;">id_pelanggan_pdam</th>
                                    <th style="background-color:whitesmoke;font-weight:bold;">nomor_meter_pln</th>
                                    <th style="background-color:whitesmoke;font-weight:bold;">mulai_menempati</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($data as $index => $d)
                                    @php
                                        if($d->birthday != null) {
                                            $t = date('d', strtotime($d->birthday));
                                            $b = date('m', strtotime($d->birthday));
                                            $tahun = date('Y', strtotime($d->birthday));
                                            $tanggal = (int)$t;
                                            $bulan = (int)$b;
                                            $tanggal_lahir = $bulan.'/'.$tanggal.'/'.$tahun;
                                        }


                                        if($d->mulai_menempati != null) {
                                            $t = (int)date('d', strtotime($d->mulai_menempati));
                                            $b = (int)date('m', strtotime($d->mulai_menempati));
                                            $tahun = date('Y', strtotime($d->mulai_menempati));
                                            $tanggal= (int)$t;
                                            $bulan = (int)$b;

                                            $tanggal_menempati = $bulan.'/'.$tanggal.'/'.$tahun;
                                        }


                                    @endphp


                                    <tr>
                                        <td style="text-align:left;background-color:green;">{{ $index + 1 }}</td>
                                        <td style="text-align:left;background-color:green;">{{ $d->id }}</td>
                                        <td style="text-align:left;background-color:green;">{{ (string)$d->name }}</td>
                                        <td style="text-align:left;background-color:green;">{{ (string)$d->blok }}</td>
                                        <td style="text-align:left;background-color:green;">{{ (string)$d->nomor_rumah }}</td>
                                        <td style="text-align:left;">{{ $d->birthday == null ? '' : $tanggal_lahir }}</td>
                                        <td style="text-align:left;">{{ (string)$d->username }}</td>
                                        <td style="text-align:left;">{{ (string)$d->email }}</td>
                                        <td style="text-align:left;"></td>
                                        <td style="text-align:left;">{{ $d->jenis_kelamin == 'Laki-laki' ? 1 : 2 }}</td>
                                        <td style="text-align:left;">{{ (string)$d->no_hp }}</td>
                                        <td style="text-align:left;">{{ (string)$d->level }}</td>
                                        <td style="text-align:left;">{{ (string)$d->is_active }}</td>
                                        <td style="text-align:left;">{{ (string)$d->penyelia }}</td>
                                        <td style="text-align:left;">{{ (string)$d->daya_listrik }}</td>
                                        <td style="text-align:left;">{{ (string)$d->luas_tanah }}</td>
                                        <td style="text-align:left;">{{ (string)$d->iuran_bulanan }}</td>
                                        <td style="text-align:left;">{{ (string)$d->whatsapp_emergency }}</td>
                                        <td style="text-align:left;">{{ (string)$d->keterangan }}</td>
                                        <td style="text-align:left;">{{ (string)$d->alamat_surat_menyurat }}</td>
                                        <td style="text-align:left;">{{ (string)$d->nomor_telepon_rumah }}</td>
                                        <td style="text-align:left;">{{ (string)$d->id_pelanggan_pdam }}</td>
                                        <td style="text-align:left;">{{ (string)$d->nomor_meter_pln }}</td>
                                        <td style="text-align:left;">{{ $d->mulai_menempati == null ? '' : $tanggal_menempati }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

</body>

</html>
