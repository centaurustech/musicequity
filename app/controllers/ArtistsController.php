<?php
use transloadit\Transloadit as Transloadit;
use Carbon\Carbon;
use MusicEquity\Helpers\Mailer as Mailer;
class ArtistsController extends BaseController {

    /**
     * New Event for the Artist
     *
     * @return Response
     */

    public function new_event()
    {
        $e = new ArtistEvent;

        if($e->save()) {
            return Response::json(['success' => 'true']);
        }
        else {
            return Response::json($e->errors());
        }
    }

    /**
     * New artist post-signup page
     *
     * return @Response
     */
    public function new_artist()
    {
        $user = User::find(Auth::user()->id);
        if($user) {
            $user::$rules = array();
            $user->fresh = false;
            $user->save();
        }
        return View::make('artist.registration-confirmation');
    }



    public function comment()
    {
        $song_id = Input::get('song');

        $song = Song::find($song_id);
        if($song)
        {
            $comment = new Comment;
            $comment->song = $song_id;
            $comment->user = Auth::user()->id;
            $comment->comment = Input::get('comment');
            if($comment->save()) {
                return Response::json([
                    'success' => true
                ]);
            }
            else {
                return Response::json([
                    'success' => false
                ]);
            }

        }
    }

    /**
     *  Handles User Report
     *
     * @return Response
     */

    public function report()
    {
        $r = new Report;

        if($r->save()) {

            $user = new User;
            $user->email = 'noel@musicequity.com';

            $target = User::find($r->user);
            $m = new Mailer;
            $m->send($user, 'emails.report', array('report' => $r, 'target' => $target), array('subject' => 'Music Equity Account Report', 'from' => 'reports@musicequity.com'));

            return Response::json(['success' => true]);
        }
        else {
            return Response::json($r->errors());
        }
    }

    /**
     * Show track page with comments
     *
     * @param $id
     * @return Response
     */

    public function track($id)
    {
        $song = Song::find($id);


        if($song)
        {
            $user = $song->artist;
            $comments = Comment::where('song', '=', $song->id)->orderBy('id', 'desc')->get();
            $profile = User::find($user);
            $songs = Song::where('artist', '=', $profile->id)->where('completed', '=', '1')->orderBy('id', 'desc')->get();
            $activation = Activation::where('user', '=', $profile->id)->first();

            $followers = Follow::where('artist', '=', $profile->id)->get();
            $countries = [];
            foreach ($followers as $f) {
                if (empty ($f->profile->country)) {
                    $f->profile->country = 'Country unknown';
                }
                if (isset($countries [$f->profile->country])) {
                    $countries [$f->profile->country]++;
                } else {
                    $countries [$f->profile->country] = 1;
                }
            }
            if($activation) {
                return View::make('artist.not-activated');
            }
            else {
                if($profile)
                {

                    $events = ArtistEvent::where('artist', '=', $profile->id)->where('date', '>', \Carbon\Carbon::now())->orderBy('id', 'desc')->take(3)->get();
                    $songs_in = [];
                    $songs = Song::where('artist', '=', $profile->id)->where('completed', '=', '1')->get();
                    foreach($songs as $s) {
                        $songs_in[] = $s->id;
                    }
                    $comments_block = Comment::whereIn('song', $songs_in)->orderBy('id', 'desc')->take(3)->get();
                    return View::make('artist.track-new', ['profile'=>$profile, 'song' => $song, 'comments' => $comments_block, 'comments_this' => $comments, 'events' => $events, 'songs' => $songs,  'countries' => $countries]);
                }
                else App::abort(404);
            }

        }
        else
        {
            App::abort('404');
        }
    }

    public function create_album()
    {
        $bundle = new Bundle;
        $bundle->artist = Auth::user()->id;
        $bundle->price = Input::get('price');
        $bundle->name = Input::get('name');
        $bundle->save();

        foreach(Input::get('songs') as $v) {
            $list = new BundleList;
            $list->bundle_id = $bundle->id;
            $list->song = $v;
            $list->save();
        }

        return Response::json([
           'success' => true
        ]);
    }

