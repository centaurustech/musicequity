<?php

class CharityController extends \BaseController {



	/**
	 * Store a newly created Charity User in storage.
	 * POST /charity
	 *
	 * @return Response
	 */
	public function store()
	{
		//
	}

    /**
     * New charity post-signup page
     *
     * return @Response
     */
    public function new_charity()
    {
        return View::make('charity.registration-confirmation');
    }

    /**
     * Save charity settings
     *
     * @return Response
     */
    public function postsettings()
    {
        $input = Input::all();

        $profile = User::find(Auth::user()->id);


        if(!$profile->charity) {
            $charity = new Charity;
        }
        else {
            $charity = Charity::find($profile->charity_id);
        }

        $charity->name = $input['name'];
        $charity->goal = $input['goal'];
        $charity->description = $input['description'];
        $charity->save();
        $profile->charity_id = $charity->id;
        $profile->profile_url = Str::slug($input['name']);
        $profile->phone = $input['phone'];
        $profile->country = $input['country'];
        $profile->city = $input['city'];
        $profile->picture = $input['picture_id'];
        $profile->zip = $input['zip'];
        $profile->paypal = $input['paypal'];


        //Disabling validation rules
        $profile::$rules['email'] = '';
        $profile::$rules['agree'] = '';
        $profile->autoHashPasswordAttributes = false;

        if($profile->save([
            'charity_id' => 'required|exists:charity,id',
            'profile_url' => 'unique:users,profile_url,'  . $profile->id
        ]))
        {
            return Redirect::to('/charity/'  . $profile->profile_url);
        }
        else {
            return Redirect::to( 'charity/settings' )->with('errors', $profile->errors()->all());
        }
    }

    /**
     * Show charity set up form
     *
     * @return Response
     */
    public function settings()
    {
        $profile = Auth::user();
        return View::make( 'charity.settings', ['profile' => $profile] );
    }


    /**
     * Show charity profile
     *
     * @param int $charity
     *
     * @return Response
     *
     */
    public function show($charity)
    {
        $profile = User::where('profile_url','=',$charity)->where('approved', '=', '0')->first();
        if($profile->charity) {
            $page = Page::where('title', '=', 'faq-charity')->first();
            $songs = Song::where('charity', $profile->charity->id)->where('charity_approved', '=', 1)->orderBy('created_at', 'desc')->get();
            $songs_in = [];
						$approval = Song::where('charity', $profile->charity->id)->where('charity_approved', '=', 0)->get();
            foreach($songs as $s) {
                $songs_in[] = $s->id;
            }
            if(count($songs_in) > 0) {
                $comments = Comment::whereIn('song', $songs_in)->orderBy('id', 'desc')->take(3)->get();
            }
            else $comments = "";
            return View::make('charity.profile-new', [
                'profile' => $profile,
                'wall' => $songs,
                'page' => $page,
                'comments' => $comments,
                'approval' => $approval
            ]);
        }
        else {
            return Redirect::to('charity/settings');
        }

    }

		public function approve($id)
		{
			$song = Song::find($id);
			$me = Auth::user();

			if(count($song) < 1 || $song->charity != $me->charity->id) {
				return false;
			}



			$song->charity_approved = 1;
			$song->save();

			return Redirect::to('charity/' . $me->profile_url);

		}

		public function decline($id)
		{
			$song = Song::find($id);
			$me = Auth::user();

			if(count($song) < 1 || $song->charity != $me->charity->id) {
				return false;
			}



			$song->charity = '';
			$song->save();

			return Redirect::to('charity/' . $me->profile_url);


		}

}
