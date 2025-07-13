Поля от 1С-ников                                       | Локальная БД
---------------------------------------------------------------------------------------------------
Поле           | Тип        | Описание                 | у меня в БД     |
---------------------------------------------------------------------------------------------------
ДокументGUID   | строка 36  | Уникальный ID в 1С       | guid            |
---------------------------------------------------------------------------------------------------
ДокументТип    | строка 100 | Текст имя типа документа | type            |
---------------------------------------------------------------------------------------------------
ДокументНомер  | строка 12  | Номер документа          | doc_number      |
---------------------------------------------------------------------------------------------------
ДокументДата   | Дата       | Дата документа           | fact_date       | фактическая дата
---------------------------------------------------------------------------------------------------
ОрганизацияИД  | строка 9   | Код организации          | company_code    |
---------------------------------------------------------------------------------------------------
               | строка 9   | Текст организации        | company_text    |
---------------------------------------------------------------------------------------------------
КонтрагентИД   | строка 9   | Код контрагента          | contractor_code |
---------------------------------------------------------------------------------------------------
               | строка 9   | Текст контрагента        | contractor_text |
---------------------------------------------------------------------------------------------------
ДоговорИД      | строка 9   | Код договора             | contract_code   |
---------------------------------------------------------------------------------------------------
ЭтоПоступление | булево     | Поступление true         | incoming        | поступление или возврат
---------------------------------------------------------------------------------------------------
Сумма          | Число 16,2 | Сумма                    | fact_amount     | факт сумма
---------------------------------------------------------------------------------------------------
               |            |                          | payment_number  | номер платежа
---------------------------------------------------------------------------------------------------
               |            |                          | plan_amount     | план сумма
---------------------------------------------------------------------------------------------------
               |            |                          | plan_date       | плановая дата
---------------------------------------------------------------------------------------------------
               |            |                          | status          | статус
---------------------------------------------------------------------------------------------------
               |            |                          | note            | примечание
---------------------------------------------------------------------------------------------------
               |            |                          | deal_id         | ID сделки в мегаплане
---------------------------------------------------------------------------------------------------

====================================================================================================
Установка:

1. Настроить удаленный доступ к БД 1С-ников (название соединения: perspektivakrym_remote)

====================================================================================================
Поля в сделке

