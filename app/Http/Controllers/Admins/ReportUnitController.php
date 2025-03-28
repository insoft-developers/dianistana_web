<?php

namespace App\Http\Controllers\Admins;

use App\Helpers\Resp;
use Illuminate\View\View;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use App\Traits\DBcustom\DataTablesTraitStatic;
use DataTables;
use Validator;
use App\Models\Payment;
use App\Models\PaymentDetail;
use Illuminate\Validation\Rule;
use DB;
use PDF;
use App\Exports\LaporanDetailKasExport;
use App\Exports\LaporanKeuanganExport;
use App\Exports\LaporanUnitExport;
use App\Exports\AccountingUnit;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\Transaction;
use App\Models\UnitBisnis;


class ReportUnitController extends Controller
{
    use DataTablesTraitStatic;
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $view = "report-unit";
        $method = Transaction::where('payment_method', '!=', null)->groupBy('payment_method')->get();
        $unit = UnitBisnis::all();
        return view("admins.report.bisnis.index", compact('view','method','unit'));
    }


    public function ajax_list(Request $request)
    {
        $input = $request->all();
        $awal = $input['awal'];
        $akhir = $input['akhir'];
        $unit = $input['unit'];
        $ending = strtotime("+1 day", strtotime($akhir));
        $sampai = date('Y-m-d', $ending);
        if(empty($awal) && empty($akhir)) {
            $bln = date('m');
            $thn = date('Y');
            $tanggal_akhir = cal_days_in_month(CAL_GREGORIAN, $bln, $thn);
            $start = $thn.'-'.$bln.'-01';
            $end = $thn.'-'.$bln.'-'.$tanggal_akhir;
            $query = DB::table('transactions')
                                ->select('transactions.*', 'unit_bisnis.name_unit')
                                ->join('unit_bisnis', 'unit_bisnis.id', '=', 'transactions.business_unit_id', 'left')
                                ->where('transactions.payment_status', 'PAID')
                                ->where('transactions.paid_at', '>=', $start)
                                ->where('transactions.paid_at', '<=', $end);
                                
        } else {
            $query = DB::table('transactions')
                                ->select('transactions.*', 'unit_bisnis.name_unit')
                                ->join('unit_bisnis', 'unit_bisnis.id', '=', 'transactions.business_unit_id', 'left')
                                ->where('transactions.payment_status', 'PAID')
                                ->where('transactions.paid_at', '>=', $awal)
                                ->where('transactions.paid_at', '<=', $akhir);
                                
        }

        if(! empty($input['payment'])) {
            $query->where('payment_method', $input['payment']);
        }
        if(! empty($input['unit'])) {
            $query->where('business_unit_id', $input['unit']);
        }

        $data = $query->get();


        return Datatables::of($data)
            ->addColumn('created_at', function($data){
                return '<center>'.date('d-m-Y', strtotime($data->created_at)).'</center>';
            })
            ->addColumn('booking_date', function($data){
                return '<center>'.date('d-m-Y', strtotime($data->booking_date)).'</center>';
            })
           
            ->addColumn('paid_at', function($data){
                return '<center>'.date('d-m-Y', strtotime($data->paid_at)).'</center>';
            })
            
            ->addColumn('user_id', function($data){
                $users = \App\Models\User::where('id', $data->user_id);
                if($users->count() > 0) {
                    $user = $users->first();
                    return $user->name.'<br>[ '.$user->blok.' - '.$user->nomor_rumah.' ]';
                } else {
                    return 'no-data';
                }
            })
            ->addColumn('payment_method', function($data){
                if($data->payment_method == 'BC') {
                    $payment = 'BCA';
                } 
                else if($data->payment_method == 'BR') {
                    $payment = 'BRI';
                }
                else if($data->payment_method == 'BT') {
                    $payment = 'BTN';
                }
                else if($data->payment_method == 'DA') {
                    $payment = 'DANA';
                }
                else {
                    $payment = 'OTHERS';
                }
                return $payment;
            })
            ->addColumn('booking_time', function($data){
                return '<center>'.$data->start_time.'-'.$data->finish_time.'</center>';
            })
            ->addColumn('total_price', function($data){
                return '<div style="text-align:right;">'.number_format($data->total_price).'</div>';
            })
            ->addColumn('action', function($data){
                return '<a href="javascript:void(0);" class="bs-tooltip text-success mb-2" data-bs-toggle="tooltip" data-bs-placement="top" data-original-title="Detail" aria-label="Edit" data-bs-original-title="Detail" title="Detail" onclick="printData('.$data->id.')"><i class="far fa-file"></i></a>';
        })->rawColumns(['action','created_at','user_id','paid_at','total_price','booking_time','booking_date','payment_method'])
        ->addIndexColumn()
        ->make(true);
    }

    
    public function print_unit_report($awal, $akhir, $payment, $unit) {
        
        $ending = strtotime("+1 day", strtotime($akhir));
        $sampai = date('Y-m-d', $ending);
        if(empty($awal) && empty($akhir)) {
            $bln = date('m');
            $thn = date('Y');
            $start = $thn.'-'.$bln.'-01';
            $tanggal_akhir = cal_days_in_month(CAL_GREGORIAN, $bln, $thn);
            $end = $thn.'-'.$bln.'-'.$tanggal_akhir;
            $query = DB::table('transactions')
                                ->select('transactions.*', 'unit_bisnis.name_unit', 'unit_bisnis.kepemilikan')
                                ->join('unit_bisnis', 'unit_bisnis.id', '=', 'transactions.business_unit_id', 'left')
                                ->where('transactions.payment_status', 'PAID')
                                ->where('transactions.paid_at', '>=', $start)
                                ->where('transactions.paid_at', '<=', $end);
        } else {
            $query = DB::table('transactions')
                                ->select('transactions.*', 'unit_bisnis.name_unit', 'unit_bisnis.kepemilikan')
                                ->join('unit_bisnis', 'unit_bisnis.id', '=', 'transactions.business_unit_id', 'left')
                                ->where('transactions.payment_status', 'PAID')
                                ->where('transactions.paid_at', '>=', $awal)
                                ->where('transactions.paid_at', '<=', $akhir);
        }

        if($payment != 0) {
            $query->where('transactions.payment_method', $payment);
        }

        $units = "";

        if($unit != 0) {
            $query->where('transactions.business_unit_id', $unit);
            $unit_query = UnitBisnis::findorFail($unit);
            $units = $unit_query->name_unit;
        }
        $query->orderBY('transactions.updated_at', 'asc');
        $data = $query->get();
        $setting = \App\Models\Setting::findorFail(1);

       

        return view('admins.report.bisnis.print', compact('data','awal','akhir','setting','awal','akhir','payment','unit','units'));
    }


    public function print_unit_report_pdf($awal, $akhir, $payment, $unit) {
        $ending = strtotime("+1 day", strtotime($akhir));
        $sampai = date('Y-m-d', $ending);
        if(empty($awal) && empty($akhir)) {
            $bln = date('m');
            $thn = date('Y');
            $start = $thn.'-'.$bln.'-01';
            $tanggal_akhir = cal_days_in_month(CAL_GREGORIAN, $bln, $thn);
            $end = $thn.'-'.$bln.'-'.$tanggal_akhir;
            $query = DB::table('transactions')
                                ->select('transactions.*', 'unit_bisnis.name_unit', 'unit_bisnis.kepemilikan')
                                ->join('unit_bisnis', 'unit_bisnis.id', '=', 'transactions.business_unit_id', 'left')
                                ->where('transactions.payment_status', 'PAID')
                                ->where('transactions.paid_at', '>=', $start)
                                ->where('transactions.paid_at', '<=', $end)
                                ->orderBy('transactions.paid_at', 'asc');
        } else {
            $query = DB::table('transactions')
                                ->select('transactions.*', 'unit_bisnis.name_unit', 'unit_bisnis.kepemilikan')
                                ->join('unit_bisnis', 'unit_bisnis.id', '=', 'transactions.business_unit_id', 'left')
                                ->where('transactions.payment_status', 'PAID')
                                ->where('transactions.paid_at', '>=', $awal)
                                ->where('transactions.paid_at', '<=', $akhir)
                                ->orderBy('transactions.paid_at', 'asc');
        }

        if($payment != 0) {
            $query->where('transactions.payment_method', $payment);
        }


        $nameunit = "";
        if($unit != 0) {
            $query->where('transactions.business_unit_id', $unit);
            $q = UnitBisnis::findorFail($unit);
            $nameunit = $q->name_unit;
        }
        
         $query->orderBY('transactions.updated_at', 'asc');

        $data = $query->get();
        $setting = \App\Models\Setting::findorFail(1);
        $pdf= PDF::loadView('admins.report.bisnis.print_pdf', compact('data','awal','akhir','setting','nameunit'));
        $pdf->setPaper('a4', 'potrait');
        return $pdf->stream();
        // return view('admins.report.iuran.print_pdf', compact('data','awal','akhir','setting'));
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
        
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
       
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
       
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
       
    }

    
    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
       
    }


    public function print_unit_report_excel($awal, $akhir, $payment, $unit) 
    {
         return Excel::download(new LaporanUnitExport($awal, $akhir, $payment, $unit), 'laporan_kas_masuk_bisnis_unit.xlsx');
    }

    public function print_unit_accounting($awal, $akhir, $payment, $unit) 
    {
         return Excel::download(new AccountingUnit($awal, $akhir, $payment, $unit), 'dianistana_export_unit_to_accounting.csv');
    }

    
   
}