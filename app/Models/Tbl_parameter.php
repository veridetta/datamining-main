<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class Tbl_parameter extends Model
{
    use HasFactory, Notifiable;
    protected $primaryKey = 'no_rangka';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = [
        'no_rangka',
        'type_mobil',
        'pkb_type',
        'kilometer',
        'total_revenue',
        'tahun_kontruksi',
        'service_kategori'
    ];
    public $timestamps = false;
}
