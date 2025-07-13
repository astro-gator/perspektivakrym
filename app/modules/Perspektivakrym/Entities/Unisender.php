<?php


namespace Modules\Perspektivakrym\Entities;


use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use Unisender\ApiWrapper\UnisenderApi;

class Unisender
{
    protected $unisenser;
    public function __construct()
    {
        $apiKey = config('perspektivakrym.unisender.key');
        $encoding = config('perspektivakrym.unisender.encoding');
        $retryCount = config('perspektivakrym.unisender.retryCount');
        $timeout = config('perspektivakrym.unisender.timeout');
        $compression = config('perspektivakrym.unisender.compression');
        $platform = config('perspektivakrym.unisender.platform');

        $this->unisender = new UnisenderApi($apiKey, $encoding, $retryCount, $timeout, $compression, $platform);
    }

    //19828748  -- 19828748
    public function getLists()
    {
        $client = new Client([
            'base_uri' => 'https://api.unisender.com/ru/api/',
            'timeout' => 30.0
        ]);

        $url = 'getLists?format=json&api_key=' . config('perspektivakrym.unisender.key');

        $res = $client->request('GET', $url);

        $stringBody = (string) $res->getBody();
        $data = json_decode($stringBody);

        dd($data);
    }

    public function importContacts($users, $listId)
    {
        $client = new Client([
            'base_uri' => 'https://api.unisender.com/ru/api/',
            'timeout' => 30.0
        ]);

        $url = 'importContacts?format=json&api_key=' . config('perspektivakrym.unisender.key');
        $url .= '&field_names[0]=email&field_names[1]=Name&field_names[2]=email_list_ids';

        foreach ($users as $key => $value) {

            $url .= '&data[' . $key . '][0]=' . $value['email'] . '&data[' . $key . '][1]=' . $value['name'] . '&data[' . $key . '][2]=' . $listId;
        }


        $res = $client->request('POST', $url);

        $stringBody = (string) $res->getBody();
        $data = json_decode($stringBody);
    }

    public function exclude($email, $listId)
    {
//        https://api.unisender.com/ru/api/exclude?format=json&api_key=KEY&contact=test@example.org&contact_type=
//        TYPE&list_ids=134,135
        $client = new Client([
            'base_uri' => 'https://api.unisender.com/ru/api/',
            'timeout' => 30.0
        ]);

        $url = 'exclude?format=json&api_key=' . config('perspektivakrym.unisender.key');
        $url .= '&contact=' . $email . '&contact_type=email&list_ids=' . $listId;

        $res = $client->request('POST', $url);

        $stringBody = (string) $res->getBody();
        $data = json_decode($stringBody);
    }


}
