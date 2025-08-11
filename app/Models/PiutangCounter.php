<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PiutangCounter extends Model
{
    use HasFactory;

    protected $table = "piutang_counter";
    protected $guarded = [];
}
