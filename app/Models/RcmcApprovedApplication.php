<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RcmcApprovedApplication extends Model
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
        'employee_code',
        'office_id',
    ];

    protected $casts = [
        'file_date' => 'date',
    ];
    
    // Relationships can be added here later (e.g., belongsTo Office)
}