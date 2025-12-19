<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RcmcRawContactPerson extends Model
{
    use HasFactory;

    protected $table = 'rcmc_raw_contact_persons';

    protected $guarded = ['id'];
}