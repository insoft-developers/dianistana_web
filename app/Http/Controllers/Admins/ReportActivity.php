<?php

namespace App\Http\Controllers\Admins;

use App\Http\Controllers\Controller;
use App\Models\AdminsData;
use App\Models\UserLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class ReportActivity extends Controller
{
    public function index() {
        $view = 'report-activity';  
        $admins = AdminsData::all();

        return view('admins.report.activity.index', compact('view','admins'));
    }

    public function ajax_list(Request $request)
    {
        $input = $request->all();
        $awal = $input['awal'];
        $akhir = $input['akhir'];
       
        if(! empty($awal) && ! empty($akhir)) {
            $query = UserLog::where('created_at', '>=', $awal)
                ->where('created_at', '<=', $akhir)
                ->orderBy('id','desc');

        } else {
            $query = UserLog::orderBy('id', 'desc');
        }

        if(! empty($input['admin_id'])) {
            $query->where('user_id', $input['admin_id']);
        }

        if(! empty($input['activity_type'])) {
            $query->where('action', strtoupper($input['activity_type']));
        }
       
        $data = $query->get();
        

        return DataTables::of($data)
        ->addColumn('user_id', function($data){
            $admins = AdminsData::where('id', $data->user_id);
            if($admins->count() > 0) {
                $admin = $admins->first()->username.' ( '. $admins->first()->name.' )';

            } else {
                $admin = 'not found';
            }

            return $admin;
            
        
        })
        ->addColumn('created_at', function($data){
            return date('d-m-Y H:i:s', strtotime($data->created_at));
        })
        ->addColumn('description', function($data){
            return '<div style="white-space:normal;">'.$data->description.'</div>';
        })
        ->rawColumns(['description','created_at','user_id'])
        ->addIndexColumn()
        ->make(true);
    }
}
