<?php

namespace Modules\Perspektivakrym\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ProfitXmlController extends Controller
{
    protected $url;
    protected $xmlFileName;
    protected $dir;

    public function __construct()
    {
        $this->url = config('perspektivakrym.profitbase_get_xml');
        $this->xmlFileName = config('perspektivakrym.profitbase_xml_file');
        $this->dir = config('perspektivakrym.dir');
    }

    public function getXml()
    {
        try {
            $ch = curl_init($this->url);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_TIMEOUT, 300);
            curl_setopt($ch, CURLOPT_USERAGENT, 'any');
            $res = curl_exec($ch);
            curl_close($ch);
            if($res){
                Storage::put($this->dir . '/' . $this->xmlFileName, $res);
                return '';
            } else {
                throw new \DomainException(curl_error($ch));
            }
        } catch (\Exception $e) {
            Log::error('Perspektivakrym: ' . $e->getMessage());
            return '';
        }
    }

    public function parseXml()
    {
        $file = storage_path('/app/' . $this->dir . '/' . $this->xmlFileName);

//        dd($file);
        $obj = simplexml_load_file($file);

        $data = json_decode(json_encode($obj), TRUE);

        foreach ($data['offer'] as $value) {
            $internalId = $value['@attributes'];
            dd($internalId);
        }


        dd($obj);
    }
}
