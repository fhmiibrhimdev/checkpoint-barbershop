<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Persediaan extends Model
{
    use HasFactory;
    protected $table = "persediaan";
    protected $guarded = [];

    public $timestamps = false;
}
