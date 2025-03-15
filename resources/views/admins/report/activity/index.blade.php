@extends('layouts.master_admins')

@section('title_admin', 'Laporan Aktivitas User')

@section('breadcrumb_admin')
    <li class="breadcrumb-item" aria-current="page">Laporan</li>
    <li class="breadcrumb-item active" aria-current="page">Laporan Aktivitas User</li>
@endsection

@section('content_admin')

    <div class="col-xl-12 col-lg-12 col-sm-12 layout-spacing">
        <div class="card">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Tanggal Awal:</label>
                            <input type="date" id="awal" class="form-control" value="{{ date('Y-m-01') }}">
                        </div>

                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Tanggal Akhir:</label>
                            <input type="date" id="akhir" class="form-control" value="{{ date('Y-m-t') }}">
                        </div>

                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Admin Name:</label>
                            <select class="form-control" id="user_id">
                                <option value="">- All Admin - </option>
                                @foreach ($admins as $admin)
                                    <option value="{{ $admin->id }}">{{ $admin->username }} - ( {{ $admin->name }} )
                                    </option>
                                @endforeach
                            </select>

                        </div>

                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Activity Type:</label>
                            <select class="form-control" id="activity">
                                <option value="">- All Activity - </option>
                                <option value="add">ADD DATA</option>
                                <option value="update">UPDATE DATA</option>
                                <option value="delete">DELETE DATA</option>
                            </select>

                        </div>

                    </div>

                    <div class="col-md-8">
                        <div class="form-group">
                            <button id="btn-filter" class="btn btn-success btn-report"><i class="fa fa-filter"></i>
                                Filter</button>


                        </div>

                    </div>

                </div>
            </div>
        </div>

        <div class="card mt20">

            <div class="card-body" style="margin-top: -20px;">
                <div class="widget-content widget-content-area br-8 mt-10">
                    <div class="table-responsive" id="divTables">
                        <table id="report-table" class="table table-striped table-bordered table-hover table-custom"
                            style="width:100%">
                            <thead>
                                <tr>
                                    <th width="5%">No</th>
                                    <th width="10%">Date</th>

                                    <th width="*">Description</th>
                                    <th width="15%">Admin Name</th>
                                    <th width="10%">Activity Type</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>

                </div>

            </div>
        </div>
    </div>

@endsection

@section('script_admin')
    <script src="{{ asset('assets/plugins/tinymce/tinymce.min.js') }}"></script>

    {{ assets_js_back('showChangeImage') }}
@endsection
