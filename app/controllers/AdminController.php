<?php
use \Carbon\Carbon as Carbon;
class AdminController extends \BaseController {

	/**
	 * Display an index of the admin panel.
	 * GET /admin
	 *
	 * @return Response
	 */
	public function index()
	{

        //$start = new Carbon('first day of this month');
        if(Input::has('daterange')) {
            $range = explode(" ",Input::get('daterange'));
            try {
                $start = Carbon::createFromFormat('m/d/Y', $range[0]);
            } catch (Exception $ex) {
                $start = new Carbon('last month');
            }
            try {
                $finish = Carbon::createFromFormat('m/d/Y', $range[2]);
            } catch (Exception $ex) {
                $finish = new Carbon('today');
            }
        }
        else {
            $start = new Carbon('last month');
            $finish = new Carbon('today');
        }
        $range = $start->format('m/d/Y')." - ".$finish->format('m/d/Y');

        $graph = [
            'songs' => [],
            'artists' => [],
            'transactions' => [],
            'axis' => []
        ];

        $interval = DateInterval::createFromDateString('1 day');
        $period = new DatePeriod($start, $interval, $finish);
        $i = 0;

        foreach ( $period as $date) {
            $graph['axis'][$i] = $date->format('d.m.y');
            $graph['songs'][$i] = 0;
            $graph['artists'][$i] = 0;
            $graph['transactions'][$i] = 0;
            $i++;
        }
        $last = $i-1;

        $songs = Song::where('completed', '=', '1')->where('created_at', '>', $start);
        $artists = User::where('type', '=', 'artist')->where('created_at', '>', $start);
        $transactions =  MyTransaction::where('created_at', '>', $start);

        $stats['songs_count'] = $songs->count();
        $stats['artist_count'] = $artists->count();
        $stats['transactions_count'] = $transactions->count();

        /*foreach ($songs->get() as $s) {
            $c = new Carbon( $s->created_at );
            if(isset($graph['songs'][$c->format('d.m')])) {
                $graph['songs'][$c->format('d.m.y')]++;
            }
            else {
                $graph['songs'][$c->format('d.m.y')] = 1;
            }
        }*/

        foreach ($songs->get() as $s) {
            $c = new Carbon( $s->created_at );
            for ($i = 0; $i <= $last; $i++){
                if ($graph['axis'][$i] == $c->format('d.m.y')) {
                    $graph['songs'][$i]++;
                }
            }
        }

        foreach ($artists->get() as $s) {
            $c = new Carbon( $s->created_at );
            for ($i = 0; $i <= $last; $i++){
                if ($graph['axis'][$i] == $c->format('d.m.y')) {
                    $graph['artists'][$i]++;
                }
            }
        }

        foreach ($transactions->get() as $s) {
            $c = new Carbon( $s->created_at );
            for ($i = 0; $i <= $last; $i++){
                if ($graph['axis'][$i] == $c->format('d.m.y')) {
                    $graph['transactions'][$i]++;
                }
            }
        }

        /*foreach ($artists->get() as $s) {
            $c = new Carbon( $s->created_at );
            if(isset($graph['artists'][$c->format('d')])) {
                $graph['artists'][$c->format('j')]++;
            }
            else {
                $graph['artists'][$c->format('j')] = 1;
            }

        }

        foreach ($transactions->get() as $s) {
            $c = new Carbon( $s->created_at );
            if(isset($graph['transactions'][$c->format('d')])) {
                $graph['transactions'][$c->format('j')]++;
            }
            else {
                $graph['transactions'][$c->format('j')] = 1;
            }

        }*/

		return View::make('admin.dashboard', [
            'stats' => $stats,
            'range' => $range,
            'graph' => $graph
        ]);
	}

    /**
     * Suspend user
     *
     * @param $id
     * @return Response
     */

    public function suspend_user($id)
    {
        $u = User::find($id);
        if($u) {
            $u->approved = 1;
            $u->forceSave();

        }

        return Redirect::to('admin/users');
    }

    /**
     * Unsuspend user
     *
     * @param $id
     * @return Response
     */

