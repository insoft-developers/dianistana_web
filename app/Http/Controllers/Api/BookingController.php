<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\AdminsData;
use App\Models\Transaction;
use App\Models\Setting;
use App\Models\UnitBisnis;
use App\Models\BookingSetting;
use App\Models\Tunggakan;
use DateTime;
use Illuminate\Support\Facades\Validator;

class BookingController extends Controller
{
    public function check_middle(Request $request)
    {
        $input = $request->all();
        $awal = $input['start'] + 1;
        $jumlah = strlen($awal);
        if ($jumlah == 1) {
            $st = '0' . $awal;
        } else {
            $st = $awal;
        }

        $dua = $input['start'] + 2;
        $jm = strlen($dua);
        if ($jm == 1) {
            $sw = '0' . $dua;
        } else {
            $sw = $dua;
        }

        $data = Transaction::where('booking_date', $input['booking_date'])
            ->where('business_unit_id', $input['unit_id'])
            ->where('quantity', 2)
            ->where('start_time', $awal)
            ->where('payment_status', '!=', 'CANCELLED')
            ->count();

        return response()->json([
            'success' => true,
            'data' => $data,
            'finish' => $sw,
        ]);
    }

    public function booking_list()
    {
        $data = UnitBisnis::where('status_booking', 'Aktif')->get();

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    public function booking_resume(Request $request)
    {
        $input = $request->all();

        // $cek_booking_awal = Transaction::where('booking_date', $input['booking_date'])
        //     ->where('start_time', '<', $input['finish_time'])
        //     ->where('finish_time', '>=', $input['finish_time'])
        //     ->get();

        // return response()->json([
        //     "success" => false,
        //     "message" => $cek_booking_awal->count()
        // ]);

        $timestamp = strtotime($input['booking_date']);
        $day = date('D', $timestamp);

        $bloking = 0;

        $un = UnitBisnis::findorFail($input['business_unit_id']);

        if ($un->kategori != 'Kolam Renang') {
            $rules = [
                'business_unit_id' => 'required',
                'invoice' => 'required',
                'start_time' => 'required',
                'finish_time' => 'required',
                'quantity' => 'required',
                'total_price' => 'required',
                'booking_date' => 'required',
            ];
        } else {
            if ($input['level'] == 'guest') {
                $rules = [
                    'business_unit_id' => 'required',
                    'invoice' => 'required',
                    'package_id' => 'required',
                    'package_name' => 'required',
                    'quantity' => 'required',
                    'total_price' => 'required',
                    'booking_date' => 'required',
                ];
            } else {
                $rules = [
                    'business_unit_id' => 'required',
                    'invoice' => 'required',
                    'start_time' => 'required',
                    'finish_time' => 'required',
                    'quantity' => 'required',
                    'total_price' => 'required',
                    'booking_date' => 'required',
                ];
            }
        }

        $validator = Validator::make($input, $rules);
        if ($validator->fails()) {
            $pesan = $validator->errors();
            $pesanarr = explode(',', $pesan);
            $find = ['[', ']', '{', '}'];
            $html = '';
            foreach ($pesanarr as $p) {
                $html .= str_replace($find, '', $p) . '<br>';
            }
            return response()->json([
                'success' => false,
                'message' => $html,
            ]);
        }

        $cek_invoice = Transaction::where('invoice', $input['invoice'])->get();
        if ($cek_invoice->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Booking with this invoice number has already exist!',
            ]);
        }

        $cek_date = BookingSetting::where('unit_id', $input['business_unit_id'])
            ->where('type', 1)
            ->where('date', $input['booking_date'])
            ->where('is_active', 1)
            ->get();

        if ($cek_date->count() > 0) {
            foreach ($cek_date as $cd) {
                if ($input['finish_time'] > $cd->start_time && $input['finish_time'] <= $cd->finish_time) {
                    $bloking++;
                }
            }
        }

        $cek_day = BookingSetting::where('unit_id', $input['business_unit_id'])
            ->where('type', 2)
            ->where('booking_day', $day)
            ->where('is_active', 1)
            ->get();

        if ($cek_day->count() > 0) {
            foreach ($cek_day as $cd) {
                if ($input['finish_time'] > $cd->start_time && $input['finish_time'] <= $cd->finish_time) {
                    $bloking++;
                }
            }
        }

