<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RcmcRawValidity extends Model
{
    use HasFactory;

    protected $table = 'rcmc_raw_validity'; // Explicit table name to avoid pluralization issues

    protected $guarded = ['id'];
}