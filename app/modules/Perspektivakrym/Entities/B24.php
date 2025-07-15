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
        dd(3, $res);
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
