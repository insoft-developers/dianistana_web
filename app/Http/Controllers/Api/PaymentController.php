<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Payment;
use App\Models\PaymentDetail;
use App\Models\User;
use App\Models\AdminsData;
use App\Models\Tunggakan;
use App\Models\Setting;


class PaymentController extends Controller
{
    public function payment_list($id) {
    	$setting = Setting::findorFail(1);
    	$user = User::findorFail($id);
    	$query = Payment::where('payment_dedication', $id)
    		->orwhere('payment_dedication', -1)
    		->orderBy('id','desc')
    		->get();

    	$data = [];
    	$total_outstanding = 0;
    	foreach($query as $key) {
    	    
    	    $ct = Tunggakan::where('user_id', $id)->where('payment_id', $key->id)->where('amount', '>', 0);
    	    if($ct->count() > 0 && $key->payment_type == 1) {
    	        
    	    } else {
        	   	$row['id'] = $key->id;
        		$row['payment_name'] = $key->payment_name;
        		$row['payment_desc'] = $key->payment_desc;
        		$row['payment_type'] = $key->payment_type;
        		$row['due_date'] = date('d F Y', strtotime($key->due_date));
        		$row['periode'] = $key->periode;
        		$row['payment_dedication'] = $key->payment_dedication;
        		$row['created_at'] = $key->created_at;
        		$row['updated_at'] = $key->updated_at;
    
        		if($key->payment_dedication == -1) {
        			$row['bill_to'] = "Bill To All";
        		} else {
        			$row['bill_to'] = "Bill To ".$user->name;
        		}
    
        		if($key->payment_type == 1) {
        			$row['type'] = "Iuran Bulanan Komplek";
        		} else if($key->payment_type == 2) {
        			$row['type'] = "Pembayaran Rutin";
        		} else {
        			$row['type'] = "Sekali Bayar";
        		}
    
        		if($key->payment_type == 1) {
        			
        			$tunggakan = Tunggakan::where('user_id', $id)->where('payment_id', '>', 0)->sum('amount');
        			$adjust = Tunggakan::where('user_id', $id)->where('payment_id', -1)->sum('amount');
    
        			if($tunggakan > 0) {
        				$detail = \App\Models\PaymentDetail::where('payment_id', $key->id)
                            ->where('user_id', $id)
                            ->where('payment_status','PAID');
                        if($detail->count() > 0 ) {
                            $detail_data = $detail->first();
                            $row['payment_amount'] = $detail_data->amount;
                        } else{
    	                    $percent = $setting->percent_denda;
    	    				$denda = $percent * $tunggakan / 100;
    	    				$total_denda = $this->pembulatan((int)$denda) + $tunggakan;
    	    				$row['payment_amount'] = $adjust + $total_denda + $user->iuran_bulanan;
                        }
        				
        			} else {
        				$detail = \App\Models\PaymentDetail::where('payment_id', $key->id)
                            ->where('user_id', $id)
                            ->where('payment_status','PAID');
                        if($detail->count() > 0 ) {
                            $detail_data = $detail->first();
                            $row['payment_amount'] = $detail_data->amount;
                        } else {
                        	$row['payment_amount'] = $user->iuran_bulanan + $adjust;
                        }
        				
        			}
        		} else {
        			$row['payment_amount'] = $key->payment_amount;
        		}
    
        		$detail = PaymentDetail::where('payment_id', $key->id)
        				->where('user_id', $id)->where('payment_status', 'PAID')->get();
        		if($detail->count() > 0) {
        			$row['status'] = "PAID";
        		} else {
        			$row['status'] = "PENDING";
        		}
    
        		if($detail->count() <= 0) {
    
        			$tunggakan = Tunggakan::where('user_id', $id)->where('payment_id', $key->id)->where('amount', '>', 0)->get();
        			if($tunggakan->count() > 0 ) {
    
        			} else {
        				$total_outstanding = $total_outstanding + $row['payment_amount'];
        			}
        			
        		}
        		
        		array_push($data, $row);
    	    }
    	    
    	
    	}

    	return response()->json([
    		"success" =>true,
    		"data" => $data,
    		"total" => $total_outstanding
    	]);
    }

