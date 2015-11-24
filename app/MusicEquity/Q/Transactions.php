<?php namespace MusicEquity\Q;
use MyTransaction, Purchase, Download, Song, User, Charity, Log, Bundle, BundleList;
use MusicEquity\Helpers\Mailer as Mailer;
use MusicEquity\Helpers\MassPay;
use MyNotification as Notification, PayPalAdaptiveController;
class Transactions
{
    public function fire($job, $data)
    {
        // Fetching Transaction
        $t = MyTransaction::where('paypal_id', '=', $data['transaction'])->first();
        $t->status = "COMPLETE";
        $t->save();

        // Fetching songs also checking if the exists
        $songs = $t->songs;
        $bundles = $t->bundles;


        //Work with songs
        $sarr = explode(",", $songs);
        $for_sending = [];

        foreach($sarr as $s) {
            if(is_numeric($s)) {
                $song = Song::find($s);
                if(!is_null($song)) {

                    $artist = User::where('id', '=', $song->artist)->first();
                    $for_sending[$artist->id] = true;

                    // Paying out to artist
                    $fee = ($song->price/100)*46;
                    $amount = $song->price - $fee;

                    if($song->charity && $song->charity_approved == 1) {
                        $share = $song->charity_share;

                        if($share > 0) {
                            $full_payment = $amount;
                            $charity_fee = ($amount/100)*$share;
                            //$charity_to_pay = $amount - $charity_fee;
                            $amount = $amount - $charity_fee;
                            if($share == 100) {
                                $amount = 0;
                            }

                            $charity_to_pay = $full_payment - $amount;

                            $charity = Charity::find($song->charity);
                            $charity_user = User::where('charity_id', '=', $charity->id)->first();
                            $charity->donated += $charity_to_pay;
                            $charity->save();

                            //Send email to the charity
                            $m = new Mailer;
                            $m->send($charity_user, 'emails.charity_notification', [], array('subject' => 'Music Equity Payment for Charity Confirmation', 'from' => 'sales@musicequity.com'));

                            //Send email to the administrator
                            $admin_user = new User;
                            $admin_user->email = 'charity-payments@musicequity.com';
                            $m = new Mailer;
                            $m->send($admin_user, 'emails.admin_notification', ['artist' => $artist->artist_name,
                                                                                'song_cost' => $song->price,
                                                                                'sum' => $charity_to_pay,
                                                                                'charity' => $charity->name], array('subject' => 'Music Equity Payment for Charity Confirmation', 'from' => 'sales@musicequity.com'));


                            $paypal_for_charity = $charity_user->paypal;
                            //Log::info('Artist - '. $amount . ' ---- Charity - '. $charity_to_pay);
                            $p = new MassPay;
                            $result = $p->pay($charity_user->paypal, $charity_to_pay);
                        }
                    }

                    if($amount > 0) {
                        $p = new MassPay;
                        $result = $p->pay($artist->paypal, $amount);
                    }

                    // Registering new purchase of every single song
                    $p = new Purchase;
                    $p->customer = $t->customer;
                    $p->email = $data['email'];
                    $p->song = $s;
                    $p->code = $data['transaction'];
                    $p->artist = $song->artist;
                    $p->paid = $result;
                    $p->topay = $amount;
                    $p->save();



                    // Generating download links for every single song

                    $d = new Download;
                    $d->url = str_random(40);
                    $d->counter = 10;
                    $d->email = $data['email'];
                    $d->customer = $t->customer;
                    $d->song = $s;
                    $d->save();

                    // Creating temporary user
                    $tuser = new User;
                    $tuser->email = $data['email'];
                    $m = new Mailer;
                    $m->send($tuser, 'emails.download_song', ['link' => $d->url], array('subject' => 'Music Equity Song Download', 'from' => 'sales@musicequity.com'));
                    Log::info('Sending email to' . $tuser->email);
                    if($t->customer != 0) {
                        $customer = User::find($t->customer);
                    }

                    $n = new Notification;
                    $n->user = $artist->id;

                    if( @empty ( $customer -> city ) ) {
                        $city = 'Unknown location';
                    }
                    else {
                        $city = $customer->city;
                    }
                    $n->text = 'Someone in '. $city .' just bought your song (' . $song->title .')';
                    $n->save();

                }
            }
        }

        // Work with albums;
        $sarr = explode(",", $bundles);
        $for_sending_bundles = [];
        
        foreach($sarr as $s) {
            if(is_numeric($s)) {
		
                $bundle = Bundle::find($s);
                if(!is_null($bundle)) {
                    $artist = User::where('id', '=', $bundle->artist)->first();
                    $for_sending_bundles[$artist->id] = true;

                    // Paying out to artist
                    $fee = ($bundle->price/100)*46;
                    $amount = $bundle->price - $fee;

                    $p = new MassPay;
                    $result = $p->pay($artist->paypal, $amount);

                    // Registering new purchase of album
                    $p = new Purchase;
                    $p->customer = $t->customer;
                    $p->email = $data['email'];
                    $p->song = $s;
                    $p->bundle = true;
                    $p->code = $data['transaction'];
                    $p->artist = $bundle->artist;
                    $p->paid = $result;
                    $p->topay = $amount;
                    $p->save();

                    // Generating download links for every single song

                    $song_in_bundle = BundleList::where('bundle_id', '=', $bundle->id)->lists('song');
                    $url = [];
                    foreach($song_in_bundle as $sib) {
                        $d = new Download;
                        $d->url = str_random(40);
                        $d->counter = 10;
                        $d->email = $data['email'];
                        $d->customer = $t->customer;
                        $d->song = $sib;
                        $d->save();
                        $url[] = $d->url;
                    }

                    // Creating temporary user
                    $tuser = new User;
                    $tuser->email = $data['email'];
                    $m = new Mailer;
                    $m->send($tuser, 'emails.download_album', ['link' => $url], array('subject' => 'Music Equity Album Download', 'from' => 'sales@musicequity.com'));
                    Log::info('Sending email to' . $tuser->email);
                    if($t->customer != 0) {
                        $customer = User::find($t->customer);
                    }

                    $n = new Notification;
                    $n->user = $artist->id;

                    if( @empty ( $customer -> city ) ) {
                        $city = 'Unknown location';
                    }
                    else {
                        $city = $customer->city;
                    }
                    $n->text = 'Someone in '. $city .' just bought your album (' . $bundle->name .')';
                    $n->save();

                }
            }
        }

        //Send emails to artsist
        foreach ( $for_sending as $k => $v ) {
            $artist = User::find($k);
            // Sending email to the artist
            $m = new Mailer;
            $m->send($artist, 'emails.song_purchase', [], array('subject' => 'Music Equity Purchase Confirmation', 'from' => 'sales@musicequity.com'));
        }
        foreach ( $for_sending_bundles as $k => $v ) {
            $artist = User::find($k);
            // Sending email to the artist
            $m = new Mailer;
            $m->send($artist, 'emails.album_purchase', [], array('subject' => 'Music Equity Purchase Confirmation', 'from' => 'sales@musicequity.com'));
        }

        $job->delete();
    }
}
