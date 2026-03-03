<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LogPresensi extends Model
{
    protected $table = 'log_presensi';
    public $timestamps = false;
    protected $guarded = [];
    public function pegawai()
    {
        return $this->belongsTo(Pegawai::class, 'user_id', 'user_id');
    }
}