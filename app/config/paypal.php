<?php
return array(
    // set your paypal credential
    // live
    'client_id' => 'AVoxwBDTZvvoIuW9UV_ejPrPuWU1bZ0ezt_Q854IpzdrNKKZ3p9zk_WD83tv',
    'secret' => 'EC1aYhDPrp1cWiHg0mPf8gitqRTI9DSvYys9z1LZn5IXdKuznNC5pH0smDtT',
    // sandbox
    //'client_id' => 'AWkpPxDVK1C0oHYb0XrqD9qonucaoFIzYIQemOQG-3Bb6jm2DtalcOHUEDfv',
    //'secret' => 'EOTFnxB9vRF_jXSkyWmI0VJBoZwlkepUoDemtPbEbXijMpaIHSBQGRgjYM6c',
    /**
     * SDK configurations.
     */
    'settings' => array(
        /**
         * Available option 'sandbox' or 'live'
         */
        //'mode' => 'sandbox',
		'mode' => 'live',
        /**
         * Specify the max request time in seconds
         */
        'http.ConnectionTimeOut' => 30,

        /**
         * Whether want to log to a file
         */
        'log.LogEnabled' => true,

        /**
         * Specify the file that want to write on
         */
        'log.FileName' => storage_path() . '/logs/paypal.log',

        /**
         * Available option 'FINE', 'INFO', 'WARN' or 'ERROR'
         *
         * Logging is most verbose in the 'FINE' level and decreases as you
         * proceed towards ERROR
         */
        'log.LogLevel' => 'FINE'
    ),
);
