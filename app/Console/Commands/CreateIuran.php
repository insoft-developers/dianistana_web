<?php

namespace App\Console\Commands;

use App\Models\Payment;
use Illuminate\Console\Command;

class CreateIuran extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'iuran:cron';

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
	$setting = \App\Models\Setting::findorFail(1);
        $tgl_iuran = $setting->tgl_create_iuran_bulanan;
        $tgl_tempo = $setting->tanggal_jatuh_tempo_iuran_bulanan;
        $tgl_sekarang = date('d');
        $bln_sekarang = date('m');
        $thn_sekarang = date('Y');
        $periode = $bln_sekarang.'-'.$thn_sekarang;
        $due = $thn_sekarang.'-'.$bln_sekarang.'-'.$tgl_tempo;

        if($tgl_sekarang >= $tgl_iuran) {
            $cek = Payment::where('payment_type', 1)
                ->where('periode', $periode)
                ->where('payment_dedication', -1);
            if($cek->count() > 0) {

            } else {
                $i = new Payment;
                $i->payment_name = "Iuran Bulanan Periode ".$periode;
                $i->payment_desc = "Iuran Bulanan Periode ".$periode;
                $i->payment_type = 1;
                $i->due_date = $due;
                $i->periode = $periode;
                $i->payment_amount = 0;
                $i->payment_dedication = -1;
                $i->created_at = date('Y-m-d H:i:s');
                $i->updated_at = date('Y-m-d H:i:s');
                $i->save();

            }
        }
    }
}