    public function unsuspend_user($id)
    {
        $u = User::find($id);
        if($u) {
            $u::$rules = [];
            $u->approved = 0;
            $u->forceSave();
        }

        return Redirect::to('admin/users');
    }

    /**
     * Delete user from database
     *
     * @param $id
     * @return Response
     */

    public function delete_user($id)
    {
        $u = User::find($id);
        if($u) {
            $u->delete();
        }

        return Redirect::to('admin/users');
    }

    /**
     * Delete promo page
     *
     * @return Response
     */

    public function demote_user($id)
    {
        $f = Featured::where('artist', '=', $id);
        if($f) {
            $f->delete();
        }

        return Redirect::to('/admin/users');
    }

    /**
     *  Save promoted user record to the database
     *
     * @return Response
     */

    public function promote_user_post()
    {
        $f = new Featured;
        $f->title = Input::get('title');
        $f->artist = Input::get('user');
        $f->text = Input::get('text');
        $f->track = Input::get('track');
        if(Input::has('art')) {
            $image = Input::get('art');
            $exp = explode(",", $image);
            $name = str_random(15);
            $data = base64_decode($exp[1]);
            $tempfile = storage_path()."/temp/".$name."";
            file_put_contents($tempfile, $data);
            Image::make($tempfile)->resize(350, 400)->save( storage_path()."/featured-art/".$name.".png" );
            unlink($tempfile);
            $f->art = $name.".png";
        }

        $f->save();

        return Redirect::to('admin/users');
    }

    /**
     * Display User Promotion Form
     *
     * @return Response
     */

    public function promote_user($id)
    {
        $user = User::find($id);
        $songs = Song::where('artist', '=', $user->id)->where('completed', '=', 1)->get();
        return View::make('admin.user-promote',
            [
                'songs' => $songs,
                'artist' => $user
            ]
        );
    }

    /**
     * Delete Menu from database
     *
     * @return Reponse
     */

    public function delete_menu($id)
    {
        $l = MenuLink::find($id);

        if($l) {
            $l->delete();
        }

        return Redirect::to('admin/menu');
    }

    /**
     * Post menu to database
     *
     * @return Response
     */
    public function post_menu()
    {
        $l = new MenuLink;
        $l->url = Input::get('url');
        $l->title = Input::get('title');
        $l->save();

        return Redirect::to('admin/menu');
    }

    /**
     * Display the list of the menus available
     *
     * @return Response
     */

    public function list_menus()
    {
        $l = MenuLink::all();
        return View::make('admin.menu', [
            'links' => $l
        ]);
    }

    /**
     * Display the list of the pages created
     *
     * @return Response
     */

    public function list_pages()
    {
        $p = Page::all();
        return View::make('admin.pages', [
            'pages' => $p
        ]);
    }

    /**
     * Return the list of the users by type
     *
     * @param string $type
     * @return Response
     */

    public function list_users($type='all')
    {
        if($type == 'all') {
            $users = User::where('deleted_at', '=', null)->get();
        }

        return View::make('admin.users', [
            'users' => $users
        ]);
    }

    /**
     * Display the list of the news
     *
     * @return Response
     */

    public function list_news()
    {
        $news = News::all();
        return View::make('admin.news', [
            'news'  => $news
        ]);
    }



    /**
     * Display form to add menu
     *
     * @return Response
     */
    public function add_menu()
    {
        return View::make('admin.add-menu', [

        ]);
    }

    /**
     * Display form to add news
     *
     * @return Response
     */
    public function add_news_form()
    {
        $hubs = Hub::all();
        return View::make('admin.add-news', [
            'hubs' => $hubs,
        ]);
    }

    public function delete_page($id){
        $p  = Page::find($id);
        if($p) {
            $p->delete();

        }
        return Redirect::to('admin/pages');
     }


    /**
     * Publish page
     *
     * @param $id
     * @return Response
     */
    public function publish_page($id)
    {
        $p = Page::find($id);

        if($p) {
            $p->status = 1;
            $p->save();
        }

        return Redirect::to('admin/pages');
    }

    /**
     * Unublish page
     *
     * @param $id
     * @return Response
     */
    public function unpublish_page($id)
    {
        $p = Page::find($id);

        if($p) {
            $p->status = 0;
            $p->save();
        }

        return Redirect::to('admin/pages');
    }

