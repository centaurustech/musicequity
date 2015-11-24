<?php

class HubController extends BaseController {

    /**
     * Show hub picture
     *
     * @return Response
     */

    public function picture($id)
    {
        $pic = Picture::find($id);

        if($pic) {
            return Response::download(storage_path()."/pictures/".$pic->name.".png");
        }
        else {
            return App::abort(404);
        };
    }

    public function show($slug){
        $hub = Hub::where('slug', '=', $slug)->first();

        if(!is_null( $hub )) {
            $artists = User::where('type', '=', 'artist')->where('hub', '=', $hub->id)->get();
            $artists_list = [];
            $songs = [];
            $events = [];
            foreach ($artists as $a) {
                $artists_list[] = $a->id;
            }
            if( count($artists_list) > 0 ) {
                $songs = Song::where('completed', '=', '1')->whereIn('artist', $artists_list)->orderBy('created_at', 'desc')->get();
                $events = ArtistEvent::whereIn('artist', $artists_list)->get();
            }


            $news = News::where('hub', '=', $hub->id)->take(3)->get();
            return View::make('hubs.main', [
                'hub' => $hub,
                'news' => $news,
                'artists' => $artists,
                'songs' => $songs,
                'events' => $events
            ]);
        }

        App::abort(404);
    }

    public function follow($slug)
    {
        $hub = Hub::where('slug', '=', $slug)->first();
        $user = Auth::user();

        if(!is_null($hub)) {
            $f = Follow::where('user', '=', $user->id)->where('hub', '=', $hub->id)->first();

            if(is_null($f)) {
                $f = new Follow;
                $f->hub = $hub->id;
                $f->user = $user->id;
                $f->save();
                return Redirect::to('hubs/' . $hub->slug);
            }
            else {
                $f->delete();
                return Redirect::to('hubs/' . $hub->slug);
            }
        }
        else {
            App::abort('404');
        }
    }
}