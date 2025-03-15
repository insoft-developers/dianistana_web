<?php

namespace App\Imports;

use App\Models\Blok;
use App\Models\User;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use PhpOffice\PhpSpreadsheet\Shared\Date;


class UserImport implements ToModel, WithHeadingRow
{
    /**
    * @param Collection $collection
    */
    
    private $failedTask = [] ;

    public function model(array $row)
    {
        
        if($row['username'] == null) {
            return null;
        }


        $tanggal_lahir =  Date::excelToDateTimeObject($row['birthday']);
        $mulai_menempati = Date::excelToDateTimeObject($row['mulai_menempati']);

        // $tanggal_lahir =  $row['birthday'];
        // $mulai_menempati = $row['mulai_menempati']      ;
        
        
        $check = User::where('username', $row['username'])->whereNull('deleted_at')->count();
        
        
        if ($check > 0) {
            $list['name'] = $row['name'];
            $list['email'] = $row['email'];
            $list['problem'] = $row['username'];
            $list['keterangan'] = 'username has already exists';
            array_push($this->failedTask, $list);
            return null;
        }        
        
        if($row['penyelia'] != 'SDP' && $row['penyelia'] != 'DMSI') {
            $list['name'] = $row['name'];
            $list['email'] = $row['email'];
            $list['problem'] = $row['penyelia'];
            $list['keterangan'] = 'penyelia not registered';
            array_push($this->failedTask, $list);
            return null;
        }

        $blok_check = Blok::where('blok_name', $row['blok'])->count();
        if($blok_check <= 0) {
            $list['name'] = $row['name'];
            $list['email'] = $row['email'];
            $list['problem'] = $row['blok'];
            $list['keterangan'] = 'blok not registered';
            array_push($this->failedTask, $list);
            return null;
        }

        
        return new User([
            "name" => $row['name'] == null ? uniqid() : $row['name'],
            "birthday" => $tanggal_lahir,
            "username" => $row['username'] == null ? uniqid() : $row['username'],
            "email" => $row['email'] == null ? uniqid().'@gmail.com' : $row['email'],
            "password" => $row['password'] == null ?  bcrypt("1234") : bcrypt($row['password']),
            "jenis_kelamin" => $row['jenis_kelamin'] == 1 ? "Laki-laki" : "Perempuan",
            "no_hp" => '+'.$row['no_hp'],
            "level" => "user",
            "is_email_verified" => 0,
            "is_active"=> 1,
            "passcode" => 0,
            "penyelia" => $row['penyelia'] == null ? "SDP" : $row['penyelia'],
            "blok" => $row['blok'] == null ? "AI" : $row['blok'],
            "nomor_rumah" => $row['nomor_rumah'] == null ? '-' : $row['nomor_rumah'],
            "daya_listrik" => $row['daya_listrik'] == null ? '-': $row['daya_listrik'],
            "luas_tanah" => $row['luas_tanah'] == null ? 0 : $row['luas_tanah'],
            "iuran_bulanan" => $row['iuran_bulanan'] == null ? 0 : $row['iuran_bulanan'],
            "whatsapp_emergency" => $row['whatsapp_emergency'] == null ? '-' : '+'.$row['whatsapp_emergency'],
            "keterangan" => $row['keterangan'] == null ? 'tidak ada keterangan' : $row['keterangan'],
            "alamat_surat_menyurat" => $row['alamat_surat_menyurat'] == null ? '-' : $row['alamat_surat_menyurat'],
            "nomor_telepon_rumah" => $row['nomor_telepon_rumah'] == null ? '-' : $row['nomor_telepon_rumah'],
            "id_pelanggan_pdam" => $row['id_pelanggan_pdam'] == null ? '-' : $row['id_pelanggan_pdam'],
            "nomor_meter_pln" => $row['nomor_meter_pln'] == null ? '-' : $row['nomor_meter_pln'], 
            "mulai_menempati" => $row['mulai_menempati'] == null ? date('Y-m-d') : $mulai_menempati
        ]);
    }

    public function getFailedTask() {
        return $this->failedTask;
    }
}
