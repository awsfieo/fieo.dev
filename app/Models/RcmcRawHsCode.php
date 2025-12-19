<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RcmcRawHsCode extends Model
{
    use HasFactory;

    protected $table = 'rcmc_raw_hscodes';

    protected $guarded = ['id'];
}