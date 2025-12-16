<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RcmcRawApprovedApplication extends Model
{
    use HasFactory;

    protected $fillable = [
        'iec',
        'company_name',
        'file_date',
        'file_number',
        'rcmc_number',
        'application_type',
        'status',
        'closed_by',
        'office',
    ];

    protected $casts = [
        'file_date' => 'date',
    ];
}