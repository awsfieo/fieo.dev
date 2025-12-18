<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RcmcDirector extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'doe' => 'date',
        'iec_issue_date' => 'date',
        'rcmc_issue_date' => 'date',
        'is_eou_sez' => 'integer',
    ];
}