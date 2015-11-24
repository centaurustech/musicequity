<?php
use MusicEquity\Helpers\Mailer as Mailer;
class UsersController extends \BaseController {


    public function new_customer()
    {
        //dd(Input::all());
        $profile = User::create(Input::all());
        $profile->profile_url = str_random(18);
        $profile->active = 1;
        $profile->fresh = 1;
        $profile->type = 'customer';
        if($profile->save(
            [
                'name' => 'required',
                'profile_url' => 'unique:users,profile_url',
                'password' => 'required|between:4,12|confirmed',
                'password_confirmation' => 'required|Between:4,12'
            ]
        ))
        {
            Auth::login($profile);
            return Redirect::to('/c/'  . $profile->profile_url);
        }
        else {
            return Redirect::to( 'purchase/' . Input::get('pay_code') )->with('errors', $profile->errors()->all());
        }
    }

    public function recover_password()
    {
        return View::make('recovery');
    }


    /**
     * Show artist profile picture
     *
     * @return Response
     */

    public function profilepicture($id, $type)
    {
        $pic = Picture::find($id);

        if($pic) {
            return Response::download(storage_path()."/pictures/".$pic->name."-".$type.$pic->extension);
        }
        else {
            return Response::download(storage_path()."/no-user.png");
        };
    }


    public function recover_password_post()
    {
        $email = Input::get('email');

        $user = User::where('email', '=', $email)->first();

        if(!is_null($user)) {
            $user::$rules = array();
            $new_password = str_random(10);
            $user->password = $new_password;
            $user->save();

            $m = new Mailer;
            $m->send($user, 'emails.recovery', array('password' => $new_password), array('subject' => 'Music Equity Password Recovery', 'from' => 'support@musicequity.com'));
            return Redirect::to('/');
        }
        return Redirect::to('/');
    }

	/**
	 * Store a newly created user in storage.
	 *
	 * @return Response
	 */
	public function store()
	{

        /* Fetching inputs and creating model */
		$user = new User;
        $id = mt_rand();
        Input::merge(['profile_url'  => $id, 'fresh' => '1']);
        if(Input::get('type') == 'charity') {
            Input::merge(['approved'  => '1']);
        }
        if($user->save()) {


            /* Generate activation code */
            $activation = new Activation;
            $activation->generate($user);
            $activation->save();

            /* Queueing email */
            $m = new Mailer;
            $m->send($user, 'emails.activation', array('code' => $activation->code), array('subject' => 'Music Equity Account Activation', 'from' => 'activations@musicequity.com'));

            return Response::json(
                array (
                    'success' => true
                )
            );
        }
        else {
            return Response::json( $user->errors() );
        }



	}

    public function socialize($provider)
    {
        // Get the provider instance
        $provider = Socialize::with($provider);
        if (Input::has('code'))
        {

            $user = $provider->user();
            dd($user);
            return View::make('facebook', [
                'user' => $user
            ]);
        }
        else
        {
            return $provider->redirect();
        }
    }




    /**
     * Handling user activation
     *
     * return @Response
     */
    public function activation( $code )
    {
        $code = Activation::where('code', '=', $code)->first();
        if($code) {

            $user = User::find($code->user);
            if($user) {
                Auth::login($user);
            }


            $code->delete();

            $result = [
                'success' => true,
            ];

            if($user->type == 'artist')
            {
                return View::make('artist.activation', $result);
            }

            if($user->type == 'charity')
            {
                return View::make('charity.activation', $result);
            }
            if($user->type == 'customer')
            {
                return View::make('customer.activation', $result);
            }
        }
        else return View::make('charity.activation');




    }

	/**
	 * Update the specified user in storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function update($id)
	{
		$user = User::findOrFail($id);

		$validator = Validator::make($data = Input::all(), User::$rules);

		if ($validator->fails())
		{
			return Redirect::back()->withErrors($validator)->withInput();
		}

		$user->update($data);

		return Redirect::route('users.index');
	}

    /**
     * User auth
     * 
     * @return Response
     */

    public function signin()
    {

        if (Auth::attempt(array('email' => Input::get('email'), 'password' => Input::get('password'))))
        {
            if(Auth::user()->approved == 1) {
                Auth::logout();
                return Response::json('error');
            }
            if(Auth::user()->deleted_at != NULL) {
                Auth::logout();
                return Response::json('error');
            }
            $url = URL::to('/') . '/' . Auth::user()->type . '/' .  Auth::user()->profile_url;
            if(Auth::user()->type == 'customer') {
                $url = URL::to('/') . '/c/' .  Auth::user()->profile_url;
            }
            return Response::json(['success' => true, 'url' =>  $url]);
        }
        else {
            return Response::json('error');
        }

    }


    /**
     * Terminating user session
     *
     * @return Response
     */

    public function signout()
    {
        Auth::logout();

        return Redirect::to('/');
    }

	/**
	 * Remove the specified user from storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function destroy($id)
	{
		User::destroy($id);

		return Redirect::route('users.index');
	}


}
