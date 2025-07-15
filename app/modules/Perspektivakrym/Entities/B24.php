<?php


namespace Modules\Perspektivakrym\Entities;


class B24
{
    protected $rest;

    public function __construct(CRest $rest)
    {
        $this->rest = $rest;
    }

    public function getCurrentUser($auth)
    {
        $url = "https://" . config('perspektivakrym.domain') . "/rest/user.current.json?auth=" . $auth;
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, TRUE );
        curl_setopt( $ch, CURLOPT_USERAGENT, __CLASS__ );

        $res = curl_exec($ch);
        curl_close($ch);

        $result = json_decode($res, true);

        return $result;
    }

    public function getDealById($dealId)
    {
        $res = $this->rest->call('crm.deal.get', ['id' => $dealId]);
        $this->checkError($res);
        return $res;
    }

    public function changeFieldContractPaidOfDeal($dealId, $status = 0) {
        $res = $this->rest->call('crm.deal.update', ['id' => $dealId, 'fields' => ['UF_CRM_1671459181505' => $status]]);
        $this->checkError($res);
        return $res;
    }

    public function getAppInfo($auth)
    {
        $res = $this->rest->call('app.info', ['auth' => $auth]);
        $this->checkError($res);

        //array:2 [▼ // modules/Perspektivakrym/Entities/B24.php:49
        //  "result" => array:11 [▼
        //    "ID" => 210
        //    "CODE" => "local.6867d4682b0226.24518665"
        //    "VERSION" => 1
        //    "STATUS" => "L"
        //    "INSTALLED" => true
        //    "PAYMENT_EXPIRED" => "N"
        //    "DAYS" => null
        //    "LANGUAGE_ID" => "ru"
        //    "LICENSE" => "ru_pro100"
        //    "LICENSE_TYPE" => "pro100"
        //    "LICENSE_FAMILY" => "pro"
        //  ]
        //  "time" => array:8 [▼
        //    "start" => 1752600562.3879
        //    "finish" => 1752600562.4149
        //    "duration" => 0.02699613571167
        //    "processing" => 0.00032401084899902
        //    "date_start" => "2025-07-15T20:29:22+03:00"
        //    "date_finish" => "2025-07-15T20:29:22+03:00"
        //    "operating_reset_at" => 1752601162
        //    "operating" => 0
        //  ]
        //]

        return $res;
    }

    public function getContactId($contactId)
    {
        $res = $this->rest->call('crm.contact.get', ['id' => $contactId]);
        $this->checkError($res);
        return $res;
    }

    public function getDeals($order = [], $select = [], $filter = [], $start = 0)
    {
        $res = $this->rest->call('crm.deal.list', ['order' => $order, 'select' => $select, 'filter' => $filter,  'start' => $start]);
        $this->checkError($res);
        return $res;
    }

    public function getLead($order = [], $select = [], $filter = 0, $start = 0) {
        //13040
        //54 - монако
        //56 - паруса мечты
        //
        $res = $this->rest->call('crm.lead.list', ['order' => $order, 'select' => $select, 'filter' => $filter, 'start' => $start]);
//        $res = $this->rest->call('crm.lead.get', ['id' => 13040]);
        $this->checkError($res);
        return $res;
    }

    protected function checkError($res)
    {
        if (isset($res['error'])) {
            throw new \DomainException($res['error_description']);
        }
    }
}
