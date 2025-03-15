<?php
namespace App\Traits;

use App\Models\AdminsData;
use App\Models\UserLog;


trait UserLogTrait
{
    public function insert_log($user_id, $feature, $action) {
        if($action == 'add') {
            $aksi = "has added data";
        } else if($action == 'update') {
            $aksi = "has updated data";
        } else if($action == 'delete') {
            $aksi = "has deleted data";
        }

        $admin = AdminsData::find($user_id);

        $description = "User ".$admin->username." - [".$admin->name."] ".$aksi." in the ".$feature;

        $input['user_id'] = $user_id;
        $input['description'] = $description;
        $input['action'] = strtoupper($action);

        UserLog::create($input);
    }
}
