<?php

namespace Modules\Perspektivakrym\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\Perspektivakrym\Entities\B24;
use Modules\Perspektivakrym\Entities\Contact;
use Modules\Perspektivakrym\Services\DealService;

class UnisenderController extends Controller
{
    protected $b24;
    protected $contactModel;
    protected $sDeal;

    public function __construct(B24 $b24, Contact $contact, DealService $sDeal)
    {
        $this->b24 = $b24;
        $this->contactModel = $contact;
        $this->sDeal = $sDeal;
    }

    /**
     * Все лиды
     */
    public function getDealAllLead($listId)
    {
        $filter = ['STAGE_ID' => 'NEW'];
//        $listId = 19828748;

//        $this->sDeal->exclude($listId);
        $this->sDeal->getDeals($filter, $listId);
        $this->sDeal->upload($listId);
    }

    /**
     * !!!!!!
     * Интересует ...
     * @param $id
     * bitrix id: 92 - Монако || unisender list id: 19828748
     * bitrix id: 94 - Паруса Мечты || unisender list id: 19828748
     * bitrix id: 102 - Парк плаза || unisender list id: 19828748
     * bitrix id: 96 - Династия || unisender list id: 19828748
     */
    public function getDealInterested($id, $listId)
    {
        $filter = ['STAGE_ID' => ['PREPARATION', 'PREPAYMENT_INVOICE', '3'], 'UF_CRM_1602666071' => [$id]];

//        $this->sDeal->exclude($listId);
        $this->sDeal->getDeals($filter, $listId);
        $this->sDeal->upload($listId);
    }

    public function getLeadInterested($id, $listId)
    {

        $filter = ['STATUS_ID' => 'CONVERTED', 'UF_CRM_1602664584'=>[$id]];

//        $this->sDeal->exclude($listId);
        $this->sDeal->getLeads($filter, $listId);
        $this->sDeal->upload($listId);
    }

    /**
     * Купившие
     * @param int $id
     * @param int $listId
     * bitrix string: 'Паруса Мечты' || unisender list id: 19828748
     * bitrix string: 'Парк Плаза' || unisender list id: 19828748
     * bitrix string: 'Жилой комплекс «Монако»' || unisender list id: 19828748
     * bitrix string: 'Династия' || unisender list id: 19828748
     * bitrix string: 'Luchi' || unisender list id: 19829000
     */
    public function getDealBought($id, $listId)
    {
        $filter = ['STAGE_ID' => ['EXECUTING', 'WON'], 'UF_CRM_PB_PROJECT' => [$id]];

//        $this->sDeal->exclude($listId);
        $this->sDeal->getDeals($filter, $listId);
        $this->sDeal->upload($listId);
    }

    /**
     * Купившие
     * @param $list - массив ID списков
     * @param $listId - ID unisender list
     */
    public function getDealBoughtForMultiple($list, $listId)
    {
//        try {
            $this->sDeal->multipleUpload($list, $listId);
//        } catch (\Exception $e) {
//
//        }
    }

//    public function getContactInfo()
//    {
//        foreach ($this->contactModel->where('status', $this->contactModel::WAIT)->cursor() as $item) {
//            try {
//
//                if ($item->contact_id == '' || is_null($item->contact_id)) {
//                    throw new \DomainException('Нет контакта');
//                }
//
//                $contact = $this->b24->getContactId($item->contact_id);
//
//                $fullName = $contact['result']['LAST_NAME'] . ' ' .  $contact['result']['NAME'] . ' ' . $contact['result']['SECOND_NAME'];
//                $fullName = trim($fullName);
//
//                if (isset($contact['result']['EMAIL'])) {
//                    foreach ($contact['result']['EMAIL'] as $email) {
//                        $contactEmail = $email['VALUE'];
//                        break;
//                    }
//                }
//
//                if (isset($contact['result']['PHONE'])) {
//                    foreach ($contact['result']['PHONE'] as $phone) {
//                        $contactPhone = $phone['VALUE'];
//                        break;
//                    }
//                }
//
//                $item->contact_full_name = $fullName;
//                $item->contact_email = $contactEmail;
//                $item->contact_phones = $contactPhone;
//                $item->save();
//                sleep(1);
//            } catch (\Exception $e) {
//                $item->status = $this->contactModel::ERROR;
//                $item->note = $e->getMessage();
//                $item->save();
//            }
//        }
//        dd('ok');
//    }
//
//
//    protected function parseDeals($filter, $unisenderList)
//    {
//        $start = 0;
//        $step = 50;
//        $res = $this->b24->getDeals([],[],$filter, $start);
//
//        $total = $res['total'];
//
//        do {
//            foreach ($res['result'] as $deal) {
//                $this->contactModel->create([
//                    'deal_id' => $deal['ID'],
//                    'contact_id' => $deal['CONTACT_ID'],
//                    'unisender_list_id' => $unisenderList,
//                    'status' => $this->contactModel::WAIT,
//                ]);
//            }
//            $start += $step;
//            $res = $this->b24->getDeals([],[],$filter, $start);
//            sleep(1);
//        } while ($total > $start);
//    }

    public function test()
    {
//        ['STATUS_ID' => 'CONVERTED', 'UF_CRM_1602664584'=>[54,56]]
//        $res = $this->b24->getLead();
//        dd($res);
//        $listId = 19828748;
//        $this->sDeal->exclude($listId);
//        $this->sDeal->upload($listId);
    }
}