    public function delete_album($id)
    {
        $bundle = Bundle::find($id);
        if(!is_null($bundle)) {
            if($bundle->artist != Auth::user()->id) {
                return Redirect::to('/');
            }
            else {
                $bundle->delete();
                return Redirect::to('/artist/' . Auth::user()->profile_url);
            }
        }
    }

    /**
     * Show artist profile
     * 
     * return @Response
     */

    public function showprofile( $user )
    {
        $profile = User::where('profile_url', '=', $user)->where('approved', '=', '0')->where('deleted_at', '=', null)->first();
        if(! is_null( $profile )) {
            $activation = Activation::where('user', '=', $profile->id)->first();
            $page = Page::where('title', '=', 'faq-artist')->first();
            $transactions = Purchase::where('artist', '=', $profile->id)->get();

            if($activation) {
                return View::make('artist.not-activated');
            }
            else {
                if($profile)
                {
                    if($profile->active == 1) {
                        $songs = Song::where('artist', '=', $profile->id)->where('completed', '=', '1')->orderBy('id', 'desc')->paginate(10);
                        $bundles = Bundle::where('artist', '=', $profile->id)->get();
                        $wall = new \Illuminate\Database\Eloquent\Collection();
                        $wall = $wall->merge($songs);
                        $wall = $wall->merge($bundles);
                        //dd($wall);

                        $followers = Follow::where('artist', '=', $profile->id)->get();
                        $countries = [];
                        foreach ($followers as $f) {
                            if( empty ( $f->profile->country ) ) {
                                $f->profile->country = 'Country unknown';
                            }
                            if(isset($countries [ $f->profile->country ])) {
                                $countries [ $f->profile->country ] ++;
                            }
                            else {
                                $countries [ $f->profile->country ] = 1;
                            }
                        }



                        $events = ArtistEvent::where('artist', '=', $profile->id)->where('date', '>', \Carbon\Carbon::now())->orderBy('id', 'desc')->take(3)->get();
                        $songs_in = [];
                        foreach($songs as $s) {
                            $songs_in[] = $s->id;
                        }
                        if(count($songs_in) > 0) {
                            $comments = Comment::whereIn('song', $songs_in)->orderBy('id', 'desc')->take(3)->get();
                        }
                        else $comments = "";

                        $wall->sortByDesc('created_at');
                        //dd($wall);
                        $notifications = MyNotification::where( 'user', '=', $profile->id )->get();
                        return View::make('artist.profile-new', [
                            'profile'=>$profile,
                            'songs' => $songs,
                            'events' => $events,
                            'comments'=>$comments,
                            'notifications' => $notifications,
                            'wall' => $wall,
                            'page' => $page,
                            'transactions' => $transactions,
                            'countries' => $countries
                        ]);
                    }
                    else {
                        return Redirect::to('profile/settings');
                    }

                }
                else App::abort(404);
            }

        }
        else App::abort(404);



    }

    public function follow($artist)
    {
        $artist = User::where('profile_url', '=', $artist)->first();
        $user = Auth::user();
        if(!is_null($artist)) {
            $f = Follow::where('user', '=', $user->id)->where('artist', '=', $artist->id)->first();

            if(is_null($f)) {
                $f = new Follow;
                $f->artist = $artist->id;
                $f->user = $user->id;
                $f->save();
                return Redirect::to('artist/' . $artist->profile_url);
            }
            else {
                $f->delete();
                return Redirect::to('artist/' . $artist->profile_url);
            }
        }
        else {
            App::abort('404');
        }





    }

    public function postimage()
    {

        $image = Input::get('file');
        $selection = Input::get('selection');
        $exp = explode(",", $image);
        $data = base64_decode($exp[1]);
        $name = str_random(15);
        $tempfile = storage_path()."/temp/".$name."";
        file_put_contents($tempfile, $data);
        $image_info = getImageSize($tempfile);
        switch ($image_info['mime']) {
            case 'image/gif':
                $extension = '.gif';
                break;
            case 'image/jpeg':
                $extension = '.jpg';
                break;
            case 'image/png':
                $extension = '.png';
                break;
            default:
                // handle errors
                break;
        }

        // open file a image resource
        $img = Image::make($tempfile);


        $img->save($tempfile);

        $large = Image::make($tempfile)->resize(210, 210)->save( storage_path()."/pictures/".$name."-large".$extension );
        $medium = Image::make($tempfile)->resize(50, 50)->save( storage_path()."/pictures/".$name."-medium".$extension );
        $small = Image::make($tempfile)->resize(22, 22)->save( storage_path()."/pictures/".$name."-small".$extension );

        unlink($tempfile);
        $pic = new Picture;
        $pic->name = $name;
        $pic->user = Auth::user()->id;
        $pic->extension = $extension;
        $pic->save();

        return Response::json(
            [
                'id' => $pic->id,
                'success' => true
            ]
        );
    }

