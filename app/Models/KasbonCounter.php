<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class KasbonCounter extends Model
{
    use HasFactory;

    protected $table = "kasbon_counter";
    protected $guarded = [];
}
