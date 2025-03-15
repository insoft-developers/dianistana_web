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

use App\Models\Broadcasting;
use App\Traits\UserLogTrait;

use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Google\Client as GoogleClient;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;

class BroadcastingController extends Controller
{
    use DataTablesTraitStatic;
    use UserLogTrait;    
   
    
    
    public function index(): View
    {
        $view = "broadcasting";
        $user = User::all();
        $bloks = \App\Models\Blok::all();
        return view("admins.broadcasting.index", compact('view','user','bloks'));
    }


    public function ajax_list()
    {
        $data = Broadcasting::all();
        return DataTables::of($data)
            ->addColumn('created_at', function($data){
                return date('d-m-Y', strtotime($data->created_at));
            })
            ->addColumn('send_date', function($data){
                return date('d-m-Y', strtotime($data->send_date)).' '.$data->send_time;
            })
            ->addColumn('message', function($data){
                return '<div style="white-space:normal;width:200px;"><a href="#">'.substr($data->message, 0, 80).'...</a></div>';
            })
            ->addColumn('title', function($data){
                return '<div style="white-space:normal;width:100px;">'.$data->title.'</div>';
            })
            ->addColumn('sending_status', function($data){
                if($data->sending_status == 1) {
                    return '<center><i class="fa fa-check-circle text-success"></i> Sent</center>';
                } else {
                    return '<center><i class="fa fa-exclamation-circle text-warning"></i> Waiting..</center>';
                }
            })
            ->addColumn('image', function($data){
                if($data->image == null || $data->image == '') {
                    return '';
                } else {
                    return '<a href="'.asset('template/images/notif/'.$data->image).'" target="_blank"><img class="img-list-data" src="'.asset('template/images/notif/'.$data->image).'"></a>';
                }
            })
            ->addColumn('admin_id', function($data){
                $users = \App\Models\AdminsData::where('id', $data->admin_id);
                if($users->count() > 0) {
                    $user = $users->first();
                    return $user->name;
                } else {
                    return '';
                }

            })
            ->addColumn('user_id', function($data){
                if($data->is_blok == 1) {
                    $bloks = \App\Models\Blok::where('id', $data->user_id);
                    if($bloks->count() > 0) {
                        $blok = $bloks->first();
                        return 'BLOK - '.$blok->blok_name;
                    } else {
                        return '-';
                    }
                } else {
                    if($data->user_id == -1) {
                        return 'All User';
                    } 
                    else if($data->user_id == -3) {
                        $persons = $data->person;
                        $person_array = explode(",", $persons);
                        $html = '';
                        $html .= '<ul>';
                        foreach($person_array as $p) {
                            $user = \App\Models\User::where('id', $p)->first();
                            $html .= '<li>'.$user->name.'</li>';
                        }
                        $html .= '</ul>';
                        return $html;
                    }
                    else {
                        $users = \App\Models\User::where('id', $data->user_id);
                        if($users->count() > 0) {
                            $user = $users->first();
                            return $user->name;
                        } else {
                            return '';
                        }
                    }
                    

                }
                
            })
            ->addColumn('action', function($data){
                if(adminAuth()->level == 'admin') {
                    return '<a href="javascript:void(0);" class="bs-tooltip text-warning mb-2" data-bs-toggle="tooltip" data-bs-placement="top" data-original-title="Edit" aria-label="Edit" data-bs-original-title="Edit" title="Edit" onclick="editData('.$data->id.')"><i class="far fa-edit"></i></a>';
                } else {
                    return '<a href="javascript:void(0);" class="bs-tooltip text-warning mb-2" data-bs-toggle="tooltip" data-bs-placement="top" data-original-title="Edit" aria-label="Edit" data-bs-original-title="Edit" title="Edit" onclick="editData('.$data->id.')"><i class="far fa-edit"></i></a>&nbsp;<a href="javascript:void(0);" class="bs-tooltip text-danger mb-2" data-bs-toggle="tooltip" data-bs-placement="top" data-original-title="Hapus" aria-label="Hapus" data-bs-original-title="Hapus" title="Hapus" onclick="deleteData('.$data->id.')"><i class="far fa-times-circle"></i></i></a>';
                }
                
        })->rawColumns(['action','created_at','message','title','image','admin_id','user_id','sending_status'])
        ->addIndexColumn()
        ->make(true);
    }

    public function ajax_list_trash(Request $request)
    {
        return self::set_ajax_list($request, true);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        abort(404);
    }

  
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $input = $request->all();
        
        
        $rules = array(
            "title" => "required",
            "message" => "required",
            "user_id" => "required",
        );

        if($input['sending_priority'] == '2') {
            $rules['send_date'] = "required";
            $rules['send_time'] = "required";
        }


        if($input['user_id'] == -2) {
            $rules['blok'] = "required";
        }
        
        if($input['user_id'] == -3) {
            $rules['person'] = "required";
        }
        

