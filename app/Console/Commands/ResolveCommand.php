<?php

namespace App\Console\Commands;

use App\Models\Ticketing;
use Illuminate\Console\Command;

class ResolveCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'resolve:cron';

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
        $ticketing = Ticketing::where('status', '!=', 3)->get();

        foreach($ticketing as $ticket) {
            $tanggal_update = date('Y-m-d', strtotime($ticket->updated_at));
            $hari = $this->cek_hari($tanggal_update);
            if($hari >= 7) {
                Ticketing::where('id', $ticket->id)->update([
                    "status" => 3
                ]);
            }
        }
        
    }

    public function cek_hari($updated_at) {
        $tanggal_1 = date_create($updated_at);
        $tanggal_sekarang = date('Y-m-d');
        $tanggal_2 = date_create($tanggal_sekarang);
        $selisih = date_diff($tanggal_1, $tanggal_2);

        $s_hari = $selisih->days;

        return $s_hari;
    }
 }
