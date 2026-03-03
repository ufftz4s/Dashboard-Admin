<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pegawai extends Model
{
    protected $table = 'pegawai'; // Nama tabel di pgAdmin
    protected $primaryKey = 'user_id'; // Nama kolom ID-nya
    public $timestamps = false; // Matikan timestamps
    protected $guarded = []; // Izinkan semua kolom
}