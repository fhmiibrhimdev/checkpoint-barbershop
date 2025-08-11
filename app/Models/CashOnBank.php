<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CashOnBank extends Model
{
    use HasFactory;

    protected $table = "cash_on_bank";
    protected $guarded = [];
}
