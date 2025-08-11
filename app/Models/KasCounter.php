<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KasCounter extends Model
{
    use HasFactory;

    protected $table = 'kas_counter';
    protected $guarded = [];
}