"ID" => ["type" => "integer", "title" => "ID"]
"TITLE" => ["type" => "string", "title" => "Название"]
"TYPE_ID" => ["type" => "crm_status", "statusType" => "DEAL_TYPE", "title" => "Тип"]
"CATEGORY_ID" => ["type" => "crm_category", "title" => "Направление"]
"STAGE_ID" => ["type" => "crm_status", "statusType" => "DEAL_STAGE", "title" => "Стадия сделки"]
"STAGE_SEMANTIC_ID" => ["type" => "string", "title" => "Группа стадии"]
"IS_NEW" => ["type" => "char", "title" => "Новая сделка"]
"IS_RECURRING" => ["type" => "char", "title" => "Регулярная сделка"]
"IS_RETURN_CUSTOMER" => ["type" => "char", "title" => "Повторная сделка"]
"IS_REPEATED_APPROACH" => ["type" => "char", "title" => "Повторное обращение"]
"PROBABILITY" => ["type" => "integer", "title" => "Вероятность"]
"CURRENCY_ID" => ["type" => "crm_currency", "title" => "Валюта"]
"OPPORTUNITY" => ["type" => "double", "title" => "Сумма"]
"IS_MANUAL_OPPORTUNITY" => ["type" => "char", "title" => "IS_MANUAL_OPPORTUNITY"]
"TAX_VALUE" => ["type" => "double", "title" => "Ставка налога"]
"COMPANY_ID" => ["type" => "crm_company", "title" => "Компания"]
"CONTACT_ID" => ["type" => "crm_contact", "title" => "Контакт"]
"CONTACT_IDS" => ["type" => "crm_contact", "title" => "Контакты"]
"QUOTE_ID" => ["type" => "crm_quote", "title" => "Предложение"]
"BEGINDATE" => ["type" => "date", "title" => "Дата начала"]
"CLOSEDATE" => ["type" => "date", "title" => "Дата завершения"]
"OPENED" => ["type" => "char", "title" => "Доступна для всех"]
"CLOSED" => ["type" => "char", "title" => "Закрыта"]
"COMMENTS" => ["type" => "string", "title" => "Комментарий"]
"ASSIGNED_BY_ID" => ["type" => "user", "title" => "Ответственный"]
"CREATED_BY_ID" => ["type" => "user", "title" => "Кем создана"]
"MODIFY_BY_ID" => ["type" => "user", "title" => "Кем изменена"]
"DATE_CREATE" => ["type" => "datetime", "title" => "Дата создания"]
"DATE_MODIFY" => ["type" => "datetime", "title" => "Дата изменения"]
"SOURCE_ID" => ["type" => "crm_status", "statusType" => "SOURCE", "title" => "Источник"]
"SOURCE_DESCRIPTION" => ["type" => "string", "title" => "Дополнительно об источнике"]
"LEAD_ID" => ["type" => "crm_lead", "title" => "Лид"]
"ADDITIONAL_INFO" => ["type" => "string", "title" => "Дополнительная информация"]
"LOCATION_ID" => ["type" => "location", "title" => "Местоположение"]
"ORIGINATOR_ID" => ["type" => "string", "title" => "Внешний источник"]
"ORIGIN_ID" => ["type" => "string", "title" => "Идентификатор элемента во внешнем источнике"]
"UTM_SOURCE" => ["type" => "string", "title" => "Рекламная система"]
"UTM_MEDIUM" => ["type" => "string", "title" => "Тип трафика"]
"UTM_CAMPAIGN" => ["type" => "string", "title" => "Обозначение рекламной кампании"]
"UTM_CONTENT" => ["type" => "string", "title" => "Содержание кампании"]
"UTM_TERM" => ["type" => "string", "title" => "Условие поиска кампании"]
"UF_CRM_1602665724" => ["type" => "date", "title" => "UF_CRM_1602665724", "listLabel" => "Предполагаемая дата покупки"]
"UF_CRM_1602665856" => ["type" => "money", "title" => "UF_CRM_1602665856", "listLabel" => "Сумма первого взноса"]
"UF_CRM_1602665984" => [ "type" => "enumeration", "items" => [
    0 => ["ID" => "84", "VALUE" => "Ипотека"]
    1 => ["ID" => "86", "VALUE" => "Маткапитал"]
    2 => ["ID" => "88", "VALUE" => "Военная ипотека"]
    3 => ["ID" => "90", "VALUE" => "Рассрочка"]
], "title" => "UF_CRM_1602665984", "listLabel" => "Условия"]
"UF_CRM_1602666071" => ["type" => "enumeration", "items" => [
    0 => ["ID" => "92", "VALUE" => "Монако"]
    1 => ["ID" => "94", "VALUE" => "Паруса Мечты"]
    2 => ["ID" => "96", "VALUE" => "Династия"]
    3 => ["ID" => "98", "VALUE" => "Олимпия"]
    4 => ["ID" => "100", "VALUE" => "La Vita"]
    5 => ["ID" => "102", "VALUE" => "Парк Плаза"]
    6 => ["ID" => "1342", "VALUE" => "Кореиз"]
    7 => ["ID" => "1344", "VALUE" => "Ялтинский маяк"]
    8 => ["ID" => "1346", "VALUE" => "Морские камни"]
    9 => ["ID" => "1348", "VALUE" => "Судак"]
], "title" => "UF_CRM_1602666071", "listLabel" => "Объект интереса"]
"UF_CRM_1602666205" => ["type" => "double", "title" => "UF_CRM_1602666205", "listLabel" => "Рассрочка, мес."]
"UF_CRM_1602666273" => ["type" => "enumeration", "items" => [
    0 => ["ID" => "104", "VALUE" => "Без посредников"]
    1 => ["ID" => "106", "VALUE" => "АН"]
], "title" => "UF_CRM_1602666273", "listLabel" => "Тип сделки"]
"UF_CRM_1602666641" => ["type" => "enumeration", "items" => [
    0 => ["ID" => "108", "VALUE" => "Для себя"]
    1 => ["ID" => "110", "VALUE" => "Перепродажа"]
    2 => ["ID" => "112", "VALUE" => "Под сдачу"]
], "title" => "UF_CRM_1602666641", "listLabel" => "Цель покупки"]
"UF_CRM_1602667649" => ["type" => "string", "title" => "UF_CRM_1602667649", "listLabel" => "Потребность"]
"UF_CRM_PB_PRICE" => ["type" => "string", "title" => "UF_CRM_PB_PRICE", "listLabel" => "Цена, руб"]
"UF_CRM_PB_PROJECT" => ["type" => "string", "title" => "UF_CRM_PB_PROJECT", "listLabel" => "ЖК"]
"UF_CRM_PB_HOUSE" => ["type" => "string", "title" => "UF_CRM_PB_HOUSE", "listLabel" => "Дом"]
"UF_CRM_1603114986" => ["type" => "string", "title" => "UF_CRM_1603114986", "listLabel" => "Номер помещения"]
"UF_CRM_1603115023" => ["type" => "string", "title" => "UF_CRM_1603115023", "listLabel" => "Этаж"]
"UF_CRM_1603115035" => ["type" => "string", "title" => "UF_CRM_1603115035", "listLabel" => "Подъезд"]
"UF_CRM_1603115055" => ["type" => "string", "title" => "UF_CRM_1603115055", "listLabel" => "Площадь, м2"]
"UF_CRM_1603115066" => ["type" => "string", "title" => "UF_CRM_1603115066", "listLabel" => "Цена за метр"]
"UF_CRM_1603115160" => ["type" => "string", "title" => "UF_CRM_1603115160", "listLabel" => "Кол-во комнат (PB)"]
"UF_CRM_1603115212" => ["type" => "string", "title" => "UF_CRM_1603115212", "listLabel" => "ID помещения"]
"UF_CRM_1603117280" => ["type" => "enumeration", "items" => [
    0 => ["ID" => "114", "VALUE" => "Коттедж"]
    1 => ["ID" => "116", "VALUE" => "Квартира"]
    2 => ["ID" => "118", "VALUE" => "Апартамент"]
    3 => ["ID" => "120", "VALUE" => "Участок"]
    4 => ["ID" => "122", "VALUE" => "Кладовая"]
    5 => ["ID" => "124", "VALUE" => "Паркинг"]
], "title" => "UF_CRM_1603117280", "listLabel" => "Тип помещения"]
"UF_CRM_CLTCH9KIR6DU7" => ["type" => "integer", "title" => "UF_CRM_CLTCH9KIR6DU7", "listLabel" => "ID лида"]
"UF_CRM_CLTCHA6PUPVGN" => ["type" => "string", "title" => "UF_CRM_CLTCHA6PUPVGN", "listLabel" => "Тип обращения"]
"UF_CRM_CLTCHL9FCSRLV" => ["type" => "string", "title" => "UF_CRM_CLTCHL9FCSRLV", "listLabel" => "Рекламный источник/канал"]
"UF_CRM_CLTCHKZYJHUFA" => ["type" => "url", "title" => "UF_CRM_CLTCHKZYJHUFA", "listLabel" => "Ссылка на запись звонка"]
"UF_CRM_1603889718393" => ["type" => "enumeration", "items" => [
    0 => ["ID" => "208", "VALUE" => "Парк плаза"]
    1 => ["ID" => "210", "VALUE" => "Монако"]
    2 => ["ID" => "212", "VALUE" => "Паруса мечты"]
    3 => ["ID" => "214", "VALUE" => "Династия"]
    4 => ["ID" => "216", "VALUE" => "Олимпия"]
    5 => ["ID" => "218", "VALUE" => "La vita"]
], "title" => "UF_CRM_1603889718393", "listLabel" => "Проект"]
"UF_CRM_1603891325" => ["type" => "crm", "title" => "UF_CRM_1603891325", "listLabel" => "Плательщик"]
"UF_CRM_1603893003625" => ["type" => "enumeration", "items" => [
    0 => ["ID" => "226", "VALUE" => "Сайт"]
    1 => ["ID" => "228", "VALUE" => "Дизайн"]
    2 => ["ID" => "230", "VALUE" => "Реклама в интернете"]
    3 => ["ID" => "232", "VALUE" => "IT-сервисы"]
    4 => ["ID" => "234", "VALUE" => "Реклама на радио"]
    5 => ["ID" => "236", "VALUE" => "Мероприятия"]
    6 => ["ID" => "314", "VALUE" => "Комплекс"]
], "title" => "UF_CRM_1603893003625", "listLabel" => "Статья расхода"]
"UF_CRM_5FD1F107CEBED" => ["type" => "enumeration", "items" => [
    0 => ["ID" => "816", "VALUE" => "Клиент"]
    1 => ["ID" => "818", "VALUE" => "АН"]
    2 => ["ID" => "820", "VALUE" => "Партнёр"]
    3 => ["ID" => "822", "VALUE" => "Подрядчик"]
    4 => ["ID" => "1870", "VALUE" => "Cпам"]
], "title" => "UF_CRM_5FD1F107CEBED", "listLabel" => "Тип контакта"]
"UF_CRM_AMO_529145" => ["type" => "string", "title" => "UF_CRM_AMO_529145", "listLabel" => "Причина октаза подробнее:"]
"UF_CRM_AMO_530317" => ["type" => "string", "title" => "UF_CRM_AMO_530317", "listLabel" => "Комментарии"]
"UF_CRM_AMO_530321" => ["type" => "string", "title" => "UF_CRM_AMO_530321", "listLabel" => "Статус объекта"]
"UF_CRM_AMO_530357" => ["type" => "double", "title" => "UF_CRM_AMO_530357", "listLabel" => "№ Договора"]
"UF_CRM_AMO_531587" => ["type" => "string", "title" => "UF_CRM_AMO_531587", "listLabel" => "Название ЖК"]
"UF_CRM_AMO_531589" => ["type" => "string", "title" => "UF_CRM_AMO_531589", "listLabel" => "Название дома"]
"UF_CRM_AMO_531591" => ["type" => "string", "title" => "UF_CRM_AMO_531591", "listLabel" => "№ квартиры"]
"UF_CRM_AMO_531593" => ["type" => "string", "title" => "UF_CRM_AMO_531593", "listLabel" => "Кол-во комнат"]
"UF_CRM_AMO_531595" => ["type" => "string", "title" => "UF_CRM_AMO_531595", "listLabel" => "Полная цена"]
"UF_CRM_AMO_531597" => ["type" => "string", "title" => "UF_CRM_AMO_531597", "listLabel" => "Цена за метр"]
"UF_CRM_AMO_531601" => ["type" => "string", "title" => "UF_CRM_AMO_531601", "listLabel" => "Жилая площадь, м2"]
"UF_CRM_AMO_531613" => ["type" => "string", "title" => "UF_CRM_AMO_531613", "listLabel" => "Номер на площадке"]
"UF_CRM_AMO_531615" => ["type" => "string", "title" => "UF_CRM_AMO_531615", "listLabel" => "Менеджер"]
"UF_CRM_AMO_531617" => ["type" => "string", "title" => "UF_CRM_AMO_531617", "listLabel" => "Покупатель"]
"UF_CRM_AMO_535389" => ["type" => "string", "title" => "UF_CRM_AMO_535389", "listLabel" => "Этаж"]
"UF_CRM_AMO_541547" => ["type" => "enumeration", "items" => [
    0 => ["ID" => "1548", "VALUE" => "Морские Камни"]
    1 => ["ID" => "1550", "VALUE" => "Ялтинский Маяк"]
    2 => ["ID" => "1552", "VALUE" => "Кореиз"]
    3 => ["ID" => "1554", "VALUE" => "Судак"]
    4 => ["ID" => "1556", "VALUE" => "Монако"]
    5 => [ "ID" => "1558", "VALUE" => "Паруса Мечты"]
    6 => ["ID" => "1560", "VALUE" => "Династия"]
    7 => ["ID" => "1562", "VALUE" => "Морские Камни 2"]
    8 => ["ID" => "1564", "VALUE" => "Нет конкретики"]
    9 => ["ID" => "1566", "VALUE" => "Парк Плаза"]
], "title" => "UF_CRM_AMO_541547", "listLabel" => "Заинтересовал ЖК:"]
"UF_CRM_AMO_560317" => ["type" => "date", "title" => "UF_CRM_AMO_560317", "listLabel" => "Дата создания договора"]
"UF_CRM_AMO_595617" => ["type" => "date", "title" => "UF_CRM_AMO_595617", "listLabel" => "Планируемое время покупки"]
"UF_CRM_AMO_596521" => ["type" => "string", "title" => "UF_CRM_AMO_596521", "listLabel" => "Бюджет первого платежа"]
"UF_CRM_AMO_597057" => ["type" => "enumeration", "items" => [
    0 => ["ID" => "1682", "VALUE" => "Для круглогодичного проживания"]
    1 => ["ID" => "1684", "VALUE" => "Приезжать на отдых"]
    2 => ["ID" => "1686", "VALUE" => "Для перепродажи"]
    3 => ["ID" => "1688", "VALUE" => "Для сдачи в аренду"]
    4 => ["ID" => "1690""VALUE" => "Под коммерцию"]
    5 => ["ID" => "1692", "VALUE" => "Бартер"]
    6 => ["ID" => "1694", "VALUE" => "Детям"]
    7 => ["ID" => "1696", "VALUE" => "Родителям"]
], "title" => "UF_CRM_AMO_597057", "listLabel" => "Цель приобретения"]
"UF_CRM_AMO_597071" => ["type" => "enumeration", "items" => [
    0 => ["ID" => "1698", "VALUE" => "«А»"]
    1 => ["ID" => "1700", "VALUE" => "«В»"]
    2 => ["ID" => "1702", "VALUE" => "«С»"]
], "title" => "UF_CRM_AMO_597071", "listLabel" => "Категория Клиента"]
"UF_CRM_AMO_597383" => ["type" => "string", "title" => "UF_CRM_AMO_597383", "listLabel" => "Номер клиента"]
"UF_CRM_AMO_597407" => ["type" => "string", "title" => "UF_CRM_AMO_597407", "listLabel" => "Имя клиента"]
"UF_CRM_AMO_597409" => ["type" => "string", "title" => "UF_CRM_AMO_597409", "listLabel" => "Email"]
"UF_CRM_1608143650" => ["type" => "crm", "title" => "UF_CRM_1608143650", "listLabel" => "Риелтор"]
"UF_CRM_1608146116" => ["type" => "crm", "title" => "UF_CRM_1608146116", "listLabel" => "Агентство"]
"UF_CRM_1608146226" => ["type" => "enumeration", "items" => [
    0 => ["ID" => "1890", "VALUE" => "купил первичку"]
    1 => ["ID" => "1892", "VALUE" => "купил вторичку"]
    2 => ["ID" => "1894", "VALUE" => "нет продукта или условий"]
    3 => ["ID" => "1896", "VALUE" => "не вышел на связь"]
], "title" => "UF_CRM_1608146226", "listLabel" => "Причина проигрыша"]
"UF_CRM_1608149878" => ["type" => "enumeration", "items" => [
    0 => ["ID" => "1898", "VALUE" => "Замена объекта"]
    1 => ["ID" => "1900", "VALUE" => "Нет средств"]
    2 => ["ID" => "1902", "VALUE" => "Другое"]
], "title" => "UF_CRM_1608149878", "listLabel" => "Причина расторжения"]
"UF_CRM_1608373282" => ["type" => "string", "title" => "UF_CRM_1608373282", "listLabel" => "Тип лота"]
"UF_CRM_1608402464" => ["type" => "string", "title" => "UF_CRM_1608402464", "listLabel" => "Префикс"]
"UF_CRM_5FDE46EE2DA9A" => ["type" => "string", "title" => "UF_CRM_5FDE46EE2DA9A", "listLabel" => "callId"]
"UF_CRM_5FDE46EE4AF83" => ["type" => "string", "title" => "UF_CRM_5FDE46EE4AF83", "listLabel" => "requestId"]
"UF_CRM_5FDE46EE61354" => ["type" => "string", "title" => "UF_CRM_5FDE46EE61354", "listLabel" => "siteId"]
"UF_CRM_5FDE46EE7BCF1" => ["type" => "enumeration", "items" => [
    0 => ["ID" => "2002", "VALUE" => "Коттедж"]
    1 => ["ID" => "2004", "VALUE" => "Квартира"]
    2 => ["ID" => "2006", "VALUE" => "Апартаменты"]
    3 => ["ID" => "2008", "VALUE" => "Таунхаус"]
    4 => ["ID" => "2010", "VALUE" => "Паркоместо"]
    5 => ["ID" => "2012", "VALUE" => "Кладовая"]
    6 => ["ID" => "2014", "VALUE" => "Коммерция"]
], "title" => "UF_CRM_5FDE46EE7BCF1", "listLabel" => "Тип лота"]
"UF_CRM_LAST_C" => ["type" => "string", "title" => "UF_CRM_LAST_C", "listLabel" => "Последняя коммуникация"]
"UF_CRM_FIRST_C" => ["type" => "string", "title" => "UF_CRM_FIRST_C", "listLabel" => "Первая коммуникация"]
"UF_CRM_5FE8585B08EF7" => ["type" => "string", "title" => "UF_CRM_5FE8585B08EF7", "listLabel" => "UF_CRM_FORMNAME"]
"UF_CRM_5FE8585B46D50" => ["type" => "string", "title" => "UF_CRM_5FE8585B46D50", "listLabel" => "UF_CRM_TRANID"]
]
сумма земля - UF_CRM_1610624104
сумма подряд - UF_CRM_1610624123
дней на внесение ПВ - UF_CRM_1610624156
ПВ подряд - число - UF_CRM_1610799295
