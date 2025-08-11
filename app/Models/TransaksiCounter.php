<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransaksiCounter extends Model
{
    use HasFactory;

    protected $table = "transaksi_counter";
    protected $guarded = [];
}
