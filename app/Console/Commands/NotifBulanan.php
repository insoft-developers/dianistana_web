<?php

namespace App\Console\Commands;

use App\Models\NotifTagihan;
use App\Models\Payment;
use App\Models\PaymentDetail;
use App\Models\User;
use Illuminate\Console\Command;
use Google\Client as GoogleClient;

class NotifBulanan extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notif_bulanan:cron';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $payment = Payment::where('payment_type', 1)->get();
        $user = User::where('no_hp', '!=', '')->where('level', 'user')->get();

        $sekarang = date('Y-m-d');

        $belum_bayar = [];
        foreach ($payment as $p) {
            foreach ($user as $u) {
                $cek = PaymentDetail::where('user_id', $u->id)
                    ->where('payment_id', $p->id)
                    ->where('payment_status', 'PAID');
                if ($cek->count() > 0) {
                } else {
                    $row['id'] = $u->id;
                    $row['name'] = $u->name;
                    $row['wa'] = $u->no_hp;
                    $row['payment'] = $p->id;
                    $row['periode'] = $p->periode;
                    $row['reg_id'] = $u->token;
                    $row['due'] = $p->due_date;
                    array_push($belum_bayar, $row);
                }
            }
        }

        foreach ($belum_bayar as $b) {
            $cek_sudah_kirim = NotifTagihan::where('date', $sekarang)->where('payment_id', $b['payment']);
            if ($cek_sudah_kirim->count() > 0) {
            } else {
                // $tgl1 = new DateTime($b['due']);
                // $tgl2 = new DateTime($sekarang);
                // $jarak = $tgl2->diff($tgl1);
                // $selisih = $jarak->d;
                $sekarang = date('d');
                if ($sekarang == '01' || $sekarang == '10' || $sekarang == '15' || $sekarang == '18' || $sekarang == '19' || $sekarang == '22') {
                    // $this->send_wa($b['wa'], $b['name'], $b['periode']);
                    $isi = new \App\Models\NotifTagihan();
                    $isi->date = date('Y-m-d');
                    $isi->payment_id = $b['payment'];
                    $isi->save();

                    $this->send_notif_to($b);
                }
               
            }
        }
    }

    public function send_notif_to($b)
    {
        if (!empty($b['reg_id'])) {
            $title = 'Tagihan Iuran Bulanan Periode ' . $b['periode'];
            $message = '[MyDianIstana] - Bpk/Ibu ' . $b['name'] . ' yang terhormat, Tagihan iuran bulanan anda untuk periode ' . $b['periode'] . ' telah jatuh tempo. Mohon segera dilakukan pembayaran. Terima Kasih';
            $regid = $b['reg_id'];
            $n = new \App\Models\Notif();
            $n->title = $title;
            $n->slug = str_replace(' ', '', $title);
            $n->message = $message;
            $n->admin_id = -1;
            $n->user_id = $b['id'];
            $n->status = 0;
            $n->created_at = date('Y-m-d H:i:s');
            $n->updated_at = date('Y-m-d H:i:s');
            $n->save();
            $this->notify($title, $message, $regid);
        }
    }

    public function notify($title, $message, $regid)
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
            return response()->json([
                'message' => 'notification sent',
                'response' => json_decode($response, true),
            ]);
        }
    }
}