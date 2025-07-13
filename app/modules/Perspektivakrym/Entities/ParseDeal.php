<?php

namespace Modules\Perspektivakrym\Entities;

use Illuminate\Database\Eloquent\Model;

class ParseDeal extends Model
{
    protected $table = 'perspektivakrym_parse_deals';
    protected $fillable = [
        'distribution_list',
        'deal_id',
        'deal',
        'contact',
        'status',
        'note',
    ];

    //ожидаем получения данных по контакту
    public const STATUS_WAIT_GET_CONTACT = 'wait_get_contact';
    //ожидаем отправку в unisender
    public const STATUS_WAIT_UPLOAD = 'wait_upload';
    public const STATUS_SUCCESS = 'success';
    public const STATUS_ERROR = 'error';
    public const STATUS_ERROR_NOT_CONTACT =  'error_not_contact';

    //все лиды
    public const LIST_ALL_LEADS = '123';
    //Интересует Монако
    public const LIST_INTERESTED_MONAKO = '111';
    //Интересует Паруса Мечты
    public const LIST_INTERESTED_PARUSAMECHTY = '222';
    //Интересует Парк Плаза
    public const LIST_INTERESTED_PARKPLAZA = '333';
    //Интересует Династия
    public const LIST_INTERESTED_DINASTIYA = '444';

    //Купившие Паруса Мечты
    public const LIST_BOUGHT_PARUSAMECHTY = '555';
    //Купившие Парк Плаза
    public const LIST_BOUGHT_PARKPLAZA = '666';
    //Купившие Жилой комплекс «Монако»
    public const LIST_BOUGHT_MONAKO = '777';
    //Купившие Династия
    public const LIST_BOUGHT_DINASTIYA = '888';
}
