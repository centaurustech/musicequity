<?php namespace MusicEquity\Helpers;
use \PayPalAPIInterfaceServiceService, \MassPayRequestType, \MassPayRequestItemType, \BasicAmountType, \MassPayReq, \Log;
class MassPay {
    public function __construct(){
        $config = array (
            'mode' => 'live' ,
            'acct1.UserName' => 'noel_api1.musicequity.com',
            'acct1.Password' => 'FUQMAR2W5RNQS9UL',
            'acct1.Signature' => 'AQU0e5vuZCvSg-XJploSa.sGUDlpA9HWmNnsvglf4jSmy-m.wNUqxjzT',

            //"acct1.AppId" => "APP-7UU59421A93980249",
        );
        $this->service = new PayPalAPIInterfaceServiceService($config);
    }

    public function pay($to, $amount)
    {
        $amount = round($amount, 2);
        $massPayRequest = new MassPayRequestType();
        $massPayRequest->MassPayItem = array();

        $masspayItem = new MassPayRequestItemType();

        $masspayItem->Amount = new BasicAmountType('AUD', $amount);

        $masspayItem->ReceiverEmail = $to;

        $massPayRequest->MassPayItem[] = $masspayItem;

        $massPayReq = new MassPayReq();
        $massPayReq->MassPayRequest = $massPayRequest;

        $massPayResponse = $this->service->MassPay($massPayReq);
        if($massPayResponse->Ack == 'Success')
        {
            return true;
        }
        else return false;
    }
}