    /**
     * Delete web link by id
     *
     * @param $id
     * @return Response
     */
    public function delete_link($id)
    {
        $l = WebLink::find($id);
        if($l) {
            if($l->user == Auth::user()->id) {
                $l->delete();
                return Response::json([
                    'success' => true
                ]);
            }
        }
        return Response::json([
            'success' => false
        ]);
    }

    /**
     * Handling save profile form
     *
     * @return Response
     */

    public function saveprofile()
    {
        $input = Input::all();
        $id = Auth::user()->id;

        $geo = Geocoder::getCoordinatesForQuery($input['city'].', '.$input['country'].', '. $input['zip']);



        $profile = User::find($id);
        $profile->artist_name = $input['artist_name'];
        $profile->name = $input['name'];
        $profile->country = $input['country'];
        $profile->city = $input['city'];
        $profile->zip = $input['zip'];
        $profile->bio = $input['bio'];
        $profile->phone = $input['phone'];
        $profile->hub = $input['hub'];

        $profile->promo = $input['promo'];
        $profile->profile_url = Str::slug($input['artist_name']);
        $profile->picture = $input['picture_id'];
        $profile->paypal = $input['paypal'];
        if(Input::has('active')) {
            $profile->active = $input['active'];
        }
        $profile->links = $input['links'];
        if($geo != 'NOT_FOUND') {
            $profile->lat = $geo['lat'];
            $profile->lng = $geo['lng'];
        }



        if(Input::has('web')){
            foreach($input['web'] as $w) {
                $link = new WebLink;
                $link->url = $w;
                $link->user = $id;
                $parse = parse_url($w);
                $link->title = $parse['host'];
                $link->save();
            }
        }



        //Disabling validation rules
        $profile::$rules['email'] = '';
        $profile::$rules['agree'] = '';
        $profile->autoHashPasswordAttributes = false;

        if($profile->save(
            [
                'artist_name' => 'required|unique:users,artist_name,' . $profile->id,
                'profile_url' => 'unique:users,profile_url,'  . $profile->id,
                'paypal' => 'required|email'
            ]
        ))
        {
            return Redirect::to('/artist/'  . $profile->profile_url);
        }
        else {
            Input::flash();
            return Redirect::to( 'profile/settings' )->withInput()->with('errors', $profile->errors()->all());
        }




    }

    /**
     * Displaying artist profile settings form
     *
     * @return Response
     */
    public function profile()
    {
        $profile = User::find(Auth::user()->id);
        $hubs = Hub::all();
        return View::make('artist.settings', ['profile' => $profile, 'hubs' => $hubs]);
    }

    /**
     * Handle song submission
     *
     * @return Response
     */

