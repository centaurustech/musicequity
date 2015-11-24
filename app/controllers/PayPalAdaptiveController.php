<?php

class PayPalAdaptiveController extends \BaseController {
    private $service;

    public function __construct(){
        $config = array (
            'mode' => 'live' ,
            'acct1.UserName' => 'noel_api1.musicequity.com',
            'acct1.Password' => 'FUQMAR2W5RNQS9UL',
            'acct1.Signature' => 'AQU0e5vuZCvSg-XJploSa.sGUDlpA9HWmNnsvglf4jSmy-m.wNUqxjzT',
            'acct1.AppId' => "APP-4SJ28329XJ8050108",
        );
        $this->service = new PayPalAPIInterfaceServiceService($config);
	}

    public function pay()
    {

        $massPayRequest = new MassPayRequestType();
        $massPayRequest->MassPayItem = array();

        $masspayItem = new MassPayRequestItemType();

        $masspayItem->Amount = new BasicAmountType('AUD', 1.10);

        $masspayItem->ReceiverEmail = 'frankieloud@gmail.com';

        $massPayRequest->MassPayItem[] = $masspayItem;

        $massPayReq = new MassPayReq();
        $massPayReq->MassPayRequest = $massPayRequest;

        $massPayResponse = $this->service->MassPay($massPayReq);

        dd($massPayResponse);
    }

}