<?php

namespace App\Exports;

use App\Models\User;
use Maatwebsite\Excel\Concerns\FromCollection;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Protection;



class UserEditExport implements FromView,ShouldAutoSize
{
   
    
    /**
    * @return \Illuminate\Support\Collection
    */

   

    public function view(): View
    {
        $data = User::all();
        
        return view('admins.user.template', [
            'data' => $data
        ]);   
    }

   
}
