<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class KategoriSatuan extends Model
{
    use HasFactory;
    protected $table = "kategori_satuan";
    protected $guarded = [];

    public $timestamps = false;
}
