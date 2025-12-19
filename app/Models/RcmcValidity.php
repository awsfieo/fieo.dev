<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RcmcValidity extends Model
{
    use HasFactory;

    protected $table = 'rcmc_validity';

    protected $guarded = ['id'];

    protected $casts = [
        'rcmc_issue_date' => 'date',
        'rcmc_valid_upto' => 'date',
        'annual_turnover' => 'decimal:2',
        'export_turnover' => 'decimal:2',
        'export_performance' => 'decimal:2',
    ];
}