    /**
     * Display form for editing pages
     *
     * @return Response
     */

    public function edit_page_form($id)
    {

        $p = Page::find($id);
        return View::make('admin.edit-page', [
            'page' => $p
        ]);
    }

    /**
     * Display form for pages
     *
     * @return Response
     */

    public function add_page_form()
    {


        return View::make('admin.add-pages', [

        ]);
    }

    /**
     * Store page record
     *
     * @return Response
     */

    public function post_page()
    {
        $page = new Page;
        $page->title = Input::get('title');
        $page->body = Input::get('text');
        $page->author = Auth::user()->id;
        $page->status = Input::get('status');
        $page->title = Input::get('title');
        $page->url = Str::slug( Input::get('title') );
        $page->save();
        return Redirect::to('admin/pages');
    }

    public function edit_page_post()
    {
        $page = Page::find(Input::get('page'));
        if($page) {
            $page->title = Input::get('title');
            $page->body = Input::get('text');
            $page->status = Input::get('status');
            $page->title = Input::get('title');
            $page->save();
        }

        return Redirect::to('admin/pages');
    }

    public function post_news_edit()
    {
        $news = News::find( Input::get('id') );

        if($news) {
            $news->title = Input::get('title');
            $news->status = Input::get('status');
            $news->body = Input::get('text');
            $news->hub = Input::get('hub');

            if(Input::has('art')) {
                $image = Input::get('art');
                $exp = explode(",", $image);
                $name = str_random(15);
                $data = base64_decode($exp[1]);
                $tempfile = storage_path()."/temp/".$name."";
                file_put_contents($tempfile, $data);
                Image::make($tempfile)->resize(360, 140)->save( storage_path()."/news-art/".$name.".png" );
                unlink($tempfile);
                $news->art = $name.".png";
            }
            $news->save();

            return Redirect::to('admin/news');
        }
    }

    /**
     * Show edit news form
     *
     * @param $id
     * @return Reponse
     */

    public function news_edit($id)
    {
        $n = News::find($id);
        $hubs = Hub::all();
        if($n) {

            return View::make('admin.edit-news', [
                'news' => $n,
                'hubs' => $hubs
            ]);

        }
        else {
            App:abort('404');
        }
    }

    /**
     * Marks news as published
     *
     * @param $id
     * @return Reponse
     */

    public function news_publish($id)
    {
        $n = News::find($id);

        if($n) {
            $n->status = 1;
            $n->save();
        }

        return Redirect::to('admin/news');
    }

    public function news_delete($id)
    {
        $n = News::find($id);

        if($n) {
            $n->delete();
        }

        return Redirect::to('admin/news');
    }

    /**
     * Marks news as unpublished
     *
     * @param $id
     * @return Reponse
     */

    public function news_unpublish($id)
    {
        $n = News::find($id);

        if($n) {
            $n->status = 0;
            $n->save();
        }

        return Redirect::to('admin/news');
    }

    /**
     * Show existing ads
     *
     * @return Response
     */

    public function list_ads()
    {
        $b = Ads::all();
        return View::make('admin.ads', [
            'box' => $b
        ]);
    }

    /**
     * Post ads
     *
     * @return Response
     */
    public function post_ads()
    {
        $files = Input::file();


        $b = Ads::find(1);
        $b->link = Input::get('box-text1');
        $b->save();

        $b = Ads::find(2);
        $b->link = Input::get('box-text2');
        $b->save();

        $b = Ads::find(3);
        $b->link = Input::get('box-text3');
        $b->save();

        $b = Ads::find(4);
        $b->link = Input::get('box-text4');
        $b->save();

        $b = Ads::find(5);
        $b->link = Input::get('box-text5');
        $b->save();

        foreach ($files as $i => $f)
        {
            $name = $i;
            $location = storage_path() . "/ads/";
            if(Input::hasFile($name)) {

                $file = Input::file($name)->getRealPath();
                Image::make( $file )->resize(256, 180)->save( $location . $name .  ".png" );
            }
        }

        return Redirect::to('admin/ads');

    }