        $validator = Validator::make($input, $rules);
        if($validator->fails()) {
            $pesan = $validator->errors();
            $pesanarr = explode(",", $pesan);
            $find = array("[","]","{","}");
            $html = '';
            foreach($pesanarr as $p ) {
                $html .= str_replace($find,"",$p).'<br>';
            }
            
            return response()->json([
                "success" => false,
                "message" => $html
            ]);
        }
        try {
            $input['image'] = null;
            $unik = uniqid();
            if($request->hasFile('image')){
                $input['image'] = Str::slug($unik, '-').'.'.$request->image->getClientOriginalExtension();
                $request->image->move(public_path('/template/images/notif'), $input['image']);
            }
            $input['admin_id'] = adminAuth()->id;
            $input['sending_status'] = 0;
            $input['is_blok'] = 0;
            $input['person'] = "";
            if($input['user_id'] == -2) {
                $input['user_id'] = $input['blok'];
                $input['is_blok'] = 1;
            }
            
            if($input['user_id'] == -3) {
                $input['user_id'] = -3;
                $input['is_blok'] = 0;
                $person_arr = $request->person;
                $person = implode(",", $person_arr);
                $input['person'] = $person;
            }

            if($input['sending_priority'] == '1') {
                $input['send_date'] = date('Y-m-d');
                $input['send_time'] = date("H:i:s");
            }

            $br = Broadcasting::create($input);
            $id = $br->id;

            $this->insert_log(adminAuth()->id, "broadcast (new broadcast - ".$input['title'].")", "add");
            $sekarang = date('Y-m-d');
            if($input['sending_priority'] == '1') {

                if($input['is_blok'] == 1) {
                    $blok = \App\Models\Blok::findorFail($input['blok']);
                    $users = \App\Models\User::where('blok', $blok->blok_name)->get();
                    if($users->count() > 0) {
                        foreach($users as $user) {
                            if($user->token != null) {
                                $this->notify($input['title'], $input['message'], $user->id, $id);
                            }
                            $this->make_notif($input['title'], $input['message'], $input['image'], $user->id, $id);
                        }
                    }
                } else {
                    if($request->user_id == -3) {
                        $person_array = $request->person;
                        foreach($person_array as $p) {
                            $this->notify($input['title'], $input['message'], (int)$p, $id);
                            $this->make_notif($input['title'], $input['message'], $input['image'], (int)$p, $id);
                        }
                    } else {
                        $this->notify($input['title'], $input['message'], $input['user_id'], $id);
                        $this->make_notif($input['title'], $input['message'], $input['image'], $input['user_id'], $id);
                    }
                    
                    
                }

                
            }

            return response()->json([
                "success" => true,
                "message" => "New Data Successfully Added.."
            ]);
        }catch(\Exception $e) {
            return response()->json([
                "success" => false,
                "message" => $e->getMessage()
            ]);
        }
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
        $query = Broadcasting::findorFail($id);
        $persons = explode(",", $query->person);
        $rows = [];
        foreach($persons as $p) {
            array_push($rows, (int)$p);
        } 
        
