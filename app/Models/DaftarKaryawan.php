<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DaftarKaryawan extends Model
{
    use HasFactory;
    protected $table = "daftar_karyawan";
    protected $guarded = [];
}
