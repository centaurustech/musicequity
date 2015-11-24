<?php

class MainController extends BaseController {

    public function home()
    {
        $top = Song::where('completed', '=', '1')->take(10)->orderBy('hits', 'desc')->get();
        $news = News::where('status', '=', '1')->orderBy('id', 'desc')->get();
        $u = User::where('lat', '<>', '')->where('lng', '<>', '')->get();
        $hubs = Hub::where('location', '<>', '')->get();
        $charity = User::where('charity_id', '<>', '0')->take(5)->orderBy('id', 'desc')->get();
        $featured = Featured::take(3)->orderBy('id', 'desc')->get();
        $links = MenuLink::all();
        $box = Ads::all();
        return View::make('index', [
            'top_tracks' => $top,
            'location' => GeoIP::getLocation(),
            'news' => $news,
            'users' => $u,
            'charity_list' => $charity,
            'links' => $links,
            'featured' => $featured,
            'box' => $box,
            'hubs' => $hubs
        ]);
    }


    public function news($id)
    {
        $n = News::find($id);
        if(!is_null($n)) {
            return View::make('news',
                [
                    'n' => $n
                ]);
        }
        else {
            App::abort('404');
        }
    }

    public function page($page)
    {
        $p = Page::where('url', '=', $page)->where('status', '=', '1')->first();
        if(!is_null($p)) {
            return View::make('page',
                [
                    'p' => $p
                ]);
        }
        else {
            App::abort('404');
        }
    }

    public function lookup()
    {
        $q = Input::get('q');
        $country = '';
        if(Input::has('country')) {
            $country = Input::get('country');
        }
        if(Input::has('f')) {
            $filter = Input::get('f');
        }
        else {
            $filter = 'all';
        }
        $songs = [];
        if($filter == 'all' || $filter == 'songs') {
            if(!empty ($country)) {
                $songs = Song::where('title', 'like', '%' . $q . '%')
                    ->where('completed', '=', '1')
                    ->whereHas('author', function ($q) use ($country) {
                        $q->where('country', '=', $country);
                    })->orderBy('created_at', 'desc')
                    ->get();
            }
            else {
                $songs = Song::where('title', 'like', '%' . $q . '%')
                    ->where('completed', '=', '1')
                    ->orderBy('created_at', 'desc')
                    ->get();
            }
        }

        $albums = [];
        if($filter == 'all') {
            $albums = Bundle::where('name', 'like', '%' . $q . '%')
                ->orderBy('created_at', 'desc')
                ->get();
        }

        $people = [];
        if($filter == 'all' || $filter == 'people') {
	   $people = User::where(function($query) use ($q, $country) {
                $query->where('type', '=', 'artist')
                      ->where('fresh', '=', '1')
                      ->where('active', '=', '1')
                      ->where('deleted_at', '=', NULL);
                if( !empty ($country) ) {
                    $query->where('country', '=', $country);
                }
            })->where(function($query) use ($q, $country){
                $query->where('name', 'like', '%' . $q . '%')
                      ->orWhere('artist_name', 'like', '%' . $q . '%');

            })->get();
        }

        return View::make('search.results', [
            'wall' => $songs,
            'people' => $people,
            'albums' => $albums,
            'location' => GeoIP::getLocation(),
        ]);
    }

    public function featured_art($id)
    {
        $f = Featured::find($id);
        if($f) {
            $file = storage_path() . "/featured-art/" . $f->art;
            if( file_exists( $file ) ) {
                return Response::download($file);
            }
            else {
                App::abort('404');
            }
        }
        else {
            App::abort('404');
        }
    }

    public function news_art($id)
    {
        $n = News::find($id);
        if($n) {
            $file = storage_path() . "/news-art/" . $n->art;
            if( file_exists( $file ) ) {
                return Response::download($file);
            }
            else {
                App::abort('404');
            }
        }
        else {
            App::abort('404');
        }
    }

}