<?php

namespace Modules\Perspektivakrym\Entities;

use Illuminate\Database\Eloquent\Model;

class PlanPayment extends Model
{
    protected $table = 'perspektivakrym_plan_payments';
    protected $fillable = [
        'deal_id',
        'type',
        'pay_type',
        'doc_number',
        'amount',
        'date',
        'blocked',
        'note',
        'order',
        'is_text_date',
        'text_date',
        'add_type',
        'number_graph'
    ];

    //ДДУ / земля / доп лот
    public const MAIN = 'main';
    //подряд
    public const CONTRACT = 'contract';

    public const BLOCK = 1;
    public const ACTIVE = 0;

    public const DOWN_PAYMENT = 'down_payment';
    public const REGULAR_PAYMENT = 'regular_payment';

    public const TEXT_DATE_YES = 1;
    public const TEXT_DATE_NO = 0;

    //способы добавления платежей
    public const AUTO = 'auto';
    public const MANUAL = 'manual';
}
