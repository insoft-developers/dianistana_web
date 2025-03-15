<?php

namespace App\Http\Controllers\Admins;

use App\Helpers\Resp;
use Illuminate\View\View;
use App\Models\User;
use App\Models\Transaction;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use App\Traits\DBcustom\DataTablesTraitStatic;
use App\Traits\UserLogTrait;
use DateTime;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Yajra\DataTables\Facades\DataTables;
use Google\Client as GoogleClient;
class TransactionController extends Controller
{
    use DataTablesTraitStatic;
    use UserLogTrait;
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $view = 'transaction';
        $unit = \App\Models\UnitBisnis::all();
        return view('admins.transaction.index', compact('view', 'unit'));
    }

    public function ajax_list(Request $request)
    {
        $input = $request->all();
        $awal = $input['awal'];
        $akhir = $input['akhir'];
        $unit = $input['unit'];
        $ending = strtotime('+1 day', strtotime($akhir));
        $sampai = date('Y-m-d', $ending);
        $pilih = $input['pilih'];

        if (empty($awal) && empty($akhir)) {
            $bln = date('m');
            $thn = date('Y');
            $tanggal_akhir = cal_days_in_month(CAL_GREGORIAN, $bln, $thn);
            $start = $thn . '-' . $bln . '-01';
            $end = $thn . '-' . $bln . '-' . $tanggal_akhir;

            if ($pilih == 'transaksi') {
                $query = Transaction::where('transactions.created_at', '>=', $start . ' 00:00:01')->where('transactions.created_at', '<=', $end . ' 23:59:59');
            } elseif ($pilih == 'booking') {
                $query = Transaction::where('transactions.booking_date', '>=', $start)->where('transactions.booking_date', '<=', $end);
            } elseif ($pilih == 'pembayaran') {
                $query = Transaction::where('transactions.paid_at', '>=', $start)->where('transactions.paid_at', '<=', $end);
            }
        } else {
            if ($pilih == 'transaksi') {
                $query = Transaction::where('transactions.created_at', '>=', $awal . ' 00:00:01')->where('transactions.created_at', '<=', $akhir . ' 23:59:59');
            } elseif ($pilih == 'booking') {
                $query = Transaction::where('transactions.booking_date', '>=', $awal)->where('transactions.booking_date', '<=', $akhir);
            } elseif ($pilih == 'pembayaran') {
                $query = Transaction::where('transactions.paid_at', '>=', $awal)->where('transactions.paid_at', '<=', $akhir);
            }
        }

        if ($request->payment == '1') {
            $query->where('total_price', 0);
        } elseif ($request->payment == '2') {
            $query->where('total_price', '!=', 0);
        }

        // $query->where('total_price', '<=', 0);

        if (!empty($input['unit'])) {
            $query->where('business_unit_id', $input['unit']);
        }

        if (!empty($input['payment_status'])) {
            $query->where('payment_status', $input['payment_status']);
        }

        $data = $query->get();

        return DataTables::of($data)
            ->addColumn('created_at', function ($data) {
                return '<div>' . date('d-m-Y', strtotime($data->created_at)) . '<br>' . date('H:i:s', strtotime($data->created_at)) . '</div>';
            })
            ->addColumn('paid_at', function ($data) {
                return '<div>' . date('d-m-Y', strtotime($data->paid_at)) . '</div>';
            })
            ->addColumn('user_id', function ($data) {
                $users = \App\Models\User::where('id', $data->user_id);
                if ($users->count() > 0) {
                    $user = $users->first();
                    return $user->name . '<br>( ' . $user->level . ' ) ' . $user->blok . ' - ' . $user->nomor_rumah;
                } else {
                    return '';
                }
            })
            ->addColumn('business_unit_id', function ($data) {
                $units = \App\Models\UnitBisnis::where('id', $data->business_unit_id);
                if ($units->count() > 0) {
                    $unit = $units->first();
                    return $unit->name_unit;
                } else {
                    return '';
                }
            })
            ->addColumn('detail', function ($data) {
                return date('d-m-Y', strtotime($data->booking_date)) . '<br>' . $data->start_time . ' - ' . $data->finish_time;
            })
            ->addColumn('total_price', function ($data) {
                if ($data->total_price > 0) {
                    return '<div style="text-align:right;">' . number_format($data->total_price) . '</div>';
                } else {
                    return 'Free';
                }
            })
            ->addColumn('payment_status', function ($data) {
                if ($data->payment_status == 'PENDING') {
                    return '<span class="badge text-warning">PENDING</span>';
                } elseif ($data->payment_status == 'PAID') {
                    return '<span class="badge text-success"><i class="fa fa-check"></i> PAID</span>';
                } elseif ($data->payment_status == 'CANCELLED') {
                    return '<span class="badge text-danger"><i class="fa fa-trash"></i> CANCELLED</span>';
                }
            })
            ->addColumn('action', function ($data) {
                if (adminAuth()->level == 'admin') {
                    if ($data->payment_status == 'PAID') {
                        $button = '';
                        $button .= '<a href="javascript:void(0);" class="bs-tooltip text-success mb-2" data-bs-toggle="tooltip" data-bs-placement="top" data-original-title="Detail" aria-label="Edit" data-bs-original-title="Detail" title="Detail" onclick="detailData(' . $data->id . ')"><i class="far fa-file"></i></a>&nbsp;&nbsp;';
                        $button .= '<a href="javascript:void(0);" class="bs-tooltip text-success mb-2" data-bs-toggle="tooltip" data-bs-placement="top" data-original-title="Print Receipt" aria-label="Print Receipt" data-bs-original-title="Print Receipt" title="Print Receipt" onclick="printData(' . $data->id . ')"><i class="fa fa-print"></i></a>';
                        return $button;
                    } elseif ($data->payment_status == 'CANCELLED') {
                        $button = '';
                        $button .= '<a href="javascript:void(0);" class="bs-tooltip text-success mb-2" data-bs-toggle="tooltip" data-bs-placement="top" data-original-title="Detail" aria-label="Detail" data-bs-original-title="Detail" title="Detail" onclick="detailData(' . $data->id . ')"><i class="far fa-file"></i></a>';
                        return $button;
                    } else {
                        $button = '';
                        $button .= '<a href="javascript:void(0);" class="bs-tooltip text-success mb-2" data-bs-toggle="tooltip" data-bs-placement="top" data-original-title="Detail" aria-label="Detail" data-bs-original-title="Detail" title="Detail" onclick="detailData(' . $data->id . ')"><i class="far fa-file"></i></a>&nbsp;&nbsp;';

                        if (adminAuth()->role == 1) {
                            $button .= '<a href="javascript:void(0);" class="bs-tooltip text-warning mb-2" data-bs-toggle="tooltip" data-bs-placement="top" data-original-title="Payment" aria-label="Payment" data-bs-original-title="Payment" title="Payment" onclick="paymentData(' . $data->id . ')"><i class="fa fa-file-invoice-dollar"></i></a>';
                        }
                        return $button;
                    }
                } else {
                    if ($data->payment_status == 'PAID') {
                        $button = '';
                        $button .= '<a href="javascript:void(0);" class="bs-tooltip text-success mb-2" data-bs-toggle="tooltip" data-bs-placement="top" data-original-title="Detail" aria-label="Edit" data-bs-original-title="Detail" title="Detail" onclick="detailData(' . $data->id . ')"><i class="far fa-file"></i></a>&nbsp;&nbsp;';
                        $button .= '<a href="javascript:void(0);" class="bs-tooltip text-success mb-2" data-bs-toggle="tooltip" data-bs-placement="top" data-original-title="Print Receipt" aria-label="Print Receipt" data-bs-original-title="Print Receipt" title="Print Receipt" onclick="printData(' . $data->id . ')"><i class="fa fa-print"></i></a>&nbsp;&nbsp;';
                        
                        if (adminAuth()->level == 'manager') {
                            $button .= '<a href="javascript:void(0);" class="bs-tooltip text-warning mb-2" data-bs-toggle="tooltip" data-bs-placement="top" data-original-title="Hapus" aria-label="Hapus" data-bs-original-title="Hapus" title="Cancel Booking" onclick="cancelData(' . $data->id . ')"><i class="fa fa-list"></i></i></a>&nbsp;&nbsp';
                        }
                        
                        
                        $button .= '<a href="javascript:void(0);" class="bs-tooltip text-danger mb-2" data-bs-toggle="tooltip" data-bs-placement="top" data-original-title="Hapus" aria-label="Hapus" data-bs-original-title="Hapus" title="Hapus" onclick="deleteData(' . $data->id . ')"><i class="far fa-times-circle"></i></i></a>';
                        return $button;
                    } elseif ($data->payment_status == 'CANCELLED') {
                        $button = '';
                        $button .= '<a href="javascript:void(0);" class="bs-tooltip text-success mb-2" data-bs-toggle="tooltip" data-bs-placement="top" data-original-title="Detail" aria-label="Detail" data-bs-original-title="Detail" title="Detail" onclick="detailData(' . $data->id . ')"><i class="far fa-file"></i></a>&nbsp;&nbsp;';
                        $button .= '<a href="javascript:void(0);" class="bs-tooltip text-danger mb-2" data-bs-toggle="tooltip" data-bs-placement="top" data-original-title="Hapus" aria-label="Hapus" data-bs-original-title="Hapus" title="Hapus" onclick="deleteData(' . $data->id . ')"><i class="far fa-times-circle"></i></i></a>';
                        return $button;
                    } else {
                        $button = '';
                        $button .= '<a href="javascript:void(0);" class="bs-tooltip text-success mb-2" data-bs-toggle="tooltip" data-bs-placement="top" data-original-title="Detail" aria-label="Detail" data-bs-original-title="Detail" title="Detail" onclick="detailData(' . $data->id . ')"><i class="far fa-file"></i></a>&nbsp;&nbsp;';
                        if (adminAuth()->role == 1) {
                            $button .= '<a href="javascript:void(0);" class="bs-tooltip text-warning mb-2" data-bs-toggle="tooltip" data-bs-placement="top" data-original-title="Payment" aria-label="Payment" data-bs-original-title="Payment" title="Payment" onclick="paymentData(' . $data->id . ')"><i class="fa fa-file-invoice-dollar"></i></a>&nbsp;&nbsp;';
                        }
                        if (adminAuth()->level == 'manager') {
                            $button .= '<a href="javascript:void(0);" class="bs-tooltip text-warning mb-2" data-bs-toggle="tooltip" data-bs-placement="top" data-original-title="Hapus" aria-label="Hapus" data-bs-original-title="Hapus" title="Cancel Booking" onclick="cancelData(' . $data->id . ')"><i class="fa fa-list"></i></i></a>&nbsp;&nbsp';
                        }
                        $button .= '<a href="javascript:void(0);" class="bs-tooltip text-danger mb-2" data-bs-toggle="tooltip" data-bs-placement="top" data-original-title="Hapus" aria-label="Hapus" data-bs-original-title="Hapus" title="Hapus" onclick="deleteData(' . $data->id . ')"><i class="far fa-times-circle"></i></i></a>';
                        return $button;
                    }
                }
            })
            ->rawColumns(['action', 'detail', 'created_at', 'user_id', 'business_unit_id', 'total_price', 'paid_at', 'payment_status'])
            ->addIndexColumn()
            ->make(true);
    }

    public function ajax_list_trash(Request $request)
    {
        abort(404);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        abort(404);
    }

    private static function intReplace($val): int
    {
        return intval(str_replace('.', '', $val));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        abort(404);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $transaction = Transaction::findorFail($id);
        $users = \App\Models\User::where('id', $transaction->user_id);
        if ($users->count() > 0) {
            $user = $users->first();
            $userdata = $user->name;
        } else {
            $userdata = 'no-data';
        }

        $units = \App\Models\UnitBisnis::where('id', $transaction->business_unit_id);
        if ($units->count() > 0) {
            $unit = $units->first();
            $unitdata = $unit->name_unit;
        } else {
            $unitdata = 'no-data';
        }

        $HTML = '';
        $HTML .= '<input type="hidden" value="' . $id . '" id="id-transaction">';
        $HTML .= '<div class="row">';
        $HTML .= '<div class="col-md-12">';
        $HTML .= '<div class="card">';
        $HTML .= '<div class="card-body">';
        $HTML .= '<table class="table table-bordered table-striped">';
        $HTML .= '<tbody>';
        $HTML .= '<tr><th>User Name</th><th>' . $userdata . '</th></tr>';
        $HTML .= '<tr><th>Facility</th><th>' . $unitdata . '</th></tr>';
        $HTML .= '<tr><th>Invoice</th><th>' . $transaction->invoice . '</th></tr>';
        $HTML .= '<tr><th>Booking Date</th><th>' . date('d F Y', strtotime($transaction->booking_date)) . '</th></tr>';
        $HTML .= '<tr><th>Booking Time</th><th>' . $transaction->start_time . '.00 WIB - ' . $transaction->finish_time . '.00 WIB</th></tr>';
        $HTML .= '<tr><th>Number of User</th><th>' . $transaction->quantity . '</th></tr>';
        $HTML .= '<tr><th>Total Price</th><th>Rp. ' . number_format($transaction->total_price) . '</th></tr>';
        $HTML .= '<tr><th>Description</th><th>' . $transaction->description . '</th></tr>';
        $HTML .= '<tr><th>Package</th><th>' . $transaction->package_name . '</th></tr>';
        $HTML .= '<tr><th>Payment Status</th><th>' . $transaction->payment_status . '</th></tr>';
        $HTML .= '<tr><th>Payment Method</th><th>' . $transaction->payment_method . '</th></tr>';
        $HTML .= '<tr><th>Payment Channel</th><th>' . $transaction->payment_channel . '</th></tr>';
        $HTML .= '<tr><th>Paid At</th><th>' . date('d F Y', strtotime($transaction->paid_at)) . '</th></tr>';
        $HTML .= '<tr><th>Created At</th><th>' . date('d F Y', strtotime($transaction->created_at)) . '</th></tr>';
        $HTML .= '</tbody>';
        $HTML .= '</table>';
        $HTML .= '</div>'; //cardbody
        $HTML .= '</div>'; //card
        $HTML .= '</div>'; //col-md-6
        $HTML .= '</div>'; //row

        return $HTML;
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        abort(404);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        abort(404);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $transaction = Transaction::find($id);

        $this->insert_log(adminAuth()->id, 'booking transaction (invoice: ' . $transaction->invoice . ')', 'delete');
        $query = Transaction::destroy($id);
        return $query;
    }

    public function payment(Request $request)
    {
        $input = $request->all();

        $data = Transaction::findorFail($input['id']);
        $data->payment_status = 'PAID';
        $data->paid_at = date('Y-m-d H:i:s');
        $data->payment_method = 'ADMIN';
        $data->payment_channel = adminAuth()->name;
        $data->save();

        $this->insert_log(adminAuth()->id, 'booking transaction (payment status on invoice ' . $data->invoice . ')', 'update');
        return response()->json([
            'success' => true,
            'message' => 'payment success',
        ]);
    }

    public function print_ticket($id)
    {
        $tran = \App\Models\Transaction::where('id', (int) $id)->where('payment_status', 'PAID');
        if ($tran->count() <= 0) {
            return redirect('/backdata/transaction');
        }
        $trans = $tran->first();
        $user = \App\Models\User::findorFail($trans->user_id);
        $product = \App\Models\UnitBisnis::findorFail($trans->business_unit_id);
        return view('admins.transaction.ticket', compact('trans', 'user', 'product'));
    }

    public function print_transaction($id)
    {
        $transaction = Transaction::findorFail($id);
        return view('admins.transaction.print', compact('transaction'));
    }

    public function cek_expired_booking()
    {
        $setting = \App\Models\Setting::findorFail(1);
        $expired_time = $setting->booking_expired;

        $cek = Transaction::where('payment_status', 'PENDING')->get();
        $expired = [];
        foreach ($cek as $key) {
            $tanggal = date('Y-m-d H:i:s', strtotime($key->created_at));
            $sekarang = date('Y-m-d H:i:s');
            $tgl1 = new DateTime($tanggal);
            $tgl2 = new DateTime($sekarang);
            $jarak = $tgl2->diff($tgl1);
            $row['hari'] = $jarak->d;
            $row['jam'] = $jarak->h;
            $row['menit'] = $jarak->i;
            $row['id'] = $key->id;

            array_push($expired, $row);
        }

        foreach ($expired as $e) {
            $menit_hari = 1440 * (int) $e['hari'];
            $menit_jam = 60 * (int) $e['jam'];
            $total_menit = $menit_hari + $menit_jam + (int) $e['menit'];

            // if ($e['hari'] == 0 && $e['jam'] == 0 && $e['menit'] <= $expired_time) {
            if ($total_menit <= $expired_time) {
            } else {
                $trans = Transaction::findorFail($e['id']);
                $trans->payment_status = 'CANCELLED';
                $trans->save();
                $message = 'Your booking with invoice number ' . $trans->invoice . ' has been cancelled automatically by sistem';
                $this->notify('Booking Cancelled', $message, $trans->user_id);
            }
        }

        return $expired;
    }

    public function print_data_transaction($awal, $akhir, $payment, $unit, $payment_status, $pilih)
    {
        if ($pilih == 'transaksi') {
            $query = Transaction::where('created_at', '>=', $awal . ' 00:00:01')->where('created_at', '<=', $akhir . ' 23:59:59');
        } elseif ($pilih == 'booking') {
            $query = Transaction::where('booking_date', '>=', $awal)->where('booking_date', '<=', $akhir);
        } elseif ($pilih == 'pembayaran') {
            $query = Transaction::where('paid_at', '>=', $awal)->where('paid_at', '<=', $akhir);
        }

        if ($payment == '1') {
            $query->where('total_price', 0);
        } elseif ($payment == '2') {
            $query->where('total_price', '!=', 0);
        }

        if ($unit != '0') {
            $query->where('business_unit_id', $unit);
        }

        if ($payment_status != '0') {
            $query->where('payment_status', $payment_status);
        }

        $data = $query->get();

        return view('admins.transaction.print', compact('data', 'awal', 'akhir', 'unit', 'payment', 'payment_status', 'pilih'));
    }
    
    public function transaction_cancel(Request $request) {
        $input = $request->all();
        
        $data = Transaction::findorFail($input['id']);
        
        $query = Transaction::where('id', $input['id'])->update([
            "payment_status" => "CANCELLED",
            "updated_at" => date("Y-m-d H:i:s"),
            "cancelation_reason" => $input['text']
        ]);
        
        $this->insert_log(adminAuth()->id, 'cancelation booking transaction ('.$input['text'].' - Invoice '. $data->invoice . ')', 'update');
        $message = "Your booking with invoice number ".$data->invoice.' has been cancelled by Admin by reason ( '.$input['text'].' )';
        $this->notify("Booking Cancelled By Admin", $message, $data->user_id);
        return $query;
    }
    
    public function notify($title, $message, $user_id)
    {
        $title = $title;
        $description = $message;

        $credentialsFilePath = 'json/file.json';

        $client = new GoogleClient();
        $client->setAuthConfig($credentialsFilePath);
        $client->addScope('https://www.googleapis.com/auth/firebase.messaging');
        $client->refreshTokenWithAssertion();
        $token = $client->getAccessToken();

        $access_token = $token['access_token'];

        $headers = ["Authorization: Bearer $access_token", 'Content-Type: application/json'];

        $user = User::findorFail($user_id);
        $regid = trim($user->token);
        $data = [
            'message' => [
                'token' => $regid,
                'notification' => [
                    'title' => $title,
                    'body' => $description,
                ],
            ],
        ];

        $payload = json_encode($data);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/v1/projects/my-dian-istana/messages:send');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_VERBOSE, true);
        $response = curl_exec($ch);
        $err = curl_error($ch);
        curl_close($ch);

        if ($err) {
            return response()->json(
                [
                    'message' => 'Curl Error ' . $err,
                ],
                500,
            );
        } else {
            $this->make_notif($title, $message, '', $user_id);
            return response()->json([
                'message' => 'notification sent',
                'response' => json_decode($response, true),
            ]);
        }
    }

    public function make_notif($title, $message, $image, $user_id)
    {
        $data = new \App\Models\Notif();
        $data->title = $title;
        $data->slug = str_replace(' ', '-', $title);
        $data->message = $message;
        $data->image = $image;
        $data->admin_id = -1;
        $data->user_id = $user_id;
        $data->status = 0;
        $data->created_at = date('Y-m-d H:i:s');
        $data->updated_at = date('Y-m-d H:i:s');
        $data->save();
    }
}