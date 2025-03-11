<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{

    use HasFactory;

    protected $table = 'transactions';

    protected $fillable = [
        'transaction_id',
        'payment_gateway',
        'amount',
        'currency',
        'status',
        'request_data',
        'response_data',
    ];

    protected $casts = [
        'request_data' => 'array',
        'response_data' => 'array',
    ];
}
