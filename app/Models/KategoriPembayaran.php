<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class KategoriPembayaran extends Model
{
    use HasFactory;
    protected $table = "kategori_pembayaran";
    protected $guarded = [];

    public $timestamps = false;
}
