<?php


namespace Modules\Perspektivakrym\Entities;


class BDeal
{
    public function getDealById(int $dealId)
    {
        $res = CRest::call('crm.deal.get', ['id' => $dealId]);
        if (isset($res['error'])) {
            throw new \DomainException($res['error_description']);
        }

        return $res['result'];
    }

    public function getFields()
    {
        $res = CRest::call('crm.deal.fields',[]);
        if (isset($res['error'])) {
            throw new \DomainException($res['error_description']);
        }

        return $res['result'];
    }
}
