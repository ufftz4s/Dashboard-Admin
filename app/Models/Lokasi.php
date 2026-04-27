<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Lokasi extends Model
{
    protected $table = 'lokasi';

    // kode_lokasi is the primary key
    protected $primaryKey = 'kode_lokasi';

    // Disable timestamps since the table doesn't have created_at/updated_at
    public $timestamps = false;

    protected $fillable = [
        'kode_lokasi',
        'instansi',
        'unit_instansi',
        'longitude',
        'latitude',
    ];

    protected $casts = [
        'kode_lokasi' => 'integer',
        'longitude' => 'decimal:6',
        'latitude' => 'decimal:6',
    ];
}