    public function upload()
    {



        $rules = array(
            'song' => 'max:61440',
        );

        $validation = Validator::make(Input::all(), $rules);

        if ($validation->fails())
        {
            return Response::make($validation->errors->first(), 400);
        }
        $file = Input::file('song');


        if(count($file) > 0) {



            $extension = $file->getClientOriginalExtension();
            $directory = public_path().'/uploads/'.sha1(time());
            $filename = sha1(time().time()).".{$extension}";

            $name = $file->getClientOriginalName();
            $upload_success = $file->move($directory , $filename);
            if( $upload_success ) {

                $song = new Song;
                $song->path = $directory;
                $song->original_name =  $filename;
                $song->artist = Auth::user()->id;
                $song->title = $name;
                $song->save();
                Session::put('song', $song->id);


                // Generating waveform
                $transloadit = new Transloadit(array(
                    'key' => '8b6e8f905c0811e4b45af39f2111cd0b',
                    'secret' => '75b297187e5676cb0e4b6ac12b11ba550bb51082',
                ));

                $response = $transloadit->createAssembly(array(
                    'files' => array($directory . "/" .  $filename),
                    'wait' => true,

                    'params' => array(
                        'steps' => array(
                            'mp3' => array(
                                'robot' => '/audio/encode',
                                'preset' => "mp3",
                                'result' => true,
                                'ffmpeg'=> array (
                                    'ss' => "00:00:00.0",
                                    't' => "00:00:30"
                                )



                            ),
                            'waveform' => array (
                                'robot' => "/audio/waveform",
                                'use' => ":original",
                                'width' => 295,
                                'height' => 55,
                                'background_color' => "ffffffff",
                                'outer_color' => "607BA9aa",
                                'center_color' => "607BA9aa",
                                'result' => true
                            )
                        ),
                        'notify_url' =>  URL::to('/') . '/notify'
                    ),
                ));



                $assembly = $response->data['assembly_id'];
                $a = new Assembly;
                $a->assembly = $assembly;
                $a->song = $song->id;
                $a->save();
            }
            if( $upload_success ) {
                return Response::json('success', 200);
            } else {
                return Response::json('error', 400);
            }

        }
        else {
            return Response::json('error', 400);
        }


    }

    /**
     * Returns the soundwave for the song
     * 
     * @return Response
     */

    public function showthumbnail($id)
    {
        $song = Song::withTrashed()->find($id);

        if($song)
        {
            return Response::download($song->path . "/thumbnail.png");
        }
        else {
            return App::abort(404);
        }
    }



    /**
     * Displaying form for initial song set up
     *
     * @return Response
     */

    public function songinfo()
    {
        if(Session::get('song'))
        {
            //
            $charity = User::where('type', '=', 'charity')->get();

            $song = Song::find(Session::get('song'));
            return View::make('artist.song-init', $song, [
                'charity_list' => $charity
            ]);
        }
        else return Redirect::to('/');

    }

    /**
     * Song Edit
     *
     * @return Response
     */

    public function edit_song($id)
    {

        $charity = User::where('type', '=', 'charity')->where('charity_id', '>', '0')->get();


        $song = Song::find($id);
        if(!is_null($song)){
            if($song->artist == Auth::user()->id) {
                Session::put('song', $id);
                return View::make('artist.song-init', $song, [
                    'charity_list' => $charity,
                    'song' => $song
                ]);
            }
            else {
                return Redirect::to('/');
            }
        }
        else {
            return Redirect::to('/');
        }



    }

    /**
     * Function that checks if Transloadit assembly returned any results yet
     *
     * @return Response
     */

    public function check_assembly() {

        $song = Session::get('song');

        $s = Song::find($song);

        if($s->artist != Auth::user()->id) {
            die(':(');
        }

        $a = Assembly::where('song', '=', $song)->first();

        if(!is_null($a)) {
            if(Carbon::now()->subMinutes(3) > $a->created_at) {
                $a->delete();
                return Response::json(['failed' => 'true']);
            }
        }


        if($a) {
            return Response::json(['progress' => 'true'], 200);
        }
        else {
            return Response::json(['done' => 'true', 'duration' => $s->duration], 200);
        }

    }

    /**
     * Delete song from the database
     *
     * @param $id
     * @return Response
     */
    public function delete_song($id)
    {
        $song = Song::find($id);
        if(!is_null($song)) {
            if($song->author->id != Auth::user()->id) {
                return Redirect::to('/');
            }
            else {
                $song->delete();
                return Redirect::to('/artist/' . Auth::user()->profile_url);
            }
        }
     }

    /**
     *  Like song by id
     *
     * @return Response
     */
    public function like($id)
    {
        if (Auth::check())
        {
            $song = Song::find($id);
            if($song->exists()) {
                $like = Like::where('user', "=", Auth::user()->id)->where('song', '=', $id)->first();
                if($like) {
                    $like->delete();
                    return Response::json(['like'=>false]);
                }
                else {
                    $like = new Like;
                    $like->user = Auth::user()->id;
                    $like->song = $id;
                    $like->save();
                    return Response::json(['like'=>true]);
                }
            }
            else App::abort('404');
        }
        else {
            return Response::json(['auth'=>false]);
        }


    }

