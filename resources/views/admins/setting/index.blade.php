@extends('layouts.master_admins')

@section('title_admin', 'App Setting')

@section('breadcrumb_admin')
    <li class="breadcrumb-item" aria-current="page">Setting</li>
    <li class="breadcrumb-item active" aria-current="page">App Setting</li>
@endsection

@section('content_admin')

    <div class="col-xl-12 col-lg-12 col-sm-12 layout-spacing">
        <div class="card">
            <div class="card-body">
                @if ($message = Session::get('error'))
                    <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert"> <button type="button"
                            class="btn-close" data-bs-dismiss="alert" aria-label="Close"><svg
                                xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round" class="feather feather-x close" data-bs-dismiss="alert">
                                <line x1="18" y1="6" x2="6" y2="18"></line>
                                <line x1="6" y1="6" x2="18" y2="18"></line>
                            </svg></button> <strong>Error!</strong> <?= $message ?></div>
                @endif
                @if ($message = Session::get('success'))
                    <div class="alert alert-success alert-dismissible fade show mb-4" role="alert"> <button type="button"
                            class="btn-close" data-bs-dismiss="alert" aria-label="Close"><svg
                                xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round" class="feather feather-x close" data-bs-dismiss="alert">
                                <line x1="18" y1="6" x2="6" y2="18"></line>
                                <line x1="6" y1="6" x2="18" y2="18"></line>
                            </svg></button> <strong>Success!</strong> <?= $message ?></div>
                @endif

                <form id="form-setting" method="POST" action="{{ route('backdata.setting.update') }}">
                    @csrf
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-body">
                                    <div class="form-group">
                                        <label>App Name:</label>
                                        <input type="text" class="form-control" id="app_name" name="app_name"
                                            placeholder="enter app name" value="{{ $setting->app_name }}">
                                    </div>
                                    <div class="form-group mt20">
                                        <label>App Title:</label>
                                        <input type="text" class="form-control" id="app_title" name="app_title"
                                            placeholder="enter app title" value="{{ $setting->app_title }}">
                                    </div>
                                    <div class="form-group mt20">
                                        <label>Address Title:</label>
                                        <input type="text" class="form-control" id="address_title" name="address_title"
                                            placeholder="enter address title" value="{{ $setting->address_title }}">
                                    </div>
                                    <div class="form-group mt20">
                                        <label>Phone:</label>
                                        <input type="text" class="form-control" id="phone" name="phone"
                                            placeholder="enter phone number" value="{{ $setting->phone }}">
                                    </div>
                                    <div class="form-group mt20">
                                        <label>Address:</label>
                                        <textarea class="form-control" id="address" name="address" placeholder="enter address">{{ $setting->address_title }}</textarea>
                                    </div>
                                    <div class="form-group mt20">
                                        <label>Handphone Number:</label>
                                        <input type="text" class="form-control" id="hp" name="hp"
                                            placeholder="enter cell phone number" value="{{ $setting->hp }}">
                                    </div>
                                    <div class="form-group mt20">
                                        <label>Email:</label>
                                        <input type="email" class="form-control" id="email" name="email"
                                            placeholder="enter email" value="{{ $setting->email }}">
                                    </div>
                                    <div class="form-group mt20">
                                        <label>Api Key WA Gateway:</label>
                                        <input type="text" class="form-control" id="api_wa" name="api_wa"
                                            placeholder="enter api whatsapp gateway" value="{{ $setting->api_wa }}">
                                    </div>
                                    <div class="form-group mt20">
                                        <label>Api Key Payment Gateway:</label>
                                        <input type="text" class="form-control" id="api_payment" name="api_payment"
                                            placeholder="enter api payment gateway" value="{{ $setting->api_payment }}">
                                    </div>

                                    <div class="form-group mt20">
                                        <label>Merchant Code:</label>
                                        <input type="text" class="form-control" id="merchant_code"
                                            name="merchant_code" placeholder="enter duitku merchant code"
                                            value="{{ $setting->merchant_code }}">
                                    </div>
                                    <div class="form-group mt20">
                                        <label>Duitku Url:</label>
                                        <input type="text" class="form-control" id="duitku_link" name="duitku_link"
                                            placeholder="enter url duitku" value="{{ $setting->duitku_link }}">
                                    </div>
                                    <div class="form-group mt20">
                                        <label>CallBack Url:</label>
                                        <input type="text" class="form-control" id="callback_payment"
                                            name="callback_payment" placeholder="enter callback url"
                                            value="{{ $setting->callback_payment }}">
                                    </div>

                                    <div class="form-group mt20" style="display: none;">
                                        <label>Tax(%):</label>
                                        <input type="text" class="form-control" id="pajak" name="pajak"
                                            placeholder="enter tax percent" value="{{ $setting->pajak }}">
                                    </div>
                                    <div class="form-group mt20" style="display: none;">
                                        <label>Admin Fee (Rp.):</label>
                                        <input type="text" class="form-control" id="admin_fee" name="admin_fee"
                                            placeholder="enter api payment gateway" value="{{ $setting->admin_fee }}">
                                    </div>
                                    <div class="form-group mt20">
                                        <label>Booking Pending Expired In (minute):</label>
                                        <input type="text" class="form-control" id="booking_expired"
                                            name="booking_expired" placeholder="enter api payment gateway"
                                            value="{{ $setting->booking_expired }}">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-body">
                                    <div class="form-group mt20">
                                        <label>Term & Conditions:</label>
                                        <textarea class="form-control" id="term" name="term" placeholder="enter term and conditions">{{ $setting->term }}</textarea>
                                    </div>
                                    <div class="form-group mt20">
                                        <label>Privacy Policy:</label>
                                        <textarea class="form-control" id="privacy" name="privacy" placeholder="enter privacy policy">{{ $setting->privacy }}</textarea>
                                    </div>
                                    <div class="form-group mt20">
                                        <label>Tgl Jatuh Tempo Iuran Bulanan:</label>
                                        <select class="form-control" id="tanggal_jatuh_tempo_iuran_bulanan"
                                            name="tanggal_jatuh_tempo_iuran_bulanan">
                                            <option value=""> - Select Date - </option>
                                            <option <?php if ($setting->tanggal_jatuh_tempo_iuran_bulanan == '01') {
                                                echo 'selected';
                                            } ?> value="01">01</option>
                                            <option <?php if ($setting->tanggal_jatuh_tempo_iuran_bulanan == '02') {
                                                echo 'selected';
                                            } ?> value="02">02</option>
                                            <option <?php if ($setting->tanggal_jatuh_tempo_iuran_bulanan == '03') {
                                                echo 'selected';
                                            } ?> value="03">03</option>
                                            <option <?php if ($setting->tanggal_jatuh_tempo_iuran_bulanan == '04') {
                                                echo 'selected';
                                            } ?> value="04">04</option>
                                            <option <?php if ($setting->tanggal_jatuh_tempo_iuran_bulanan == '05') {
                                                echo 'selected';
                                            } ?> value="05">05</option>
                                            <option <?php if ($setting->tanggal_jatuh_tempo_iuran_bulanan == '06') {
                                                echo 'selected';
                                            } ?> value="06">06</option>
                                            <option <?php if ($setting->tanggal_jatuh_tempo_iuran_bulanan == '07') {
                                                echo 'selected';
                                            } ?> value="07">07</option>
                                            <option <?php if ($setting->tanggal_jatuh_tempo_iuran_bulanan == '08') {
                                                echo 'selected';
                                            } ?> value="08">08</option>
                                            <option <?php if ($setting->tanggal_jatuh_tempo_iuran_bulanan == '09') {
                                                echo 'selected';
                                            } ?> value="09">09</option>
                                            <option <?php if ($setting->tanggal_jatuh_tempo_iuran_bulanan == '10') {
                                                echo 'selected';
                                            } ?> value="10">10</option>

                                            <option <?php if ($setting->tanggal_jatuh_tempo_iuran_bulanan == '11') {
                                                echo 'selected';
                                            } ?> value="11">11</option>
                                            <option <?php if ($setting->tanggal_jatuh_tempo_iuran_bulanan == '12') {
                                                echo 'selected';
                                            } ?> value="12">12</option>
                                            <option <?php if ($setting->tanggal_jatuh_tempo_iuran_bulanan == '13') {
                                                echo 'selected';
                                            } ?> value="13">13</option>
                                            <option <?php if ($setting->tanggal_jatuh_tempo_iuran_bulanan == '14') {
                                                echo 'selected';
                                            } ?> value="14">14</option>
                                            <option <?php if ($setting->tanggal_jatuh_tempo_iuran_bulanan == '15') {
                                                echo 'selected';
                                            } ?> value="15">15</option>
                                            <option <?php if ($setting->tanggal_jatuh_tempo_iuran_bulanan == '16') {
                                                echo 'selected';
                                            } ?> value="16">16</option>
                                            <option <?php if ($setting->tanggal_jatuh_tempo_iuran_bulanan == '17') {
                                                echo 'selected';
                                            } ?> value="17">17</option>
                                            <option <?php if ($setting->tanggal_jatuh_tempo_iuran_bulanan == '18') {
                                                echo 'selected';
                                            } ?> value="18">18</option>
                                            <option <?php if ($setting->tanggal_jatuh_tempo_iuran_bulanan == '19') {
                                                echo 'selected';
                                            } ?> value="19">19</option>
                                            <option <?php if ($setting->tanggal_jatuh_tempo_iuran_bulanan == '20') {
                                                echo 'selected';
                                            } ?> value="20">20</option>
                                            <option <?php if ($setting->tanggal_jatuh_tempo_iuran_bulanan == '21') {
                                                echo 'selected';
                                            } ?> value="21">21</option>
                                            <option <?php if ($setting->tanggal_jatuh_tempo_iuran_bulanan == '22') {
                                                echo 'selected';
                                            } ?> value="22">22</option>
                                            <option <?php if ($setting->tanggal_jatuh_tempo_iuran_bulanan == '23') {
                                                echo 'selected';
                                            } ?> value="23">23</option>
                                            <option <?php if ($setting->tanggal_jatuh_tempo_iuran_bulanan == '24') {
                                                echo 'selected';
                                            } ?> value="24">24</option>
                                            <option <?php if ($setting->tanggal_jatuh_tempo_iuran_bulanan == '25') {
                                                echo 'selected';
                                            } ?> value="25">25</option>
                                            <option <?php if ($setting->tanggal_jatuh_tempo_iuran_bulanan == '26') {
                                                echo 'selected';
                                            } ?> value="26">26</option>
                                            <option <?php if ($setting->tanggal_jatuh_tempo_iuran_bulanan == '27') {
                                                echo 'selected';
                                            } ?> value="27">27</option>
                                            <option <?php if ($setting->tanggal_jatuh_tempo_iuran_bulanan == '28') {
                                                echo 'selected';
                                            } ?> value="28">28</option>
                                            <option <?php if ($setting->tanggal_jatuh_tempo_iuran_bulanan == '29') {
                                                echo 'selected';
                                            } ?> value="29">29</option>
                                            <option <?php if ($setting->tanggal_jatuh_tempo_iuran_bulanan == '30') {
                                                echo 'selected';
                                            } ?> value="30">30</option>
                                            <option <?php if ($setting->tanggal_jatuh_tempo_iuran_bulanan == '31') {
                                                echo 'selected';
                                            } ?> value="31">31</option>




                                        </select>
                                    </div>

                                    <div class="form-group mt20">
                                        <label>Tgl Create Iuran Bulanan:</label>
                                        <select class="form-control" id="tgl_create_iuran_bulanan"
                                            name="tgl_create_iuran_bulanan">
                                            <option value=""> - Select Date - </option>
                                            <option <?php if ($setting->tgl_create_iuran_bulanan == '01') {
                                                echo 'selected';
                                            } ?> value="01">01</option>
                                            <option <?php if ($setting->tgl_create_iuran_bulanan == '02') {
                                                echo 'selected';
                                            } ?> value="02">02</option>
                                            <option <?php if ($setting->tgl_create_iuran_bulanan == '03') {
                                                echo 'selected';
                                            } ?> value="03">03</option>
                                            <option <?php if ($setting->tgl_create_iuran_bulanan == '04') {
                                                echo 'selected';
                                            } ?> value="04">04</option>
                                            <option <?php if ($setting->tgl_create_iuran_bulanan == '05') {
                                                echo 'selected';
                                            } ?> value="05">05</option>
                                            <option <?php if ($setting->tgl_create_iuran_bulanan == '06') {
                                                echo 'selected';
                                            } ?> value="06">06</option>
                                            <option <?php if ($setting->tgl_create_iuran_bulanan == '07') {
                                                echo 'selected';
                                            } ?> value="07">07</option>
                                            <option <?php if ($setting->tgl_create_iuran_bulanan == '08') {
                                                echo 'selected';
                                            } ?> value="08">08</option>
                                            <option <?php if ($setting->tgl_create_iuran_bulanan == '09') {
                                                echo 'selected';
                                            } ?> value="09">09</option>
                                            <option <?php if ($setting->tgl_create_iuran_bulanan == '10') {
                                                echo 'selected';
                                            } ?> value="10">10</option>

                                            <option <?php if ($setting->tgl_create_iuran_bulanan == '11') {
                                                echo 'selected';
                                            } ?> value="11">11</option>
                                            <option <?php if ($setting->tgl_create_iuran_bulanan == '12') {
                                                echo 'selected';
                                            } ?> value="12">12</option>
                                            <option <?php if ($setting->tgl_create_iuran_bulanan == '13') {
                                                echo 'selected';
                                            } ?> value="13">13</option>
                                            <option <?php if ($setting->tgl_create_iuran_bulanan == '14') {
                                                echo 'selected';
                                            } ?> value="14">14</option>
                                            <option <?php if ($setting->tgl_create_iuran_bulanan == '15') {
                                                echo 'selected';
                                            } ?> value="15">15</option>
                                            <option <?php if ($setting->tgl_create_iuran_bulanan == '16') {
                                                echo 'selected';
                                            } ?> value="16">16</option>
                                            <option <?php if ($setting->tgl_create_iuran_bulanan == '17') {
                                                echo 'selected';
                                            } ?> value="17">17</option>
                                            <option <?php if ($setting->tgl_create_iuran_bulanan == '18') {
                                                echo 'selected';
                                            } ?> value="18">18</option>
                                            <option <?php if ($setting->tgl_create_iuran_bulanan == '19') {
                                                echo 'selected';
                                            } ?> value="19">19</option>
                                            <option <?php if ($setting->tgl_create_iuran_bulanan == '20') {
                                                echo 'selected';
                                            } ?> value="20">20</option>
                                            <option <?php if ($setting->tgl_create_iuran_bulanan == '21') {
                                                echo 'selected';
                                            } ?> value="21">21</option>
                                            <option <?php if ($setting->tgl_create_iuran_bulanan == '22') {
                                                echo 'selected';
                                            } ?> value="22">22</option>
                                            <option <?php if ($setting->tgl_create_iuran_bulanan == '23') {
                                                echo 'selected';
                                            } ?> value="23">23</option>
                                            <option <?php if ($setting->tgl_create_iuran_bulanan == '24') {
                                                echo 'selected';
                                            } ?> value="24">24</option>
                                            <option <?php if ($setting->tgl_create_iuran_bulanan == '25') {
                                                echo 'selected';
                                            } ?> value="25">25</option>
                                            <option <?php if ($setting->tgl_create_iuran_bulanan == '26') {
                                                echo 'selected';
                                            } ?> value="26">26</option>
                                            <option <?php if ($setting->tgl_create_iuran_bulanan == '27') {
                                                echo 'selected';
                                            } ?> value="27">27</option>
                                            <option <?php if ($setting->tgl_create_iuran_bulanan == '28') {
                                                echo 'selected';
                                            } ?> value="28">28</option>
                                            <option <?php if ($setting->tgl_create_iuran_bulanan == '29') {
                                                echo 'selected';
                                            } ?> value="29">29</option>
                                            <option <?php if ($setting->tgl_create_iuran_bulanan == '30') {
                                                echo 'selected';
                                            } ?> value="30">30</option>
                                            <option <?php if ($setting->tgl_create_iuran_bulanan == '31') {
                                                echo 'selected';
                                            } ?> value="31">31</option>
                                        </select>
                                    </div>

                                    <div class="form-group mt20">
                                        <label>Percent Denda Keterlambatan Iuran Bulanan (%):</label>
                                        <input type="text" class="form-control" id="percent_denda"
                                            name="percent_denda" placeholder="ex: 2.5"
                                            value="{{ $setting->percent_denda }}">
                                    </div>
                                    <div class="form-group mt20">
                                        <label>Maksimal Hari Booking :</label>
                                        <input type="text" class="form-control" id="max_order_day"
                                            name="max_order_day" placeholder="Maksmial Hari Booking"
                                            value="{{ $setting->max_order_day }}">
                                    </div>
                                    <div class="form-group mt20">
                                        <label>Versi terakhir aplikasi ANDROID:</label>
                                        <input type="text" class="form-control" id="version" name="version"
                                            placeholder="Versi terakhir aplikasi android" value="{{ $setting->version }}">
                                    </div>
                                     <div class="form-group mt20">
                                        <label>Versi terakhir aplikasi IOS:</label>
                                        <input type="text" class="form-control" id="version_ios" name="version_ios"
                                            placeholder="Versi terakhir aplikasi IOS" value="{{ $setting->version_ios }}">
                                    </div>

                                </div>

                            </div>
                        </div>

                    </div>
                    <button type="submit" class="btn btn-success mt30">Submit</button>
                </form>
            </div>
        </div>
        <div class="card mt20">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Upgrade Iuran User in (%)</label>
                            <input type="text" class="form-control" id="upgrade-iuran" name="upgrade-iuran"
                                placeholder="enter your percent upgrade">
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group">
                            <label>BLOK</label>
                            <select class="form-control" id="blok">
                                <option value=""> - All Blok - </option>
                                @foreach ($bloks as $blok)
                                    <option value="{{ $blok->blok_name }}">{{ $blok->blok_name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                </div>

                <button id="btn-upgrade-iuran" class="btn btn-success mt20">Upgrade Now</button>
            </div>
        </div>
        <div class="card mt20">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Tambah Blok Perumahan</label>
                            <input type="hidden" id="blok_id">
                            <input type="hidden" id="_method" value="add">
                            <input type="text" class="form-control" id="blok-tambah" name="blok-tambah"
                                placeholder="enter new BLOK">
                            <button id="btn-add-blok" class="btn btn-success mt20">Submit</button>
                        </div>
                    </div>


                    <div class="col-md-6">
                        <table id="table-blok-setting" class="table table-striped table-bordered">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Action</th>
                                    <th>Blok Name</th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
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