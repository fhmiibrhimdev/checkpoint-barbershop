<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class KategoriProduk extends Model
{
    use HasFactory;
    protected $table = "kategori_produk";
    protected $guarded = [];

    public $timestamps = false;
}
