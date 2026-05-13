<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VerificationResult extends Model
{
    use HasFactory;

    protected $table = 'verification_results';

    protected $fillable = [
        'submitted_at',
        'synced_to_sheet_at',
        'id_scraping',
        'source_tab',
        'match_idsbr',
        'match_nama_usaha',
        'match_alamat',
        'verification_status',
        'officer_name',
        'officer_id',
        'officer_latitude',
        'officer_longitude',
        'verified_latitude',
        'verified_longitude',
        'distance_km',
        'notes',
        'photo_url',
        'device_id',
        'idempotency_key',
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
        'synced_to_sheet_at' => 'datetime',
        'officer_latitude' => 'float',
        'officer_longitude' => 'float',
        'verified_latitude' => 'float',
        'verified_longitude' => 'float',
        'distance_km' => 'float',
    ];
}