        if ($bloking > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Booking at this time is disabled by the admin, please select another time.',
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'success',
        ]);
    }

    public function booking_invoice(Request $request)
    {
        $input = $request->all();
        $sett = Setting::findorFail(1);

        $tanggal_booking = $input['selected_date'];
        $bulan_sekarang = date('m');
        $bulan_booking = date('m', strtotime($tanggal_booking));
        // if ($bulan_booking != $bulan_sekarang) {
        //     return response()->json([
        //         'success' => false,
        //         'message' => 'Booking is only allowed for the current month...!',
        //     ]);
        // }

        $tanggal_1 = new DateTime();
        $tanggal_2 = new DateTime($input['selected_date']);
        $selisih = $tanggal_1->diff($tanggal_2);
        $sisa = $selisih->d;
        $mod = $sett->max_order_day;
        $modmin = $mod - 1;

        if ($sisa > $modmin) {
            return response()->json([
                'success' => false,
                'message' => 'Maximum booking is h+'.$mod.' from today',
            ]);
        }

        $random = random_int(1000, 9999);
        $invoice = $this->incrementalHash();

        $tanggal = $input['selected_date'];
        $timestamp = strtotime($tanggal);
        $day = date('D', $timestamp);

        $js['6'] = 0;
        $js['7'] = 0;
        $js['8'] = 0;
        $js['9'] = 0;
        $js['10'] = 0;
        $js['11'] = 0;
        $js['12'] = 0;
        $js['13'] = 0;
        $js['14'] = 0;
        $js['15'] = 0;
        $js['16'] = 0;
        $js['17'] = 0;
        $js['18'] = 0;
        $js['19'] = 0;
        $js['20'] = 0;

        $jam_tutup = [];

        $cr = BookingSetting::where('date', $tanggal)
            ->where('unit_id', $input['selected_unit'])
            ->where('is_active', 1)
            ->get();
        if ($cr->count() > 0) {
            foreach ($cr as $a) {
                $row['awal'] = $a->start_time;
                $row['akhir'] = $a->finish_time;
                array_push($jam_tutup, $row);
            }
        }

        $dr = BookingSetting::where('booking_day', $day)
            ->where('unit_id', $input['selected_unit'])
            ->where('is_active', 1)
            ->get();
        if ($dr->count() > 0) {
            foreach ($dr as $a) {
                $row['awal'] = $a->start_time;
                $row['akhir'] = $a->finish_time;
                array_push($jam_tutup, $row);
            }
        }

        if ($cr->count() > 0 || $dr->count() > 0) {
            foreach ($jam_tutup as $jt) {
                $pertama = (int) $jt['awal'];
                $ending = (int) $jt['akhir'];

                for ($q = $pertama; $q < $ending; $q++) {
                    $js[$q]++;
                }
            }
        }

        $not = Transaction::where('booking_date', $tanggal)
            ->where('order_status', 1)
            ->where('business_unit_id', $input['selected_unit'])
            ->where(function ($query) {
                $query->where('payment_status', 'PAID');
                $query->orWhere('payment_status', 'PENDING');
            })
            ->get();

        $jam6 = 0;
        $jam7 = 0;
        $jam8 = 0;
        $jam9 = 0;
        $jam10 = 0;
        $jam11 = 0;
        $jam12 = 0;
        $jam13 = 0;
        $jam14 = 0;
        $jam15 = 0;
        $jam16 = 0;
        $jam17 = 0;
        $jam18 = 0;
        $jam19 = 0;
        $jam20 = 0;

        foreach ($not as $key) {
            if ($key->quantity == 1) {
                if ($key->start_time == '06') {
                    $jam6++;
                } elseif ($key->start_time == '07') {
                    $jam7++;
                } elseif ($key->start_time == '08') {
                    $jam8++;
                } elseif ($key->start_time == '09') {
                    $jam9++;
                } elseif ($key->start_time == '10') {
                    $jam10++;
                } elseif ($key->start_time == '11') {
                    $jam11++;
                } elseif ($key->start_time == '12') {
                    $jam12++;
                } elseif ($key->start_time == '13') {
                    $jam13++;
                } elseif ($key->start_time == '14') {
                    $jam14++;
                } elseif ($key->start_time == '15') {
                    $jam15++;
                } elseif ($key->start_time == '16') {
                    $jam16++;
                } elseif ($key->start_time == '17') {
                    $jam17++;
                } elseif ($key->start_time == '18') {
                    $jam18++;
                } elseif ($key->start_time == '19') {
                    $jam19++;
                } elseif ($key->start_time == '20') {
                    $jam20++;
                }
            } elseif ($key->quantity == 2) {
                if ($key->start_time == '06') {
                    $jam6++;
                    $jam7++;
                } elseif ($key->start_time == '07') {
                    $jam7++;
                    $jam8++;
                } elseif ($key->start_time == '08') {
                    $jam8++;
                    $jam9++;
                } elseif ($key->start_time == '09') {
                    $jam9++;
                    $jam10++;
                } elseif ($key->start_time == '10') {
                    $jam10++;
                    $jam11++;
                } elseif ($key->start_time == '11') {
                    $jam11++;
                    $jam12++;
                } elseif ($key->start_time == '12') {
                    $jam12++;
                    $jam13++;
                } elseif ($key->start_time == '13') {
                    $jam13++;
                    $jam14++;
                } elseif ($key->start_time == '14') {
                    $jam14++;
                    $jam15++;
                } elseif ($key->start_time == '15') {
                    $jam15++;
                    $jam16++;
                } elseif ($key->start_time == '16') {
                    $jam16++;
                    $jam17++;
                } elseif ($key->start_time == '17') {
                    $jam17++;
                    $jam18++;
                } elseif ($key->start_time == '18') {
                    $jam18++;
                    $jam19++;
                } elseif ($key->start_time == '19') {
                    $jam19++;
                    $jam20++;
                }
            } else {
                if ($key->start_time == '06') {
                    $jam6++;
                    $jam7++;
                    $jam8++;
                } elseif ($key->start_time == '07') {
                    $jam7++;
                    $jam8++;
                    $jam9++;
                } elseif ($key->start_time == '08') {
                    $jam8++;
                    $jam9++;
                    $jam10++;
                } elseif ($key->start_time == '09') {
                    $jam9++;
                    $jam10++;
                    $jam11++;
                } elseif ($key->start_time == '10') {
                    $jam10++;
                    $jam11++;
                    $jam12++;
                } elseif ($key->start_time == '11') {
                    $jam11++;
                    $jam12++;
                    $jam13++;
                } elseif ($key->start_time == '12') {
                    $jam12++;
                    $jam13++;
                    $jam14++;
                } elseif ($key->start_time == '13') {
                    $jam13++;
                    $jam14++;
                    $jam15++;
                } elseif ($key->start_time == '14') {
                    $jam14++;
                    $jam15++;
                    $jam16++;
                } elseif ($key->start_time == '15') {
                    $jam15++;
                    $jam16++;
                    $jam17++;
                } elseif ($key->start_time == '16') {
                    $jam16++;
                    $jam17++;
                    $jam18++;
                } elseif ($key->start_time == '17') {
                    $jam17++;
                    $jam18++;
                    $jam19++;
                } elseif ($key->start_time == '18') {
                    $jam18++;
                    $jam19++;
                    $jam20++;
                } elseif ($key->start_time == '19') {
                    $jam19++;
                    $jam20++;
                }
            }
        }

        return response()->json([
            'success' => true,
            'data' => $invoice,
            'input' => $input,
            'js6' => $js['6'],
            'js7' => $js['7'],
            'js8' => $js['8'],
            'js9' => $js['9'],
            'js10' => $js['10'],
            'js11' => $js['11'],
            'js12' => $js['12'],
            'js13' => $js['13'],
            'js14' => $js['14'],
            'js15' => $js['15'],
            'js16' => $js['16'],
            'js17' => $js['17'],
            'js18' => $js['18'],
            'js19' => $js['19'],
            'js20' => $js['20'],
            'jam6' => $jam6,
            'jam7' => $jam7,
            'jam8' => $jam8,
            'jam9' => $jam9,
            'jam10' => $jam10,
            'jam11' => $jam11,
            'jam12' => $jam12,
            'jam13' => $jam13,
            'jam14' => $jam14,
            'jam15' => $jam15,
            'jam16' => $jam16,
            'jam17' => $jam17,
            'jam18' => $jam18,
            'jam19' => $jam19,
            'jam20' => $jam20,
        ]);
    }

    public function incrementalHash($len = 5)
    {
        $charset = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $base = strlen($charset);
        $result = '';

        $now = explode(' ', microtime())[1];
        while ($now >= $base) {
            $i = $now % $base;
            $result = $charset[$i] . $result;
            $now /= $base;
        }
        return substr($result, -5);
    }

    public function count_booking_price(Request $request)
    {
        $input = $request->all();

        // awal: 20, akhir: 21, quantity: 1, booking_date: 2024-04-30, level: user, unit_id: 3

        $unit = UnitBisnis::findorFail($input['unit_id']);
        $tanggal = $input['booking_date'];
        $timestamp = strtotime($tanggal);
        $day = date('D', $timestamp);

        if ($input['level'] == 'user') {
            if ($input['quantity'] == 1) {
                $intakhir = (int) $input['akhir'];
                if ($intakhir <= 17) {
                    $total_price = 0;
                } else {
                    if ($day == 'Sat' || $day == 'Sun') {
                        $total_price = $unit->harga_warga_1721_weekend;
                    } else {
                        $total_price = $unit->harga_warga_1721_weekday;
                    }
                }
            } elseif ($input['quantity'] == 2) {
                $intakhir = (int) $input['akhir'];
                if ($intakhir <= 17) {
                    $total_price = 0;
                } elseif ($intakhir == 18) {
                    if ($day == 'Sat' || $day == 'Sun') {
                        $total_price = $unit->harga_warga_1721_weekend;
                    } else {
                        $total_price = $unit->harga_warga_1721_weekday;
                    }
                } else {
                    if ($day == 'Sat' || $day == 'Sun') {
                        $total_price = 2 * $unit->harga_warga_1721_weekend;
                    } else {
                        $total_price = 2 * $unit->harga_warga_1721_weekday;
                    }
                }
            } elseif ($input['quantity'] == 3) {
                $intakhir = (int) $input['akhir'];
                if ($intakhir <= 17) {
                    $total_price = 0;
                } elseif ($intakhir == 18) {
                    if ($day == 'Sat' || $day == 'Sun') {
                        $total_price = $unit->harga_warga_1721_weekend;
                    } else {
                        $total_price = $unit->harga_warga_1721_weekday;
                    }
                } elseif ($intakhir == 19) {
                    if ($day == 'Sat' || $day == 'Sun') {
                        $total_price = 2 * $unit->harga_warga_1721_weekend;
                    } else {
                        $total_price = 2 * $unit->harga_warga_1721_weekday;
                    }
                } else {
                    if ($day == 'Sat' || $day == 'Sun') {
                        $total_price = 3 * $unit->harga_warga_1721_weekend;
                    } else {
                        $total_price = 3 * $unit->harga_warga_1721_weekday;
                    }
                }
            }
        } else {
            if ($input['quantity'] == 1) {
                $intakhir = (int) $input['akhir'];
                if ($intakhir <= 17) {
                    if ($day == 'Sat' || $day == 'Sun') {
                        $total_price = $unit->harga_umum_0617_weekend;
                    } else {
                        $total_price = $unit->harga_umum_0617_weekday;
                    }
                } else {
                    if ($day == 'Sat' || $day == 'Sun') {
                        $total_price = $unit->harga_umum_1721_weekend;
                    } else {
                        $total_price = $unit->harga_umum_1721_weekday;
                    }
                }
            } elseif ($input['quantity'] == 2) {
                $intakhir = (int) $input['akhir'];
                if ($intakhir <= 17) {
                    if ($day == 'Sat' || $day == 'Sun') {
                        $total_price = 2 * $unit->harga_umum_0617_weekend;
                    } else {
                        $total_price = 2 * $unit->harga_umum_0617_weekday;
                    }
                } elseif ($intakhir == 18) {
                    if ($day == 'Sat' || $day == 'Sun') {
                        $total_price = $unit->harga_umum_0617_weekend + $unit->harga_umum_1721_weekend;
                    } else {
                        $total_price = $unit->harga_umum_0617_weekday + $unit->harga_umum_1721_weekday;
                    }
                } else {
                    if ($day == 'Sat' || $day == 'Sun') {
                        $total_price = 2 * $unit->harga_umum_1721_weekend;
                    } else {
                        $total_price = 2 * $unit->harga_umum_1721_weekday;
                    }
                }
            } elseif ($input['quantity'] == 3) {
                $intakhir = (int) $input['akhir'];
                if ($intakhir <= 17) {
                    if ($day == 'Sat' || $day == 'Sun') {
                        $total_price = 3 * $unit->harga_umum_0617_weekend;
                    } else {
                        $total_price = 3 * $unit->harga_umum_0617_weekday;
                    }
                } elseif ($intakhir == 18) {
                    if ($day == 'Sat' || $day == 'Sun') {
                        $total_price = 2 * $unit->harga_umum_0617_weekend + $unit->harga_umum_1721_weekend;
                    } else {
                        $total_price = 2 * $unit->harga_umum_0617_weekday + $unit->harga_umum_1721_weekday;
                    }
                } elseif ($intakhir == 19) {
                    if ($day == 'Sat' || $day == 'Sun') {
                        $total_price = $unit->harga_umum_0617_weekend + 2 * $unit->harga_umum_1721_weekend;
                    } else {
                        $total_price = $unit->harga_umum_0617_weekday + 2 * $unit->harga_umum_1721_weekday;
                    }
                } else {
                    if ($day == 'Sat' || $day == 'Sun') {
                        $total_price = 3 * $unit->harga_umum_1721_weekend;
                    } else {
                        $total_price = 3 * $unit->harga_umum_1721_weekday;
                    }
                }
            }
        }

        return response()->json([
            'success' => true,
            'data' => $total_price,
        ]);
    }

    public function term()
    {
        $setting = Setting::findorFail(1);
        return response()->json([
            'success' => true,
            'data' => $setting,
        ]);
    }

    public function transaction(Request $request)
    {
        $input = $request->all();

        $un = UnitBisnis::findorFail($input['business_unit_id']);

        if ($un->kategori != 'Kolam Renang') {
            $rules = [
                'business_unit_id' => 'required',
                'invoice' => 'required',
                'start_time' => 'required',
                'finish_time' => 'required',
                'quantity' => 'required',
                'total_price' => 'required',
                'booking_date' => 'required',
            ];
        } else {
            if ($input['level'] == 'guest') {
                $rules = [
                    'business_unit_id' => 'required',
                    'invoice' => 'required',
                    'package_id' => 'required',
                    'package_name' => 'required',
                    'quantity' => 'required',
                    'total_price' => 'required',
                    'booking_date' => 'required',
                ];
            } else {
                $rules = [
                    'business_unit_id' => 'required',
                    'invoice' => 'required',
                    'start_time' => 'required',
                    'finish_time' => 'required',
                    'quantity' => 'required',
                    'total_price' => 'required',
                    'booking_date' => 'required',
                ];
            }
        }

        $validator = Validator::make($input, $rules);
        if ($validator->fails()) {
            $pesan = $validator->errors();
            $pesanarr = explode(',', $pesan);
            $find = ['[', ']', '{', '}'];
            $html = '';
            foreach ($pesanarr as $p) {
                $html .= str_replace($find, '', $p) . '<br>';
            }
            return response()->json([
                'success' => false,
                'message' => $html,
            ]);
        }

        $sekarang = date('Y-m-d');
        $hitung = strtotime('-1 day', strtotime($sekarang));
        $awal = date('Y-m-d', $hitung);
        
        $exist_transaction = Transaction::where('booking_date', $input['booking_date'])
            ->where('business_unit_id', $input['business_unit_id'])
            ->where('start_time', $input['start_time'])
            ->where('finish_time', $input['finish_time'])
            ->where('payment_status', '!=', 'CANCELLED');
            
            
        if($exist_transaction->count() > 0) {
            return response()->json([
               "success" => false,
               "start_time" => $input['start_time'],
               "message" => "Sorry this booking date and time has already taken..!"
            ]);
        }
        
        $pre_time = $input['start_time'] - 1;
        $pre_transaction = Transaction::where('booking_date', $input['booking_date'])
            ->where('business_unit_id', $input['business_unit_id'])
            ->where('start_time', $pre_time)
            ->where('quantity', 2)
            ->where('payment_status', '!=', 'CANCELLED');
        
        if($pre_transaction->count() > 0) {
            return response()->json([
               "success" => false,
               "start_time" => $pre_time,
               "message" => "Sorry this booking date and time has already taken..!"
            ]);
        }

        // $cek = Transaction::where('user_id', $input['user_id'])->where('payment_status', 'PAID')
        //     ->where('business_unit_id', $input['business_unit_id'])
        //     ->where('created_at', '>=', $awal.' 00:00:00')
        //     ->where('created_at', '<=', $sekarang.' 23:59:59')
        //     ->get();

        // if($cek->count() >= $un->max_user_order) {

        //     return response()->json([
        //     	"success" => false,
        //     	"message" => "Your booking quota for business units (".$un->name_unit.") is used up. Each user can book a maximum of ".$un->max_user_order." times per month",
        //     ]);
        // }

        $cek_order = Transaction::where('user_id', $input['user_id'])
            ->where('payment_status', '!=', 'CANCELLED')
            ->where('business_unit_id', $input['business_unit_id'])
            ->where('booking_date', $input['booking_date']);

        if ($cek_order->count() >= $un->max_user_order) {
            return response()->json([
                'success' => false,
                'message' => 'You cannot book the same business unit on the same date more than ' . $un->max_user_order . ' time(s)',
            ]);
        }

        try {
            if ($input['total_price'] == 0 || $input['total_price'] == '0') {
                $input['payment_status'] = 'PAID';
                $input['description'] = 'free for user';
                $input['paid_at'] = date('Y-m-d H:i:s');
                $input['payment_method'] = 'FREE HOUR';
                $input['payment_channel'] = 'FREE HOUR';
            } else {
                $input['payment_status'] = 'PENDING';
                $input['description'] = 'order';
            }

            if ($request->start_time == null) {
                $input['start_time'] = '00';
            }
            if ($request->finish_time == null) {
                $input['finish_time'] = '00';
            }
            if ($request->package_id == null) {
                $input['package_id'] = 0;
            }
            if ($request->package_name == null) {
                $input['package_name'] = 'no-package';
            }

            $input['order_status'] = 1;
            $data = Transaction::create($input);

            return response()->json([
                'success' => true,
                'message' => 'success',
                'id' => $data->id,
                'total_price' => $input['total_price'],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function payment_process(Request $request)
    {
        $input = $request->all();

        $trans = Transaction::where('payment_status', 'PENDING')->where('id', $input['id'])->first();
        $setting = Setting::findorFail(1);
        $amount = $trans->total_price;

        $user = User::findorFail($trans->user_id);
        $product = UnitBisnis::findorFail($trans->business_unit_id);

        $merchantCode = $setting->merchant_code; // dari duitku
        $merchantKey = $setting->api_payment; // dari duitku

        $timestamp = round(microtime(true) * 1000); //in milisecond
        $paymentAmount = $amount;
        $merchantOrderId = $trans->invoice; // dari merchant, unique
        $productDetails = 'Order atas nama : ' . $user->name . " \nuntuk fasilitas : " . $product->name_unit . " \nuntuk tanggal : " . $trans->booking_date . " \njam : " . $trans->start_time . ':00 WIB - ' . $trans->finish_time . ':00 WIB';
        $email = $user->email; // email pelanggan merchant
        $phoneNumber = $user->no_hp; // nomor tlp pelanggan merchant (opsional)
        $additionalParam = ''; // opsional
        $merchantUserInfo = ''; // opsional
        $customerVaName = $user->name; // menampilkan nama pelanggan pada tampilan konfirmasi bank
        $callbackUrl = $setting->callback_payment; // url untuk callback
        $returnUrl = url('/api/print_ticket/' . $input['id']);
        $expiryPeriod = 10; // untuk menentukan waktu kedaluarsa dalam menit
        $signature = hash('sha256', $merchantCode . $timestamp . $merchantKey);
        //$paymentMethod = 'VC'; //digunakan untuk direksional pembayaran

        $customerDetail = [
            'firstName' => $user->name,
            'lastName' => '',
            'email' => $user->email,
            'phoneNumber' => str_replace('+62', '', $user->no_hp),
        ];

        $item1 = [
            'name' => 'Order atas nama : ' . $user->name . ' <br>untuk fasilitas : ' . $product->name_unit . ' <br>untuk tanggal : ' . $trans->booking_date . ' <br>jam : ' . $trans->start_time . ':00 WIB - ' . $trans->finish_time . ':00 WIB',
            'price' => $amount,
            'quantity' => 1,
        ];

        $itemDetails = [$item1];

        $params = [
            'paymentAmount' => $paymentAmount,
            'merchantOrderId' => $merchantOrderId,
            'productDetails' => $productDetails,
            'additionalParam' => $additionalParam,
            'merchantUserInfo' => $merchantUserInfo,
            'customerVaName' => $customerVaName,
            'email' => $email,
            'phoneNumber' => $phoneNumber,
            // 'itemDetails' => $itemDetails,
            'customerDetail' => $customerDetail,
            //'creditCardDetail' => $creditCardDetail,
            'callbackUrl' => $callbackUrl,
            'returnUrl' => $returnUrl,
            'expiryPeriod' => $expiryPeriod,
            //'paymentMethod' => $paymentMethod
        ];

        $params_string = json_encode($params);

        $url = $setting->duitku_link . '/api/merchant/createinvoice'; // Sandbox
        // $url = 'https://api-prod.duitku.com/api/merchant/createinvoice'; // Production

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params_string);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json', 'Content-Length: ' . strlen($params_string), 'x-duitku-signature:' . $signature, 'x-duitku-timestamp:' . $timestamp, 'x-duitku-merchantcode:' . $merchantCode]);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $request = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        $response = json_decode($request);

        return response()->json([
            'success' => true,
            'data' => $response,
        ]);
    }

    public function print_ticket($id)
    {
        $tran = Transaction::where('id', (int) $id)->where('payment_status', 'PAID');
        if ($tran->count() <= 0) {
            return redirect('/api/mobile_redirect_booking');
        }
        $trans = $tran->first();
        $user = User::findorFail($trans->user_id);
        $product = UnitBisnis::findorFail($trans->business_unit_id);
        return view('mobile.ticket', compact('trans', 'user', 'product'));
    }

    public function booking_check(Request $request)
    {
        $input = $request->all();

        $sekarang = date('Y-m-d');
        $hitung = strtotime('-12 day', strtotime($sekarang));
        $awal = date('Y-m-d', $hitung);

        // $cek = Transaction::where('user_id', $input['user_id'])->where('payment_status', 'PAID')
        //     ->where('created_at', '>=', $awal.' 00:00:00')
        //     ->where('created_at', '<=', $sekarang.' 23:59:59')
        //     ->get();

        // if($cek->count() < 0) {
        //     // return Redirect::to('frontend_dashboard')->with('error', "You have booked more than 3 times within 30 days");
        //     return response()->json([
        //     	"success" => false,
        //     	"message" => "Your booking quota for business units is used up. Each user can book a maximum of 3 times per month",
        //     ]);
        // }

        $cek2 = \App\Models\Tunggakan::where('user_id', $input['user_id'])
            ->where('amount', '>', 0)
            ->get();

        if ($cek2->count() > 0) {
            // return Redirect::to('frontend_dashboard')->with('error', "You can not use booking feature for your outstanding payments. Please Pay The Bills First");
            return response()->json([
                'success' => false,
                'message' => 'You can not use booking feature for your outstanding payments. Please Pay The Bills First',
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'success',
        ]);
    }

    public function booking_finish_check(Request $request)
    {
        $input = $request->all();
        $transaction = Transaction::where('business_unit_id', $input['business_unit_id'])
            ->where('payment_status', '!=', 'CANCELLED')
            ->where('booking_date', $input['booking_date'])
            ->get();

        $jam7 = 0;
        $jam8 = 0;
        $jam9 = 0;
        $jam10 = 0;
        $jam11 = 0;
        $jam12 = 0;
        $jam13 = 0;
        $jam14 = 0;
        $jam15 = 0;
        $jam16 = 0;
        $jam17 = 0;
        $jam18 = 0;
        $jam19 = 0;
        $jam20 = 0;
        $jam21 = 0;

        foreach ($transaction as $t) {
            if ($t->quantity == 1) {
                if ($t->finish_time == '07') {
                    $jam7++;
                } elseif ($t->finish_time == '08') {
                    $jam8++;
                } elseif ($t->finish_time == '09') {
                    $jam9++;
                } elseif ($t->finish_time == '10') {
                    $jam10++;
                } elseif ($t->finish_time == '11') {
                    $jam11++;
                } elseif ($t->finish_time == '12') {
                    $jam12++;
                } elseif ($t->finish_time == '13') {
                    $jam13++;
                } elseif ($t->finish_time == '14') {
                    $jam14++;
                } elseif ($t->finish_time == '15') {
                    $jam15++;
                } elseif ($t->finish_time == '16') {
                    $jam16++;
                } elseif ($t->finish_time == '17') {
                    $jam17++;
                } elseif ($t->finish_time == '18') {
                    $jam18++;
                } elseif ($t->finish_time == '19') {
                    $jam19++;
                } elseif ($t->finish_time == '20') {
                    $jam20++;
                } elseif ($t->finish_time == '21') {
                    $jam21++;
                }
            } elseif ($t->quantity == 2) {
                if ($t->finish_time == '08') {
                    $jam7++;
                    $jam8++;
                } elseif ($t->finish_time == '09') {
                    $jam8++;
                    $jam9++;
                } elseif ($t->finish_time == '10') {
                    $jam9++;
                    $jam10++;
                } elseif ($t->finish_time == '11') {
                    $jam10++;
                    $jam11++;
                } elseif ($t->finish_time == '12') {
                    $jam11++;
                    $jam12++;
                } elseif ($t->finish_time == '13') {
                    $jam12++;
                    $jam13++;
                } elseif ($t->finish_time == '14') {
                    $jam13++;
                    $jam14++;
                } elseif ($t->finish_time == '15') {
                    $jam14++;
                    $jam15++;
                } elseif ($t->finish_time == '16') {
                    $jam15++;
                    $jam16++;
                } elseif ($t->finish_time == '17') {
                    $jam16++;
                    $jam17++;
                } elseif ($t->finish_time == '18') {
                    $jam17++;
                    $jam18++;
                } elseif ($t->finish_time == '19') {
                    $jam18++;
                    $jam19++;
                } elseif ($t->finish_time == '20') {
                    $jam19++;
                    $jam20++;
                } elseif ($t->finish_time == '21') {
                    $jam20++;
                    $jam21++;
                }
            }
        }

        $data = \App\Models\BookingSetting::where('unit_id', $input['business_unit_id'])
            ->where('is_active', 1)
            ->where('date', $input['booking_date'])
            ->where('type', 1)
            ->get();

        if ($data->count() > 0) {
            foreach ($data as $d) {
                $start = (int) $d->start_time;
                $finish = (int) $d->finish_time;
                for ($i = $start + 1; $i <= $finish; $i++) {
                    ${'jam' . $i}++;
                }
            }
        }

        $tanggal = $input['booking_date'];
        $timestamp = strtotime($tanggal);
        $day = date('D', $timestamp);

        $dataDay = \App\Models\BookingSetting::where('unit_id', $input['business_unit_id'])
            ->where('is_active', 1)
            ->where('booking_day', $day)
            ->where('type', 2)
            ->get();

        if ($dataDay->count() > 0) {
            foreach ($dataDay as $d) {
                $start = (int) $d->start_time;
                $finish = (int) $d->finish_time;
                for ($i = $start + 1; $i <= $finish; $i++) {
                    ${'jam' . $i}++;
                }
            }
        }

        return response()->json([
            'success' => true,
            'jam7' => $jam7,
            'jam8' => $jam8,
            'jam9' => $jam9,
            'jam10' => $jam10,
            'jam11' => $jam11,
            'jam12' => $jam12,
            'jam13' => $jam13,
            'jam14' => $jam14,
            'jam15' => $jam15,
            'jam16' => $jam16,
            'jam17' => $jam17,
            'jam18' => $jam18,
            'jam19' => $jam19,
            'jam20' => $jam20,
            'jam21' => $jam21,
            'data' => $day,
        ]);
    }

    public function redirect_booking()
    {
        return view('mobile.note2');
    }
}