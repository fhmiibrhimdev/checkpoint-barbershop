<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CabangLokasi extends Model
{
    use HasFactory;
    protected $table = "cabang_lokasi";
    protected $guarded = [];

    public $timestamps = false;
}
