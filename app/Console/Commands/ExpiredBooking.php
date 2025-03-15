<?php

namespace App\Console\Commands;

use App\Models\Notif;
use App\Models\Setting;
use App\Models\Transaction;
use App\Models\User;
use DateTime;
use Illuminate\Console\Command;
use Google\Client as GoogleClient;

class ExpiredBooking extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'expired_booking:cron';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Expired Booking Handle';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $setting = Setting::findorFail(1);
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
            if ($total_menit < $expired_time) {
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
        $data = new Notif;
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