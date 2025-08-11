<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KategoriKeuangan extends Model
{
    use HasFactory;
    protected $table = "kategori_keuangan";
    protected $guarded = [];

    public $timestamps = false;
}