    public function transactions()
    {
        $list = Purchase::all();

        return View::make('admin.transactions', [
            'list' => $list
        ]);

    }

    public function change_status($id)
    {
        $t = Purchase::find($id);
        if(! is_null($t) ) {
            $t->paid = ! $t->paid;
            $t->save();
        }

        return Redirect::to('admin/transactions');
    }

    /**
     * Create new news record
     *
     * @return Response
     */

    public function post_news()
    {
        $news = new News;
        $news->title = Input::get('title');
        $news->status = Input::get('status');
        $news->body = Input::get('text');
        $news->author = Auth::user()->id;
        $news->hub = Input::get('hub');

        if(Input::has('art')) {
            $image = Input::get('art');
            $exp = explode(",", $image);
            $name = str_random(15);
            $data = base64_decode($exp[1]);
            $tempfile = storage_path()."/temp/".$name."";
            file_put_contents($tempfile, $data);
            Image::make($tempfile)->resize(360, 140)->save( storage_path()."/news-art/".$name.".png" );
            unlink($tempfile);
            $news->art = $name.".png";
        }
        $news->save();

        return Redirect::to('admin/news');

    }

    /**
     * Show the list of existing hubs
     *
     * @return Response
     */

    public function list_hubs()
    {

        $hubs = Hub::all();

        return View::make('admin.hubs', [
            'hubs' => $hubs,
        ]);
    }

    /**
     * Display new hub form
     *
     * @return Response
     */

    public function add_hub()
    {
        return View::make('admin.add-hub');
    }

    /**
     * Display new hub form
     *
     * @return Response
     */

    public function edit_hub($id)
    {
        $hub = Hub::find($id);
        if(! is_null( $hub ) ) {
            return View::make('admin.add-hub', ['hub' => $hub]);
        }
        else {
            return \Illuminate\Support\Facades\Redirect::to('admin/hubs');
        }

    }



    /**
     * Handle hub form (add/edit)
     *
     * @return Response
     */

    public function post_hub()
    {
        $hub = null;

        if( Input::has('id') ) {
            $hub = Hub::find( Input::get('id') );
        }

        if( is_null( $hub ) ) {
            $hub = new Hub;
        }

        $geo = Geocoder::getCoordinatesForQuery(Input::get('location'));


        $hub->name  = Input::get('name');
        $hub->description = Input::get('description');
        $hub->location = Input::get('location');
				$hub->facebook = Input::get('facebook');
				$hub->google = Input::get('google');
				$hub->twitter = Input::get('twitter');
        $hub->slug = Str::slug($hub->name);

        if($geo != 'NOT_FOUND') {
            $hub->lat = $geo['lat'];
            $hub->lng = $geo['lng'];
        }

        if(Input::has('art')) {
            $image = Input::get('art');
            $exp = explode(",", $image);
            $name = str_random(15);
            $data = base64_decode($exp[1]);
            $tempfile = storage_path()."/temp/".$name."";
            file_put_contents($tempfile, $data);
            Image::make($tempfile)->resize(210, 210)->save( storage_path()."/pictures/".$name.".png" );
            unlink($tempfile);

            $p = new Picture;
            $p->name = $name;
            $p->user = 0;
            $p->extension = '.png';
            $p->save();
            $hub->picture = $p->id;
        }


        $hub->save();

        return Redirect::to('admin/hubs');
    }

	public function list_admins()
	{
		$admins = User::where('admin', 1)->get();
		$users = User::where('admin', 0)->get();


		return View::make('admin.list-admins', [
				'admins' => $admins,
				'users' => $users
			]);
	}

	public function admin_demote($id)
	{
		$admin = User::find($id);
		$me = Auth::user();

		if(count($admin) < 1) {
			return false;
		}

		if($me->id == $admin->id) {
			return 'You can not demote yourself.';
		}

		$admin->admin = '0';
		$admin->forceSave();

		return Redirect::to('admin/admins');
	}

	public function admin_promote()
	{

		$u = User::find( Input::get('user') );

		if( count($u) < 1 ) {
			return false;
		}

		$u->admin = 1;
		$u->admin_access = Input::get('access');
		$u->forceSave();

		return Redirect::to('admin/admins');

	}


}