    /**
     * Returns art by songs id
     *
     * @param $id
     * @return Response
     */

    public function show_art($id) {
        $song = Song::find($id);
        $author = User::find($song->artist);
        $pic = Picture::find($author->picture);
        if($song) {
            if(!empty($song->art)) {
                return Response::download($song->art);
            }
            else {
                return Response::download(storage_path()."/pictures/".$pic->name."-medium".$pic->extension);
                //return Response::download(storage_path() . "/cd.png");
            }
        }

    }



    /**
     *  Add hit to the song
     *
     * @return Response
     */

    public function add_hit($id)
    {
        $song = Song::find($id);
        if($song) {
            $song->hits++;
            $song->save();
            return Response::json(['success' => true]);
        }
        else App::abort('404');
    }

    /**
     * Returns the song mp3 by id
     *
     * @param $id
     * @return Response
     */

    public function song_download($id) {
        $song = Song::withTrashed()->find($id);
        if($song) {
            return Response::download($song->path . "/". $song->sample. ".mp3");
        }
        else App::abort('404');

    }

    /**
     * Callback functions for transloadit proccessor
     *
     *
     * @return Response
     */

    public function notify()
    {
        $response = json_decode(Input::get('transloadit'));


        @$a = Assembly::where('assembly', '=', $response->assembly_id)->first();

        if($a)
        {
            $s = Song::find($a->song);

            /// Processing big image
            $image = $response->results->waveform[0]->url;
            $imgs3 = file_get_contents($image);
            file_put_contents($s->path . "/thumbnail" . ".png", $imgs3);



            // Processing mp3
            $duration   = $response->results->waveform[0]->meta->duration;
            $mp3name = substr(md5(microtime()),0,150);
            $s->sample = $mp3name;
            $duration = number_format($duration, 2);
            $s->duration = $duration;
            $s->save();
            $mp3 = $response->results->mp3[0]->url;

            $dsong = file_get_contents($mp3);
            file_put_contents($s->path . "/" . $mp3name.".mp3", $dsong);



            $a->delete();



        }

        return Response::json('success', 200);

    }

    /**
     * Handle song initialisation submit form
     */

    public function addsong()
    {
        $input = Input::all();
        $song_id = $input['song'];
        $song = Song::find($song_id);

        if($song->artist != Auth::user()->id ) {
            return Redirect::to('/');
        }
        else {

            if(Input::has('art_cropped')) {
                $image = Input::get('art_cropped');
                $exp = explode(",", $image);
                $data = base64_decode($exp[1]);
                $name = str_random(15);
                $tempfile = storage_path()."/temp/".$name."";
                file_put_contents($tempfile, $data);
                $image_info = getImageSize($tempfile);
                switch ($image_info['mime']) {
                    case 'image/gif':
                        $extension = '.gif';
                        break;
                    case 'image/jpeg':
                        $extension = '.jpg';
                        break;
                    case 'image/png':
                        $extension = '.png';
                        break;
                    default:
                        // handle errors
                        break;
                }

                // open file a image resource
                $img = Image::make($tempfile);


                $img->save($tempfile);

                $large = Image::make($tempfile)->resize(120, 120)->save( $song->path . "/" . $song->sample . "-large".$extension );
                $small = Image::make($tempfile)->resize(50, 50)->save(  $song->path . "/" . $song->sample . "-small".$extension );
                $song->art = $song->path . "/" . $song->sample . "-large".$extension;
                unlink($tempfile);
            }




            $song->genre = $input['genre'];
            $song->title = $input['title'];
            $song->price = $input['price'];
            $song->description = $input['description'];
            $song->completed = true;
            if(Input::has('charity')) {
                if(Input::get('charity_share') > 0)
                {
                    if(  is_numeric( Input::get('charity_share') ) )
                    {
                        $charity = Charity::find($input['charity']);
                        if($charity) {
                            $song->charity_share = $input['charity_share'];
                            $song->charity = $input['charity'];
                        }

                    }
                }
            }
            $song->save();
            Session::forget('song');

        }




        return Redirect::to('/artist/' . Auth::user()->profile_url);
    }

}