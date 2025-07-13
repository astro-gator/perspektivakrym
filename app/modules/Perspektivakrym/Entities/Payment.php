<?php

namespace Modules\Perspektivakrym\Entities;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = [
        'deal_id',
        'type',
        'doc_number',
        'number',
        'guid',
        'fact_date',
        'plan_date',
        'fact_amount',
        'plan_amount',
        'status',
        'note',
        'blocked',
        'user_block',
    ];
    protected $table = 'perspektivakrym_payments';
}
