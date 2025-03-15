<?php

namespace App\Console\Commands;

use App\Models\Payment;
use App\Models\PaymentDetail;
use Illuminate\Console\Command;
class DueDateCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'due-date:cron';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Expired one time payment';
    /**
     * Execute the console command.
     */
    public function handle()
    {
        $sekarang = date('Y-m-d');
        $payment = Payment::where('due_date', '<=', $sekarang)->where('payment_type', 3)->whereNull('status');
        $lists = $payment->get();
        if ($lists->count() > 0) {
            foreach ($lists as $list) {
                if ($list->payment_dedication > 0) {
                    $bayar = PaymentDetail::where('payment_id', $list->id)->where('payment_status', 'PAID');
                    if($bayar->count() > 0) {
                        Payment::where('id', $list->id)->update([
                            "status" => "PAID"
                        ]);
                    } else {
                        Payment::where('id', $list->id)->update([
                            "status" => "CANCELLED"
                        ]);
                    }
                }  else {
                    Payment::where('id', $list->id)->update([
                        "status" => "CANCELLED"
                    ]);
                }
            }
        }
    }
}