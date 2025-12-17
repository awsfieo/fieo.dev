<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RcmcReceipt extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'receipt_date' => 'date',
        'is_tds_paid'  => 'boolean',
    ];

    public function office()
    {
        return $this->belongsTo(Office::class);
    }
}