<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RcmcContactPerson extends Model
{
    use HasFactory;

    protected $table = 'rcmc_contact_persons';

    protected $guarded = ['id'];

    protected $casts = [
        'dob' => 'date',
        'rcmc_issue_date' => 'date',
    ];
}