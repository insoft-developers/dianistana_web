<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Broadcasting extends Model
{
    use HasFactory;

    protected $fillable = [
        "title",
        "message",
        "image",
        "admin_id",
        "user_id",
        "send_date",
        "send_time",
        "sending_priority",
        "sending_status",
        "is_blok",
        "person",
    ] ;
}