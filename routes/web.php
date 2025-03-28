<?php
   
use App\Http\Controllers\Admins\AdminsController;
use App\Http\Controllers\Admins\AuthController;
use App\Http\Controllers\Admins\BannerIklanController;
use App\Http\Controllers\Admins\DashboardController;
use App\Http\Controllers\Admins\PenyeliaController;
use App\Http\Controllers\Admins\PenyeliaKategoriController;
use App\Http\Controllers\Admins\UnitBisnisController;
use App\Http\Controllers\Admins\UserController;
use App\Http\Controllers\Admins\TransactionController;
use App\Http\Controllers\Admins\TicketController;
use App\Http\Controllers\Admins\PembayaranController;
use App\Http\Controllers\Admins\BroadcastingController;
use App\Http\Controllers\Admins\ReportIuranController;
use App\Http\Controllers\Admins\ReportUnitController;
use App\Http\Controllers\Admins\ReportLainController;
use App\Http\Controllers\Admins\SettingController;
use App\Http\Controllers\Admins\BookingSettingController;
use App\Http\Controllers\Admins\OutstandingController;
use App\Http\Controllers\Admins\ReportActivity;
use App\Http\Controllers\Main\AuthUsersController;
use App\Http\Controllers\Main\DashboardMainController;
use App\Http\Controllers\Main\TicketingController;
use App\Http\Controllers\Main\NotifController;
use App\Http\Controllers\Main\PaymentController;
use App\Http\Controllers\Main\DuitkuCallback;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Hash;


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/



// Route::get('/',[DashboardMainController::class, 'index'])->name("home_public");
Route::get('/',[AuthUsersController::class, 'login'])->name("home_public");

// Route::get('register-user',[AuthUsersController::class, 'index_register'])->name("register_user");
// Route::get('auth-user',[AuthUsersController::class, 'index_login'])->name('login_user');
// Route::get('/dashboard', [DashboardMainController::class, 'dashboard']);
Route::get('/term', [DashboardMainController::class, 'term']);
Route::get('/privacy', [DashboardMainController::class,'privacy']);
Route::get('/contact', [DashboardMainController::class, 'contact']);
Route::get('/sendwa', [DashboardMainController::class, 'send_wa']);
Route::get('/frontend_register', [AuthUsersController::class, 'register']);

Route::get('/account_removal', [AuthUsersController::class, 'account_removal']);
// Route::post('/register_now', [AuthUsersController::class, 'register_now'])->name('register_now');
Route::get('/otp', [AuthUsersController::class, 'otp']);
Route::post('/send_otp', [AuthUsersController::class, 'send_otp'])->name('send.otp');
Route::get('/login', [AuthUsersController::class, 'login'])->name('login_user');
Route::post('/frontend_login', [AuthUsersController::class, 'frontend_login'])->name('frontend.login');
Route::get('/frontend_logout', [AuthUsersController::class, 'logout']);

Route::group(['middleware' => 'auth'], function () {
   Route::get('/frontend_dashboard', [AuthUsersController::class, 'dashboard']); 
   Route::get('/frontend_booking', [AuthUsersController::class, 'booking']);
   Route::get('/booking_detail/{slug}', [AuthUsersController::class, 'booking_detail']);
   Route::get('/display_calendar/{bulan}/{tahun}', [AuthUsersController::class, 'display_calendar']);
   Route::post('/booking_time', [AuthUsersController::class, 'booking_time']);
   Route::post('/transaction', [AuthUsersController::class, 'transaction']);
   Route::get('/riwayat', [AuthUsersController::class, 'riwayat']);
   Route::post('/payment_process', [AuthUsersController::class, 'payment_process']);
  
   Route::get('/print_ticket/{id}', [AuthUsersController::class, 'print']);
   Route::get('/ticketing', [TicketingController::class, 'index'] );
   Route::get('/ticketing_add', [TicketingController::class, 'add']);
   Route::post('/open_ticket', [TicketingController::class, 'open'])->name('open.ticket');
   Route::get('/ticketing_detail/{number}', [TicketingController::class, 'ticketing_detail']);
   Route::get('/donwload_ticketing/{file}', [TicketingController::class, 'download']);
   Route::post('/reply_ticket', [TicketingController::class, 'reply'])->name('reply.ticket');
   
   Route::get('/user_data', [AuthUsersController::class, 'user_data']);
   
   Route::get('/frontend_setting', [AuthUsersController::class, 'setting']);
   Route::post('/profile_update', [AuthUsersController::class, 'profile_update'])->name('profile.update');
   Route::get('/frontend_change_password', [AuthUsersController::class, 'change_password']);
   Route::post('frontend_password_update', [AuthUsersController::class, 'password_update'])->name('password.update');
   Route::patch('/fcm_token', [AuthUsersController::class, 'fcm_token'])->name('fcm.token');
   Route::get('/notify', [AuthUsersController::class, 'notify']);
   Route::get('/notif_list', [NotifController::class, 'index']);
   Route::get('/notif_detail/{id}', [NotifController::class, 'notif_detail']);
   
   Route::get('/payment', [PaymentController::class, 'index']);
   Route::post('/payment_post', [PaymentController::class, 'payment_post'])->name('payment.post');
   Route::get('/payment_link_share/{id}', [PaymentController::class, 'payment_link_share']);

   Route::get('/print_kwitansi/{id}', [PaymentController::class, 'kwitansi']);
});

