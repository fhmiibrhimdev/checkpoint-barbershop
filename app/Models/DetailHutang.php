<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetailHutang extends Model
{
    use HasFactory;

    protected $table = "detail_hutang";
    protected $guarded = [];
    public $timestamps = true;
}
