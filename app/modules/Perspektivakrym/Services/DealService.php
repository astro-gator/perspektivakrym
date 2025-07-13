<?php


namespace Modules\Perspektivakrym\Services;


use Illuminate\Support\Facades\Log;
use Modules\Perspektivakrym\Entities\B24;
use Modules\Perspektivakrym\Entities\ParseDeal;
use Modules\Perspektivakrym\Entities\Unisender;

class DealService
{
    protected $mParserDeal;
    protected $mUnisender;
    protected $b24;

    public function __construct(ParseDeal $parseDeal, B24 $b24, Unisender $unisender)
    {
        $this->mParserDeal = $parseDeal;
        $this->b24 = $b24;
        $this->mUnisender = $unisender;
    }

    public function exclude($listId)
    {
        $this->mParserDeal
            ->where('distribution_list', $listId)
            ->where('status', $this->mParserDeal::STATUS_SUCCESS)
            ->chunk(50, function ($chunk) use ($listId) {
                foreach ($chunk as $deal) {
                    $contactData = json_decode($deal->contact);

                    foreach ($contactData->EMAIL as $email) {
                        $this->mUnisender->exclude($email->VALUE, $listId);
                    }
            }
        });
    }

    public function getDeals($filter, $listId)
    {
        //Удаляем сделки из этого списка
        $this->mParserDeal->where('distribution_list', $listId)->delete();

        $start = 0;
        $step = 50;
        $res = $this->b24->getDeals([],[],$filter, $start);

        $total = $res['total'];

        do {
            foreach ($res['result'] as $deal) {
                try {
                    $status = $this->mParserDeal::STATUS_WAIT_GET_CONTACT;
                    $note = null;

                    if (!is_null($deal['CONTACT_ID'])) {
                        $resContact = $this->b24->getContactId($deal['CONTACT_ID']);
                        $contact = $resContact['result'];
                        $status = $this->mParserDeal::STATUS_WAIT_UPLOAD;
                    } else {
                        $contact = [];
                        $status = $this->mParserDeal::STATUS_ERROR_NOT_CONTACT;
                        $note = 'нет контакта в сделке';
                    }


                } catch (\DomainException $e) {
                    if ($e->getMessage() === 'Not found') {
                         $contact = [];
                         $status = $this->mParserDeal::STATUS_ERROR_NOT_CONTACT;
                         $note = 'нет контакта в Б24';
                    }

                } finally {
                    $this->mParserDeal->create([
                        'distribution_list' => $listId,
                        'deal_id' => $deal['ID'],
                        'deal' => json_encode($deal),
                        'contact' => json_encode($contact),
                        'status' => $status,
                        'note' => $note,
                    ]);
                }
            }
            $start += $step;
            $res = $this->b24->getDeals([], [], $filter, $start);
            sleep(1);
        } while ($total > $start);
    }

    public function getLeads($filter, $listId)
    {
        //Удаляем сделки из этого списка
        $this->mParserDeal->where('distribution_list', $listId)->delete();

        $start = 0;
        $step = 50;
        $res = $this->b24->getLead([],[],$filter, $start);

        $total = $res['total'];

        do {
            foreach ($res['result'] as $deal) {
                try {
                    $status = $this->mParserDeal::STATUS_WAIT_GET_CONTACT;
                    $note = null;

                    if (!is_null($deal['CONTACT_ID'])) {
                        $resContact = $this->b24->getContactId($deal['CONTACT_ID']);
                        $contact = $resContact['result'];
                        $status = $this->mParserDeal::STATUS_WAIT_UPLOAD;
                    } else {
                        $contact = [];
                        $status = $this->mParserDeal::STATUS_ERROR_NOT_CONTACT;
                        $note = 'нет контакта в сделке';
                    }


                } catch (\DomainException $e) {
                    if ($e->getMessage() === 'Not found') {
                        $contact = [];
                        $status = $this->mParserDeal::STATUS_ERROR_NOT_CONTACT;
                        $note = 'нет контакта в Б24';
                    }

                } finally {
                    $this->mParserDeal->create([
                        'distribution_list' => $listId,
                        'deal_id' => $deal['ID'],
                        'deal' => json_encode($deal),
                        'contact' => json_encode($contact),
                        'status' => $status,
                        'note' => $note,
                    ]);
                }
            }
            $start += $step;
            $res = $this->b24->getLead([], [], $filter, $start);
            sleep(1);
        } while ($total > $start);
    }

    public function upload($listId)
    {
        $i = 0;
        $this->mParserDeal
            ->where('distribution_list', $listId)
            ->where('status', $this->mParserDeal::STATUS_WAIT_UPLOAD)
            ->chunkById(50, function ($chunk) use ($listId, $i) {
                $data = [];
//                $i = $i + 1;
//                echo $i . ' | ' . count($chunk) . PHP_EOL;
                foreach ($chunk as $deal) {

                    $dataContact = json_decode($deal->contact);

                    $name = $dataContact->NAME;

                    if (!isset($dataContact->EMAIL)) {
                        $deal->status = $this->mParserDeal::STATUS_ERROR;
                        $deal->note = 'У контакта нет email адреса';
                        $deal->save();
                        continue;
                    }
                    if (count($dataContact->EMAIL) === 0) {
                        $deal->status = $this->mParserDeal::STATUS_ERROR;
                        $deal->note = 'У контакта нет email адреса';
                        $deal->save();
                        continue;
                    }

                    foreach ($dataContact->EMAIL as $email) {
                        $data[] = [
                            'name' => $name,
                            'email' => $email->VALUE,
                            'email_list_ids' => $listId,
                        ];

                        $deal->status = $this->mParserDeal::STATUS_SUCCESS;
                        $deal->save();
                    }
                }
                $this->mUnisender->importContacts($data, $listId);
        });
    }

    public function multipleUpload($list, $listId)
    {
        $i = 0;
        $this->mParserDeal
            ->whereIn('distribution_list', $list)
            ->where('status', $this->mParserDeal::STATUS_SUCCESS)
            ->chunkById(50, function ($chunk) use ($listId, $i) {
                $data = [];
                foreach ($chunk as $deal) {
                    $dataContact = json_decode($deal->contact);
                    $name = $dataContact->NAME;
                    foreach ($dataContact->EMAIL as $email) {
                        $data[] = [
                            'name' => $name,
                            'email' => $email->VALUE,
                            'email_list_ids' => $listId,
                        ];
                    }

                }

                $this->mUnisender->importContacts($data, $listId);
            });
    }
}