Route::get('/save_firebase_token/{token}', [DashboardMainController::class, 'save_fcm_token']);

Route::get('/notify', [AuthUsersController::class, 'notify']);
Route::get('/update_notif_number', [NotifController::class, 'update_notif_number']);

Route::get('/payment_link_external/{token}', [PaymentController::class, 'payment_link_external']);




Route::post('/xendit_callback', [DuitkuCallback::class, 'callback']);
// a808e2df9c7b1ad752ec37c0defbb119

// for admins

Route::get('/login-admins', [AuthController::class, 'index'])->name("login_admin");
Route::get('backdata/save_firebase_token/{token}', [AuthController::class, 'save_firebase_token']);
Route::get('backdata/update_notif_number', [TicketController::class, 'update_notif_number']);

Route::middleware(['throttle:webAuthAdmin'])->group(function(){
   Route::post('/process-auth-admin',[AuthController::class, 'prosesAuth']);
});

Route::get('/check_payment_routine', [PembayaranController::class, 'check_payment_routine']);
Route::get('/notifikasi_bulanan', [PembayaranController::class, 'notifikasi_bulanan']);
Route::get('/cek_expired_booking', [TransactionController::class, 'cek_expired_booking']);


Route::prefix("backdata")
->middleware('authAdmins')
->group(function() {
   Route::get('/',[DashboardController::class, 'index'])->name("home_admin");
   Route::get("outstanding-dashboard", [DashboardController::class, 'ajax_list'])->name('outstanding.dashboard');

   Route::get('/logout',[AuthController::class, 'logout'])->name("logout_admin");
   

   // admin crud register
   Route::post("admins-list",[AdminsController::class,'ajax_list']) ;
   Route::post("admins-list-trash",[AdminsController::class,'ajax_list_trash']) ;
   Route::get("admins/{id}/trash",[AdminsController::class,'editTrash']) ;
   Route::post("admins/{id}/restore",[AdminsController::class,'restore']) ;
   Route::resource("admins",AdminsController::class)->middleware('reguler');
   

   // kategori penyelia crud data
   Route::post("penyelia-kategori-list",[PenyeliaKategoriController::class,'ajax_list']) ;
   Route::post("penyelia-kategori-list-trash",[PenyeliaKategoriController::class,'ajax_list_trash']) ;
   Route::get("penyelia-kategori/{id}/trash",[PenyeliaKategoriController::class,'editTrash']) ;
   Route::post("penyelia-kategori/{id}/restore",[PenyeliaKategoriController::class,'restore']) ;
   Route::resource("penyelia-kategori",PenyeliaKategoriController::class);

   //penyelia crud data
   Route::post("penyelia-list",[PenyeliaController::class,'ajax_list']) ;
   Route::post("penyelia-list-trash",[PenyeliaController::class,'ajax_list_trash']) ;
   Route::get("penyelia/{id}/trash",[PenyeliaController::class,'editTrash']) ;
   Route::post("penyelia/{id}/restore",[PenyeliaController::class,'restore']) ;
   Route::resource("penyelia",PenyeliaController::class);

   // banner iklan
   Route::post("banner-iklan-list",[BannerIklanController::class,'ajax_list']) ;
   Route::post("banner-iklan-list-trash",[BannerIklanController::class,'ajax_list_trash']) ;
   Route::get("banner-iklan/{id}/trash",[BannerIklanController::class,'editTrash']) ;
   Route::post("banner-iklan/{id}/restore",[BannerIklanController::class,'restore']) ;
   Route::resource("banner-iklan",BannerIklanController::class);

   Route::get("outstanding-list", [OutstandingController::class, 'ajax_list'])->name('outstanding.list');
   Route::resource("outstanding", OutstandingController::class);
   Route::post('save_adjustment', [OutstandingController::class, 'save_adjustment']);


   // user data
   Route::post("user-list",[UserController::class,'ajax_list'])->name('user.list') ;
   Route::post("user-list-trash",[UserController::class,'ajax_list_trash']) ;
   Route::get("user/{id}/trash",[UserController::class,'editTrash']) ;
   Route::post("user/{id}/restore",[UserController::class,'restore']) ;
   Route::resource("user",UserController::class);
   Route::get('print_detail/{id}', [UserController::class, 'print_detail']);
   Route::post('upgrade_iuran_bulanan', [UserController::class, 'upgrade_iuran_bulanan']);
   Route::get('print_form_permohonan/{id}', [UserController::class, 'print_form_permohonan']);
   Route::post('user-upload', [UserController::class, 'user_upload'])->name('user.upload');
   Route::get('download-template-edit', [UserController::class, 'download_template_edit']);
   Route::post('user-upload-update', [UserController::class, 'user_upload_update'])->name('user.upload.update');


   // transaction data
   Route::post("transaction-list",[TransactionController::class,'ajax_list'])->name('transaction.list') ;
   Route::resource("transaction",TransactionController::class);
   Route::get('print_transaction/{id}', [TransactionController::class, 'print_transaction']);
   Route::post('booking_payment', [TransactionController::class, 'payment']);
   Route::get('booking_print_ticket/{id}', [TransactionController::class, 'print_ticket']);
   Route::post('transaction_cancel', [TransactionController::class, 'transaction_cancel']);

   // ticketing data
   Route::post("ticketing-list",[TicketController::class,'ajax_list'])->name('ticketing.list') ;
   Route::resource("ticketing",TicketController::class);
   Route::get('print_ticketing/{id}', [TicketController::class, 'print_ticketing']);
   Route::post('payment', [TicketController::class, 'payment']);
   Route::get('print_ticket/{id}', [TicketController::class, 'print_ticket']);
   Route::post('set_on_hold', [TicketController::class, 'set_on_hold']);
   Route::post('set_resolved', [TicketController::class, 'set_resolved']);
   Route::get('payment_ticketing_list/{id}', [TicketController::class, 'payment_ticketing_list']);
   Route::post('add_ticketing_payment', [TicketController::class, 'add_ticketing_payment']);


Route::resource("pembayaran",PembayaranController::class);
   Route::get("pembayaran-list",[PembayaranController::class,'ajax_list'])->name('pembayaran.list') ;
   
   Route::get('kwitansi/{id}', [PembayaranController::class, 'kwitansi']);
   Route::post('delete_payment', [PembayaranController::class, 'delete_payment']);
   Route::post('process_payment', [PembayaranController::class, 'process_payment']);
   Route::get('payment_admin/{id}', [PembayaranController::class, 'payment_admin']);
   Route::get('get_iuran_bulanan/{id}', [PembayaranController::class, 'get_iuran_bulanan']);
   Route::get('check_due_bills', [PembayaranController::class, 'check_due_bills']);



   Route::get("broadcasting-list",[BroadcastingController::class,'ajax_list'])->name('broadcasting.list') ;
   Route::resource("broadcasting",BroadcastingController::class);
   Route::get('check_broadcasting', [BroadcastingController::class, 'check_broadcasting']);


   Route::post("report-iuran-list",[ReportIuranController::class,'ajax_list'])->name('report.iuran.list') ;
   Route::resource("report-iuran",ReportIuranController::class);
   Route::get('print_kas_detail/{awal}/{akhir}/{payment}/{penyelia}', [ReportIuranController::class, 'print_kas_detail']);
   Route::get('print_kas_detail_pdf/{awal}/{akhir}/{payment}/{penyelia}', [ReportIuranController::class, 'print_kas_detail_pdf']);
   Route::get('print_financing_pdf/{awal}/{akhir}/{payment}/{penyelia}', [ReportIuranController::class, 'print_financing_pdf']);
   Route::get('print_kas_detail_excel/{awal}/{akhir}', [ReportIuranController::class, 'export']);
   Route::get('print_iuran_financing/{awal}/{akhir}/{payment}/{penyelia}', [ReportIuranController::class, 'print_iuran_financing']);
   Route::get('print_financing_excel/{awal}/{akhir}', [ReportIuranController::class, 'financing_export']);
   Route::get('print_export_accounting/{awal}/{akhir}/{payment}/{penyelia}', [ReportIuranController::class, 'print_export_accounting']);


   Route::post("report-unit-list",[ReportUnitController::class,'ajax_list'])->name('report.unit.list') ;
   Route::resource("report-unit",ReportUnitController::class);
   Route::get('print_unit_report/{awal}/{akhir}/{payment}/{unit}', [ReportUnitController::class, 'print_unit_report']);
   Route::get('print_unit_report_pdf/{awal}/{akhir}/{payment}/{unit}', [ReportUnitController::class, 'print_unit_report_pdf']);
   Route::get('print_unit_report_excel/{awal}/{akhir}/{payment}/{unit}', [ReportUnitController::class, 'print_unit_report_excel']);
   Route::get('print_unit_accounting/{awal}/{akhir}/{payment}/{unit}', [ReportUnitController::class, 'print_unit_accounting']);


   Route::post("report-lain-list",[ReportLainController::class,'ajax_list'])->name('report.lain.list') ;
   Route::resource("report-lain",ReportLainController::class);
   Route::get('print_lain_report/{awal}/{akhir}/{payment}/{penyelia}', [ReportLainController::class, 'print_lain_report']);
   Route::get('print_lain_report_pdf/{awal}/{akhir}/{payment}/{penyelia}', [ReportLainController::class, 'print_lain_report_pdf']);
   Route::get('print_lain_report_excel/{awal}/{akhir}', [ReportLainController::class, 'print_lain_report_excel']);
   Route::get('print_lain_accounting/{awal}/{akhir}/{payment}/{penyelia}', [ReportLainController::class, 'print_export_accounting']);


   Route::get('report-activity', [ReportActivity::class, 'index']);
   Route::post("report-activity-list",[ReportActivity::class,'ajax_list'])->name('report.activity.list') ;

   Route::resource("setting",SettingController::class)->middleware('appsetting');
   
   Route::post('setting_update', [SettingController::class, 'setting_update'])->name('backdata.setting.update');

   Route::get('change_password', [SettingController::class, 'change_password']);
   Route::post('password_admin_update', [SettingController::class, 'password_admin_update'])->name('backdata.password.update');
   Route::get('blok_setting_list', [SettingController::class, 'blok_setting_list'])->name('blok.list');
   Route::post('add_blok', [SettingController::class, 'add_blok']);
   Route::get('blok_edit/{id}', [SettingController::class, 'blok_edit']);
   Route::post('blok_delete', [SettingController::class, 'blok_delete']);

   Route::resource('booking_setting', BookingSettingController::class);
   Route::get("booking-setting-list",[BookingSettingController::class,'ajax_list'])->name('booking.setting.list') ;

   
   


   // unit bisnis
   Route::post("unit-bisnis-list",[UnitBisnisController::class,'ajax_list']) ;
   Route::post("unit-bisnis-list-trash",[UnitBisnisController::class,'ajax_list_trash']) ;
   Route::get("unit-bisnis/{id}/trash",[UnitBisnisController::class,'editTrash']) ;
   Route::post("unit-bisnis/{id}/restore",[UnitBisnisController::class,'restore']) ;
   Route::resource("unit-bisnis",UnitBisnisController::class);
   
   Route::get('print_data_transaction/{awal}/{akhir}/{payment}/{unit}/{payment_status}/{pilih}', [TransactionController::class, 'print_data_transaction']);   


});




Route::group(['prefix' => 'filemanager', 'middleware' => ['authAdmins']], function () {
 
});