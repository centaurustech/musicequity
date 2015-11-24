<?php

class CustomerController extends \BaseController {

    /**
     * Show form after quick purchase
     *
     * @param $code
     * @return mixed
     */
    public function purchase_msg($code)
    {
        $t = Purchase::where('code', '=', $code)->get();
        if(  count( $t )  > 0) {
            return View::make('customer.purchase', [
                'email' => $t[0]->email,
                'code' => $code
            ]);
        }
        else {
            App::abort('404');
        }
    }



    /**
     * Show welcome message after registration
     *
     * @return Response
     */
    public function new_customer()
    {
        return View::make('customer.registration-confirmation');
    }

    /**
     * Display customer profile
     *
     * @param $profile
     * @return Response
     */

    public function show($profile)
    {
        $p = User::where('profile_url', '=', $profile)->where('approved', '=', '0')->first();
        $page = Page::where('title', '=', 'faq-customer')->first();
        $follow = Follow::where('user', $p->id)->where('hub', '=', 0)->get();
        $follow_hub = Follow::where('user', $p->id)->where('artist', '=', 0)->get();
        $wall = new \Illuminate\Database\Eloquent\Collection();
        $events = new \Illuminate\Database\Eloquent\Collection();
        $comments = Comment::where('user', '=', $p->id)->orderBy('created_at', 'desc')->get();
        $hidden = unserialize( Cookie::get('hide') );
        //dd( Cookie::get('hide') );
        if(count($follow) > 0) {
            foreach($follow as $f) {
                $s = Song::where('artist', '=', $f->artist)->where('completed', '=', '1')->get();

                $e = ArtistEvent::where('artist', '=', $f->artist)->where('date', '>', \Carbon\Carbon::now())->get();
                $wall = $wall->merge($s);
                $events = $events->merge($e);
            }
        }

        if(count($follow_hub) > 0) {
            foreach($follow_hub as $h) {
                $hub = Hub::where('id', '=', $h->hub)->first();
                if(!is_null( $hub )) {
                    $artists = User::where('type', '=', 'artist')->where('hub', '=', $hub->id)->get();
                    $artists_list = [];
                    $songs = [];
                    $events = [];
                    foreach ($artists as $a) {
                        $artists_list[] = $a->id;
                    }
                    if (count($artists_list) > 0) {
                        $songs = Song::where('completed', '=', '1')->whereIn('artist', $artists_list)->orderBy('created_at', 'desc')->get();
                        $events = ArtistEvent::whereIn('artist', $artists_list)->get();
                    }
                    $news = News::where('hub', '=', $hub->id)->take(3)->get();
                    $wall = $wall->merge($songs);
                    $events = $events->merge($events);
                }
            }
        }

        $purchased = Purchase::where('customer', '=', $p->id)->get();
        foreach ($purchased as $pp) {
            $song_purchased = Song::withTrashed()->where('id', '=', $pp->song)->get();
            $download = Download::where('customer', '=', $p->id)->where('song', '=', $pp->song)->first();
            $song_purchased[0]->purchased = true;
            if (isset($download)) {
                $song_purchased[0]->link = $download->url;
            }
            $wall = $wall->merge($song_purchased);
        }
        $wall->sortByDesc('created_at');
        if(!isset($news)) $news = null;
        return View::make('customer.profile-new', [
            'profile' => $p,
            'wall' => $wall,
            'page' => $page,
            'events' => $events,
            'comments' => $comments,
            'hidden' => $hidden,
            'news' => $news
        ]);
    }

    public function viewcart()
    {
        $songs = Session::get('cart.songs');
        $response = Response::json($songs);
        return $response;

    }

    public function viewcart_bundle()
    {
        $bundles = Session::get('cart.bundles');
        $response = Response::json($bundles);
        return $response;

    }

    /**
     * Remove song from a cart
     *
     * @param $song
     * @return Response
     */

    public function remove_from_cart($song)
    {
        $session = Session::pull('cart.songs');
        $ss = Song::find($song);
        if(count($session) > 0) {
            foreach($session as $s) {
                if($s['id'] != $song)
                {

                    $data = [
                        'id' => $s['id'],
                        'title' => $s['title'],
                        'price' => $s['price']
                    ];
                    Session::push('cart.songs', $data);
                }
            }

        }
        return Response::json(['id'=>$song, 'price' => $ss->price]);
    }

    public function remove_bundle_from_cart($bundle)
    {
        $session = Session::pull('cart.bundles');
        $ss = Bundle::find($bundle);
        if(count($session) > 0) {
            foreach($session as $s) {
                if($s['id'] != $bundle)
                {

                    $data = [
                        'id' => $s['id'],
                        'title' => $s['title'],
                        'price' => $s['price']
                    ];
                    Session::push('cart.bundles', $data);
                }
            }

        }
        return Response::json(['id'=>$bundle, 'price' => $ss->price]);
    }

    /**
     * Add song to a cart
     *
     * @param $song
     * @return Response
     */

    public function add_to_cart($song)
    {
        $song = Song::find($song);
        if(!is_null($song)) {
            $song_title = preg_replace('%[^A-Za-z0-9 ]%', '', $song->title);
            $data = [
                'id' => $song->id,
                'title' => $song_title,
                'price' => $song->price
            ];
            Session::push('cart.songs', $data);
            return Response::json([
                'id' => $song->id,
                'price' => $song->price
            ]);
        }
        else {
            App::abort('404');
        }



    }


    public function add_bundle_to_cart($bundle)
    {
        $bundle = Bundle::find($bundle);
        if(!is_null($bundle)) {
            $data = [
                'id' => $bundle->id,
                'title' => $bundle->name,
                'price' => $bundle->price
            ];
            Session::push('cart.bundles', $data);
            return Response::json([
                'id' => $bundle->id,
                'price' => $bundle->price
            ]);
        }
        else {
            App::abort('404');
        }



    }


    /**
     * Show customer settings
     *
     * @return Response
     */
    public function settings()
    {
        $profile = Auth::user();
        return View::make( 'customer.settings', ['profile' => $profile] );
    }

    public function hide_elem($id)
    {
        $hidden = unserialize(Cookie::get('hide'));
        $hidden[] = $id;
        $hide = Cookie::forever('hide', serialize($hidden));

        return Redirect::to('/c/' . Auth::user()->profile_url)->withCookie($hide);;
    }

    public function reset_wall()
    {
        $cookie = Cookie::forget('hide');
        return Redirect::to('/c/' . Auth::user()->profile_url)->withCookie($cookie);;
    }

    public function post_settings()
    {
        $id = Auth::user()->id;

        $user = User::find($id);
        $user->name = Input::get('name');
        $user->country = Input::get('country');
        $user->city = Input::get('city');
        $user->zip = Input::get('zip');
        $user->phone = Input::get('phone');
        $user->picture = Input::get('picture_id');

        //Disabling validation rules
        $user::$rules['email'] = '';
        $user::$rules['agree'] = '';
        $user::$rules['profile_url'] = '';
        $user->autoHashPasswordAttributes = false;

        if($user->save())
        {
            return Redirect::to('c/'. $user->profile_url);
        }
        else {
            return Response::json( $user->errors()->all() );
        }






    }
}