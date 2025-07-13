<?php

namespace Modules\Perspektivakrym\Entities;

use Illuminate\Database\Eloquent\Model;

class Contact extends Model
{
    protected $table = 'perspektivakrym_contacts';
    protected $fillable = [
        'deal_id',
        'contact_id',
        'contact_full_name',
        'contact_email',
        'contact_phones',
        'unisender_list_id',
        'status',
        'note'
    ];

    public const WAIT = 'wait';
    public const ERROR = 'error';
    public const SUCCESS = 'success';
}
