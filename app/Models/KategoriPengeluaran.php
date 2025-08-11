<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class KategoriPengeluaran extends Model
{
    use HasFactory;
    protected $table = "kategori_pengeluaran";
    protected $guarded = [];

    public $timestamps = false;
}
