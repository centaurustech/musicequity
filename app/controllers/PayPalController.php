<?php
use PayPal\Rest\ApiContext;
use PayPal\Auth\OAuthTokenCredential;
use PayPal\Api\Amount;
use PayPal\Api\Details;
use PayPal\Api\Item;
use PayPal\Api\ItemList;
use PayPal\Api\Payer;
use PayPal\Api\Payment;
use PayPal\Api\RedirectUrls;
use PayPal\Api\ExecutePayment;
use PayPal\Api\PaymentExecution;
use PayPal\Api\Transaction;

class PayPalController extends BaseController
{

    private $_api_context;

    public function __construct()
    {

        // setup PayPal api context
        $paypal_conf = Config::get('paypal');
        //dd($paypal_conf);
        $this->_api_context = new ApiContext(new OAuthTokenCredential($paypal_conf['client_id'], $paypal_conf['secret']));
        $this->_api_context->setConfig($paypal_conf['settings']);
    }


    public function checkout()
    {
        $payer = new Payer();
        $payer->setPaymentMethod('paypal');

        $items = [];
        $session = Session::get('cart.songs');
        $bundles = Session::get('cart.bundles');

        $total = 0;
        if((count($session) == 0) && (count($bundles) == 0)) {
            die('No Items in you cart');
        }

        $songs_string = "";
        $bundles_string = "";

        if(count($session) != 0)
        {
            foreach($session as $s) {
                $song = Song::find($s['id']);
                $item = new Item();
                $item->setName($song->title)
                    ->setCurrency('AUD')
                    ->setQuantity(1)
                    ->setPrice($song->price);
                $items[] = $item;
                $total += $song->price;
                $songs_string .= ",".$song->id;
            }
        }

        if(count($bundles) != 0) {
            foreach($bundles as $s) {
                $bundles = Bundle::find($s['id']);
                $item = new Item();
                $item->setName($bundles->name)
                    ->setCurrency('AUD')
                    ->setQuantity(1)
                    ->setPrice($bundles->price);
                $items[] = $item;
                $total += $bundles->price;
                $bundles_string .= ",".$bundles->id;
            }
        }

        $item_list = new ItemList();
        $item_list->setItems($items);

        $amount = new Amount();
        $amount->setCurrency('AUD')
            ->setTotal($total);

        $transaction = new Transaction();
        $transaction->setAmount($amount)
            ->setItemList($item_list)
            ->setDescription('Music Equity Song Purchase');

        $redirect_urls = new RedirectUrls();
        $redirect_urls->setReturnUrl(URL::route('payment.status'))
            ->setCancelUrl(URL::route('payment.status'));

        $payment = new Payment();
        $payment->setIntent('Sale')
            ->setPayer($payer)
            ->setRedirectUrls($redirect_urls)
            ->setTransactions(array($transaction));

        try {
            $payment->create($this->_api_context);
        } catch (\PayPal\Exception\PPConnectionException $ex) {
            if (\Config::get('app.debug')) {
                echo "Exception: " . $ex->getMessage() . PHP_EOL;
                $err_data = json_decode($ex->getData(), true);
                exit;
            } else {
                die('Some error occur, sorry for inconvenient');
            }
        }

        foreach($payment->getLinks() as $link) {
            if($link->getRel() == 'approval_url') {
                $redirect_url = $link->getHref();
                break;
            }
        }

        $t = new MyTransaction;
        $t->amount = $total;

        if(Auth::check()) {
            $t->customer = Auth::user()->id;
        }
        else {
            $t->customer = '0';
        }
        $t->songs = $songs_string;
        $t->bundles = $bundles_string;
        $t->status = 'OPEN';
        $t->paypal_id = $payment->getId();
        $t->save();

        // add payment ID to session
        Session::put('paypal_payment_id', $payment->getId());

        if(isset($redirect_url)) {
            // redirect to paypal
            return Redirect::away($redirect_url);
        }
    }

    public function status()
    {
        // Get the payment ID before session clear
        $payment_id = Session::get('paypal_payment_id');

        // clear the session payment ID
        Session::forget('paypal_payment_id');



        $payment = Payment::get($payment_id, $this->_api_context);

        // PaymentExecution object includes information necessary
        // to execute a PayPal account payment.
        // The payer_id is added to the request query parameters
        // when the user is redirected from paypal back to your site
        $execution = new PaymentExecution();
        $execution->setPayerId(Input::get('PayerID'));

        //Execute the payment
        $result = $payment->execute($execution, $this->_api_context);
        if(Auth::check()) {
            $profile = 'c/' . Auth::user()->profile_url;
        }
        else {
            $profile = 'purchase/' . $payment_id;
        }
        if ($result->getState() == 'approved') { // payment made
            Session::forget('cart.songs');
            Session::forget('cart.bundles');
            Queue::push('\MusicEquity\Q\Transactions', [
                'transaction' => $payment_id,
                'email' => $result->payer->payer_info->email
            ]);
        
            return Redirect::to($profile)
                ->with('payment', 'true');
        }
        return Redirect::route('/')
            ->with('error', 'Payment failed');
    }

}