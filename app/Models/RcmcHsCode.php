<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RcmcHsCode extends Model
{
    use HasFactory;

    protected $table = 'rcmc_hscodes';

    protected $guarded = ['id'];

    protected $casts = [
        'rcmc_issue_date' => 'date',
        'product_images'  => 'array', // Cast JSON to Array automatically
    ];
}