        $data['data'] = $query;
        $data['person'] = $rows;
        return $data;
    }

    
    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $input = $request->all();

        $data = Broadcasting::findorFail($id);

        $rules = array(
            "title" => "required",
            "message" => "required",
            "user_id" => "required",
            
        );

        if($input['sending_priority'] == '2') {
            $rules['send_date'] = "required";
            $rules['send_time'] = "required";
        }

        if($input['user_id'] == -2) {
            $rules['blok'] = "required";
        }
        
        if($input['user_id'] == -3) {
            $rules['person'] = "required";
        }

        $validator = Validator::make($input, $rules);
        if($validator->fails()) {
            $pesan = $validator->errors();
            $pesanarr = explode(",", $pesan);
            $find = array("[","]","{","}");
            $html = '';
            foreach($pesanarr as $p ) {
                $html .= str_replace($find,"",$p).'<br>';
            }
            
            return response()->json([
                "success" => false,
                "message" => $html
            ]);
        }
        try {
            $input['image'] = $data->image;
            $unik = uniqid();
            if($request->hasFile('image')){
                if(! empty($data->image)) {
                    $path = public_path('/template/images/notif/'.$data->image);
                    if(file_exists($path)) {
                        unlink($path);
                    }
                }
                $input['image'] = Str::slug($unik, '-').'.'.$request->image->getClientOriginalExtension();
                $request->image->move(public_path('/template/images/notif'), $input['image']);
            }
            $input['admin_id'] = adminAuth()->id;
            $input['sending_status'] = 0;
            $input['is_blok'] = 0;
            
            if($input['user_id'] == -2) {
                $input['user_id'] = $input['blok'];
                $input['is_blok'] = 1;
            }
            
            if($input['user_id'] == -3) {
                $input['user_id'] = -3;
                $input['is_blok'] = 0;
                $person_arr = $request->person;
                $person = implode(",", $person_arr);
                $input['person'] = $person;
            }
            
            

            if($input['sending_priority'] == '1') {
                $input['send_date'] = date('Y-m-d');
                $input['send_time'] = date("H:i:s");
            }

            $br = $data->update($input);
            $this->insert_log(adminAuth()->id, "broadcast (update broadcast - ".$input['title'].")", "update");
            $sekarang = date('Y-m-d');
            if($input['sending_priority'] == '1') {
                if($input['is_blok'] == 1) {
                    $blok = \App\Models\Blok::findorFail($input['blok']);
                    $users = \App\Models\User::where('blok', $blok->blok_name)->get();
                    if($users->count() > 0) {
                        foreach($users as $user) {
                            if($user->token != null) {
                                $this->notify($input['title'], $input['message'], $user->id, $id);
                            }
                            $this->make_notif($input['title'], $input['message'], $input['image'], $user->id, $id);
                        }
                    }
                } else {
                    
                    if($request->user_id == -3) {
                        $person_array = $request->person;
                        foreach($person_array as $p) {
                            $this->notify($input['title'], $input['message'], (int)$p, $id);
                            $this->make_notif($input['title'], $input['message'], $input['image'], (int)$p, $id);
                        }
                    } else {
                        $this->notify($input['title'], $input['message'], $input['user_id'], $id);
                        $this->make_notif($input['title'], $input['message'], $input['image'], $input['user_id'], $id);
                    }
                }
            }

            return response()->json([
                "success" => true,
                "message" => "Data Successfully Updated.."
            ]);
        }catch(\Exception $e) {
            return response()->json([
                "success" => false,
                "message" => $e->getMessage()
            ]);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        
        $broadcast = Broadcasting::find($id);
        $query = Broadcasting::destroy($id);
        $this->insert_log(adminAuth()->id, "broadcast (delete broadcast - ".$broadcast->title.")", "delete");
        $del = \App\Models\Notif::where('broadcast_id', $id)->delete();
        return $query;
    }

    public function check_broadcasting() {
        $sent = 0;
        $sekarang = date('Y-m-d');
        $time = date('H:i:s');
        $cek = Broadcasting::where('sending_status', 0)
            ->where('send_date', $sekarang)
            ->where('send_time',  '<=', $time)
            ->get();
        if($cek->count() > 0) {
            foreach($cek as $key) {
                if($key->is_blok == 1) {
                    $blok = \App\Models\Blok::findorFail($key->user_id);
                    $users = \App\Models\User::where('blok', $blok->blok_name)->get();
                    if($users->count() > 0) {
                        foreach($users as $user) {
                            if($user->token != null) {
                                $this->notify($key->title, $key->message, $user->id, $key->id);
                            }
                        }
                    }
                } else {
                    if($key->user_id == -3) {
                        $person_array = explode(",", $key->person);
                        foreach($person_array as $p) {
                            $this->notify($key->title, $key->message, (int)$p, $key->id);
                        }
                    } else {
                        $this->notify($key->title, $key->message, $key->user_id, $key->id);
                    }
                    
                }
                $sent++;
            }
        }
        
        return $sent;
    }



    public function make_notif($title, $message, $image, $user_id, $bid) {
        $data = new \App\Models\Notif;
        $data->title = $title;
        $data->slug = str_replace(" ","-", $title);
        $data->message = $message;
        $data->image = $image;
        $data->admin_id = adminAuth()->id;
        $data->user_id = $user_id;
        $data->status = 0;
        $data->created_at = date('Y-m-d H:i:s');
        $data->updated_at = date('Y-m-d H:i:s');
        $data->broadcast_id = $bid;
        $data->save();
    }


    public function notify($title, $message, $user_id, $id) {
        
      
        
           $title = $title;
           $description = $message;
           
           $credentialsFilePath = "json/file.json";
           
           $client = new GoogleClient();
           $client->setAuthConfig($credentialsFilePath);
           $client->addScope('https://www.googleapis.com/auth/firebase.messaging');
           $client->refreshTokenWithAssertion();
           $token = $client->getAccessToken();
           
           $access_token = $token['access_token'];
           
           $headers = [
                "Authorization: Bearer $access_token",
                'Content-Type: application/json'
           ];
           
           if($user_id == -1) {
                $data = [
                    "message" => [
                        // "token" => $fcm,
                        "topic" => "dianistana_user",
                        "notification" => [
                            "title" => $title,    
                            "body" => $description
                        ],
                        
                    ]    
               ];
            } else {
                $user = User::findorFail($user_id);
                $regid = trim($user->token);
                $data = [
                    "message" => [
                        "token" => $regid,
                        "notification" => [
                            "title" => $title,    
                            "body" => $description
                        ],
                        
                    ]    
                ];
            }
           
           
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
           
           if($err) {
               return response()->json([
                  "message" => 'Curl Error '.$err 
               ], 500);
           } else {
                $br = Broadcasting::findorFail($id);
                $br->sending_status = 1;
                $br->save();
               return response()->json([
                  "message" => 'notification sent',
                  "response" => json_decode($response, true)
               ]);
           }
           
        
    }

   
}