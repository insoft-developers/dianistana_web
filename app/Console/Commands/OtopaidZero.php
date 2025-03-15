<?php

namespace App\Console\Commands;

use App\Models\Payment;
use App\Models\PaymentDetail;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;

class OtopaidZero extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'zero:cron';

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
 	$periode = date('m') . '-' . date('Y');
        $payments = Payment::where('payment_type', 1)
            ->where('periode', $periode)
            ->where('payment_dedication', -1)
            ->get();

        if ($payments->count() > 0) {
            foreach ($payments as $payment) {
                $users = User::where('level', 'user')->where('is_active', 1)->where('iuran_bulanan', 0)->get();
                if ($users->count() > 0) {
                    foreach ($users as $user) {
                        $random = random_int(1000, 9999);
                        $invoice = 'PM-' . date('dmyHis') . $random;

                        $cek = PaymentDetail::where('payment_id', $payment->id)->where('user_id', $user->id);
                        if ($cek->count() > 0) {
                        } else {
                            $pd = new PaymentDetail();
                            $pd->invoice = $invoice;
                            $pd->payment_id = $payment->id;
                            $pd->user_id = $user->id;
                            $pd->payment_status = 'PAID';
                            $pd->payment_method = 'admin_payment';
                            $pd->payment_channel = 'dian istana developers';
                            $pd->paid_at = Carbon::now();
                            $pd->created_at = Carbon::now();
                            $pd->updated_at = Carbon::now();
                            $pd->save();
                        }
                    }
                }
            }
        }   
    }
}
