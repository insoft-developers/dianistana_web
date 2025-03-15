<?php

namespace App\Imports;

use App\Models\User;
use App\Models\UserLog;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use PhpOffice\PhpSpreadsheet\Shared\Date;


class UserEditImport implements ToModel, WithHeadingRow
{
    /**
    * @param Collection $collection
    */
    public function model(Array $row)
    {
        
        if($row['user_id'] == null) {
            return null;
        }

        
       
        
        $data_update = [
            
            "username" => $row['username'] == null ? uniqid() : $row['username'],
            "email" => $row['email'] == null ? uniqid().'@gmail.com' : $row['email'],
            "jenis_kelamin" => $row['jenis_kelamin'] == 1 ? "Laki-laki" : "Perempuan",
            "no_hp" => '+'.$row['no_hp'],
            "level" => "user",
            "is_email_verified" => 0,
            "is_active"=> 1,
            "passcode" => 0,
            "penyelia" => $row['penyelia'] == null ? "SDP" : $row['penyelia'],
            "daya_listrik" => $row['daya_listrik'] == null ? '': $row['daya_listrik'],
            "luas_tanah" => $row['luas_tanah'] == null ? 0 : $row['luas_tanah'],
            "iuran_bulanan" => $row['iuran_bulanan'] == null ? 0 : $row['iuran_bulanan'],
            "whatsapp_emergency" => $row['whatsapp_emergency'] == null ? '' : '+'.$row['whatsapp_emergency'],
            "keterangan" => $row['keterangan'] == null ? '' : $row['keterangan'],
            "alamat_surat_menyurat" => $row['alamat_surat_menyurat'] == null ? '' : $row['alamat_surat_menyurat'],
            "nomor_telepon_rumah" => $row['nomor_telepon_rumah'] == null ? '' : $row['nomor_telepon_rumah'],
            "id_pelanggan_pdam" => $row['id_pelanggan_pdam'] == null ? '' : $row['id_pelanggan_pdam'],
            "nomor_meter_pln" => $row['nomor_meter_pln'] == null ? '' : $row['nomor_meter_pln'], 
           
        ];

        
         if($row['password'] != null) {
            $data_update['password'] = bcrypt($row['password']);
         }

         if($row['birthday'] != null && ! is_string($row['birthday'])) {
            $tanggal_lahir =  Date::excelToDateTimeObject($row['birthday']);
            $data_update['birthday'] = $tanggal_lahir;
         }

         if($row['mulai_menempati'] != null && ! is_string($row['mulai_menempati'])) {
            $mulai_menempati = Date::excelToDateTimeObject($row['mulai_menempati']);
            $data_update['mulai_menempati'] = $mulai_menempati;
         }

         
        User::where('id', $row['user_id'])->update($data_update);
        
        return new UserLog([
            "user_id" => adminAuth()->id,
            "description" => 'upload edit user',
            "action" => "UPLOAD",
        ]);
    }
}
