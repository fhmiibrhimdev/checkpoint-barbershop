<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class HutangCounter extends Model
{
    use HasFactory;

    protected $table = "hutang_counter";
    protected $guarded = [];
}
