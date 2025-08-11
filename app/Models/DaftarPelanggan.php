<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DaftarPelanggan extends Model
{
    use HasFactory;
    protected $table = "daftar_pelanggan";
    protected $guarded = [];
}
