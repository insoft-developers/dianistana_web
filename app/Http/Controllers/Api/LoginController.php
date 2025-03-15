<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use DateTime;

class LoginController extends Controller
{
    protected function selisih_hari($tanggal)
    {
        $tanggal_1 = new DateTime();
        $tanggal_2 = new DateTime($tanggal);
        $selisih = $tanggal_1->diff($tanggal_2);
        return $selisih->y;
    }
       
    public function get_birthday(Request $request) {
        $input = $request->all();
        
        $user = User::findorFail($input['userid']);
        $birthday = date('m-d', strtotime($user->birthday));
        $now = date('m-d');
        
        if($birthday == $now) {
            return response()->json([
                "success" => true,
                "data" => $user,
                "umur" => $this->selisih_hari($user->birthday)
            ]);
        } else {
            return response()->json([
                "success" => false,
                "data" => $birthday,
                "data2" => $now
            ]);
        }
        
        
    }
    
    
    public function login(Request $request) {
        $input = $request->all();

        $rules = array(
            "username" =>"required",
            "password" => "required|min:6"

        );

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

        
        if(Auth::attempt(['username'=>$input['username'],'password'=>$input['password']])) {
        	$data = User::where('username', $input['username'])->first();
            
            $token = SHA1(date('Y-m-d H:i:s'));
           	return response()->json([
            	"success" => true,
            	"message" => "success",
            	"data" => $data,
            	"token" => $token
            ]);
        }
        
        // else if(Auth::attempt(['email'=>$input['username'],'password'=>$input['password']])) {
            
        //     $data = User::where('email', $input['username'])->first();
        //     return response()->json([
        //     	"success" => true,
        //     	"message" => "success",
        //     	"data" => $data
        //     ]);
        // }

        // else if(Auth::attempt(['no_hp'=>$input['username'],'password'=>$input['password']])) {
            
        //     $data = User::where('no_hp', $input['username'])->first();
        //     return response()->json([
        //     	"success" => true,
        //     	"message" => "success",
        //     	"data" => $data
        //     ]);


        // }
        
        
        else {
            return response()->json([
            	"success" => false,
            	"message" => "wrong username or password"

            ]);
        }
    }

    public function update_fcm_token(Request $request) {
        $input = $request->all();
        $data = User::findorFail($input['id']);
        $data->token = $input['token'];
        $data->save();
        return response()->json([
            "success" => true,
            "message" => "token saved ".$input['token']
        ]);
    }

}