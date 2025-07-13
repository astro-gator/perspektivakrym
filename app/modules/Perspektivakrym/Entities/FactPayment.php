<?php

namespace Modules\Perspektivakrym\Entities;

use Illuminate\Database\Eloquent\Model;

class FactPayment extends Model
{
    protected $table = 'perspektivakrym_fact_payments';
    protected $fillable = [
        'payment_id',
        'type',
        'date',
        'contractor',
        'doc_number',
        'amount',
        'note',
        'deal_id'
    ];

    public const MANUAL = 'manual';
    public const С1 = '1c';
}
