<?php

namespace App\Console\Commands;

use App\Models\Payment;
use App\Models\PaymentDetail;
use App\Models\User;
use Illuminate\Console\Command;

class Tunggakan extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tunggakan:cron';

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
       	$sekarang = date('Y-m-d');
        $payments = Payment::where('payment_type', 1)
                ->where('due_date', '<', $sekarang)
                ->get();
        $users = User::where('level', 'user')->get();
        $belum_bayar = [];
        foreach($payments as $p) {
            foreach($users as $u) {
                $cek = PaymentDetail::where('user_id', $u->id)->where('payment_id', $p->id)->where('payment_status','PAID');
                if($cek->count() > 0) {}
                else {
                     $cek_tunggakan = \App\Models\Tunggakan::where('payment_id', $p->id)
                            ->where('user_id', $u->id);
                     if($cek_tunggakan->count() > 0) {

                     } else {
                        $tunggakan = new \App\Models\Tunggakan;
                        $tunggakan->user_id = $u->id;
                        $tunggakan->payment_id = $p->id;
                        $tunggakan->amount = $u->iuran_bulanan;
                        $tunggakan->description = $p->payment_name;
                        $tunggakan->save();
                     }

                }
            }
        }

       return $belum_bayar;
    }
}