    public function payment_post(Request $request) {
        
    	$input = $request->all();
    	$setting = \App\Models\Setting::findorFail(1);

    	$user = User::findorFail($input['user_id']);

        if($user->level != "user" ) {
            return response()->json([
            	"success" => false,
            	"message" => "You are not allowed to access this feature...!"
            ]);
        }
        
        $payment = Payment::findorFail($input['id']);

        if($payment->status !== null) {
            return response()->json([
            	"success" => false,
            	"message" => "This bill has already expired...!"
            ]);
        }

        $random = random_int(1000, 9999);
        $invoice = "PM-".date('dmyHis').$random;

        if($payment->payment_type == 1) {
            $setting= \App\Models\Setting::findorFail(1);
            $denda = $setting->percent_denda;
            $sekarang = date('Y-m-d');
            
            $due = $payment->due_date;
            $iuran = $user->iuran_bulanan;
            if($sekarang > $due) {
                return response()->json([
                    "success" => false,
                    "message" => "Payment of bills is due by the 20th of each month. Your current bill is already past due and therefore cannot be paid at this time. A penalty will be incurred and added to your next bill"
                ]);

            } else {
                $tunggakan = \App\Models\Tunggakan::where('user_id', $input['user_id'])
                            ->where('amount', '!=', 0)->where('payment_id', '>', 0);
                $adjust = \App\Models\Tunggakan::where('user_id', $input['user_id'])->where('payment_id', -1)->sum('amount');
                if($tunggakan->count() > 0) {
                    $jumlah_tunggakan = $tunggakan->sum('amount');
                    $nomi = $denda * $jumlah_tunggakan /100;
                    $nom_denda = $this->pembulatan((int)$nomi);
                    
                    $total_tunggakan = $nom_denda + $jumlah_tunggakan;
                    $amount = $iuran + $total_tunggakan + $adjust;
                    $text_denda = "Iuran Bulan ini :".number_format($iuran).'\nTunggakan : '.number_format($jumlah_tunggakan).'\nDenda Tunggakan : '.number_format($nom_denda).'\n Total Tunggakan : '.number_format($total_tunggakan).'\nAdjustment : '.number_format($adjust);
                } else {
                    $amount = $iuran + $adjust;
                    $text_denda = "";
                }
                
            }         

        } else {
            $sekarang = date('Y-m-d');
            $due = $payment->due_date;
            
            if($sekarang > $due) {
                return response()->json([
                    "success" => false,
                    "message" => "Payment of bills is due on ".date('d-m-Y', strtotime($due)).". Your current bill is already past due and therefore cannot be paid at this time. A penalty will be incurred and added to your next bill"
                ]);

            }
            
            $amount = $payment->payment_amount;
            $text_denda = "";
        }

        
        $detail = new PaymentDetail;
        $detail->invoice = $invoice;
        $detail->payment_id = $input['id'];
        $detail->user_id = $input['user_id'];
        $detail->amount = $amount;
        $detail->payment_status = "PENDING";
        $detail->created_at = date('Y-m-d H:i:s');
        $detail->updated_at = date('Y-m-d H:i:s');
        $detail->save();
        
        

        if($detail) {
            
            
            $merchantCode = $setting->merchant_code; // dari duitku
            $merchantKey = $setting->api_payment; // dari duitku

            $timestamp = round(microtime(true) * 1000); //in milisecond
            $paymentAmount = $amount;
            $merchantOrderId = $invoice; // dari merchant, unique
            $productDetails = $payment->payment_name;
            $email = $user->email; // email pelanggan merchant
            $phoneNumber = $user->no_hp; // nomor tlp pelanggan merchant (opsional)
            $additionalParam = ''; // opsional
            $merchantUserInfo = ''; // opsional
            $customerVaName = $user->name; // menampilkan nama pelanggan pada tampilan konfirmasi bank
            $callbackUrl = $setting->callback_payment; // url untuk callback
            $returnUrl = url('/api/mobile_redirect/Thanks for your payment. Press back button to back to the menu.');//'http://example.com/return'; // url untuk redirect
            $expiryPeriod = 1440; // untuk menentukan waktu kedaluarsa dalam menit
            $signature = hash('sha256', $merchantCode.$timestamp.$merchantKey);
            //$paymentMethod = 'VC'; //digunakan untuk direksional pembayaran

           
            $customerDetail = array(
                'firstName' => $user->name,
                'lastName' => "",
                'email' => $user->email,
                'phoneNumber' => str_replace("+62","",$user->no_hp),
            );


            $item1 = array(
                'name' => $payment->payment_name,
                'price' => $amount,
                'quantity' => 1);

          
            $itemDetails = array(
                $item1
            );

           
            $params = array(
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
                'expiryPeriod' => $expiryPeriod
                //'paymentMethod' => $paymentMethod
            );

            $params_string = json_encode($params);
            
            $url = $setting->duitku_link.'/api/merchant/createinvoice'; // Sandbox
            // $url = 'https://api-prod.duitku.com/api/merchant/createinvoice'; // Production

            $ch = curl_init();


            curl_setopt($ch, CURLOPT_URL, $url); 
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");                                                                     
            curl_setopt($ch, CURLOPT_POSTFIELDS, $params_string);                                                                  
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);                                                                      
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(                                                                          
                'Content-Type: application/json',                                                                                
                'Content-Length: ' . strlen($params_string),
                'x-duitku-signature:' . $signature ,
                'x-duitku-timestamp:' . $timestamp ,
                'x-duitku-merchantcode:' . $merchantCode    
                )                                                                       
            );   
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);

          
            $request = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

           
            $response = json_decode($request);
            
            return response()->json([
                "success" => true,
                "data" => $response
            ]);
        }
    }



    public function kwitansi($id, $user_id) {
        
        $setting = \App\Models\Setting::findorFail(1);
        $payment = Payment::findorFail($id);   
        $data = PaymentDetail::where('payment_id', $payment->id)->where('user_id', $user_id)->first(); 
        return view('admins.pembayaran.kwitansi', compact('data','setting','payment'));
    }



    public function pembulatan($uang)
    {
        $ratusan = substr($uang, -3);
        if($ratusan<500) {
            $akhir = $uang - $ratusan;
        }   
        else {
            $akhir = $uang + (1000-$ratusan);
        }
       
        return $akhir;
    }
    
    
    public function payment_test(Request $request) {
        
            
            
            $setting = Setting::findorFail(1);
            
            // $merchantCode = "DS18810"; // dari duitku
            // $merchantKey = "2e083b1f97e15dfd4cb02e6aa1d057a7"; // dari duitku
            
            
            $merchantCode = trim($setting->merchant_code); // dari duitku
            $merchantKey = trim($setting->api_payment); // dari duitku

            $timestamp = round(microtime(true) * 1000); //in milisecond
            
            $paymentAmount = 40000;
            $merchantOrderId = time() . ''; // dari merchant, unique
            $productDetails = 'Test Pay with duitku';
            $email = 'test@test.com'; // email pelanggan merchant
            $phoneNumber = '08123456789'; // nomor tlp pelanggan merchant (opsional)
            $additionalParam = ''; // opsional
            $merchantUserInfo = ''; // opsional
            $customerVaName = 'John Doe'; // menampilkan nama pelanggan pada tampilan konfirmasi bank
            $callbackUrl = 'http://example.com/api-pop/backend/callback.php'; // url untuk callback
            $returnUrl = 'http://example.com/api-pop/backend/redirect.php';//'http://example.com/return'; // url untuk redirect
            $expiryPeriod = 10; // untuk menentukan waktu kedaluarsa dalam menit
            $signature = hash('sha256', $merchantCode.$timestamp.$merchantKey);
            // return response()->json([
            //     "timestamp" => $timestamp,
            //     "signature" => $signature,
            //     "date" => date('Y-m-d H:i:s')
            // ]);
           
        
            // Detail pelanggan
            $firstName = "John";
            $lastName = "Doe";
        
            // Alamat
            $alamat = "Jl. Kembangan Raya";
            $city = "Jakarta";
            $postalCode = "11530";
            $countryCode = "ID";
        
            $address = array(
                'firstName' => $firstName,
                'lastName' => $lastName,
                'address' => $alamat,
                'city' => $city,
                'postalCode' => $postalCode,
                'phone' => $phoneNumber,
                'countryCode' => $countryCode
            );
        
            $customerDetail = array(
                'firstName' => $firstName,
                'lastName' => $lastName,
                'email' => $email,
                'phoneNumber' => $phoneNumber,
                'billingAddress' => $address,
                'shippingAddress' => $address
            );
        
        
            $item1 = array(
                'name' => 'Test Item 1',
                'price' => 10000,
                'quantity' => 1);
        
            $item2 = array(
                'name' => 'Test Item 2',
                'price' => 30000,
                'quantity' => 3);
        
            $itemDetails = array(
                $item1, $item2
            );
        
            
        
            $params = array(
                'paymentAmount' => $paymentAmount,
                'merchantOrderId' => $merchantOrderId,
                'productDetails' => $productDetails,
                'additionalParam' => $additionalParam,
                'merchantUserInfo' => $merchantUserInfo,
                'customerVaName' => $customerVaName,
                'email' => $email,
                'phoneNumber' => $phoneNumber,
                'itemDetails' => $itemDetails,
                'customerDetail' => $customerDetail,
                //'creditCardDetail' => $creditCardDetail,
                'callbackUrl' => $callbackUrl,
                'returnUrl' => $returnUrl,
                'expiryPeriod' => $expiryPeriod
                //'paymentMethod' => $paymentMethod
            );
        
            $params_string = json_encode($params);
            //echo $params_string;
            $url = $setting->duitku_link.'/api/merchant/createinvoice';
            // $url = 'https://api-prod.duitku.com/api/merchant/createinvoice'; // Production
        
            //log transaksi untuk debug 
            // file_put_contents('log_createInvoice.txt', "* log *\r\n", FILE_APPEND | LOCK_EX);
            // file_put_contents('log_createInvoice.txt', $params_string . "\r\n\r\n", FILE_APPEND | LOCK_EX);
            // file_put_contents('log_createInvoice.txt', 'x-duitku-signature:' . $signature . "\r\n\r\n", FILE_APPEND | LOCK_EX);
            // file_put_contents('log_createInvoice.txt', 'x-duitku-timestamp:' . $timestamp . "\r\n\r\n", FILE_APPEND | LOCK_EX);
            // file_put_contents('log_createInvoice.txt', 'x-duitku-merchantcode:' . $merchantCode . "\r\n\r\n", FILE_APPEND | LOCK_EX);
            $ch = curl_init();
        
        
            curl_setopt($ch, CURLOPT_URL, $url); 
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");                                                                     
            curl_setopt($ch, CURLOPT_POSTFIELDS, $params_string);                                                                  
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);                                                                      
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(                                                                          
                'Content-Type: application/json',                                                                                
                'Content-Length: ' . strlen($params_string),
                'x-duitku-signature:' . $signature ,
                'x-duitku-timestamp:' . $timestamp ,
                'x-duitku-merchantcode:' . $merchantCode    
                )                                                                       
            );   
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        
            //execute post
            $request = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
            if($httpCode == 200)
            {
                $result = json_decode($request, true);
                //header('location: '. $result['paymentUrl']);
                print_r($result, false);
                // echo "paymentUrl :". $result['paymentUrl'] . "<br />";
                // echo "reference :". $result['reference'] . "<br />";
                // echo "statusCode :". $result['statusCode'] . "<br />";
                // echo "statusMessage :". $result['statusMessage'] . "<br />";
            }
            else
            {
                // echo $httpCode . " " . $request ;
                echo $request ;
            }
    }
    
    
    
    
    
    
}