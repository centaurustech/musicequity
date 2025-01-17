<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the Closure to execute when that URI is requested.
|
*/



if( Schema::hasTable('menu_links') ) {
    $twig = app('twig');
    $twig->addGlobal('links', MenuLink::all());
}


Route::get('/', 'MainController@home');

// All user related resources
Route::resource('users', 'UsersController');
Route::get('signout', 'UsersController@signout');
Route::get('search', 'MainController@lookup');
Route::get('recover-password', 'UsersController@recover_password');
Route::post('recover-password', 'UsersController@recover_password_post');
Route::post('signin', 'UsersController@signin');
Route::get('activation/{code}', 'UsersController@activation');
Route::get('p/{page}', 'MainController@page');
Route::get('news/{id}', 'MainController@news');

// Artist related routes
Route::get('artist/new/', array('before'=>'auth|fresh|isArtist','uses' => 'ArtistsController@new_artist'));
Route::post('create/album/', array('before'=>'auth|fresh|isArtist','uses' => 'ArtistsController@create_album'));
Route::get('delete/album/{id}', array('before'=>'auth|fresh|isArtist','uses' => 'ArtistsController@delete_album'));
Route::post('comment', array('before' => 'auth', 'uses' => 'ArtistsController@comment'));
Route::get('profile/settings',  array('before' => 'auth|isArtist', 'uses'=>'ArtistsController@profile'));
Route::post('profile/settings', array('before' => 'auth|isArtist', 'uses'=>'ArtistsController@saveprofile'));
Route::get('profile/picture/{id}/{type}', 'UsersController@profilepicture');
Route::get('upload', array('before' => 'auth|isArtist', 'uses'=>'ArtistsController@songinfo'));
Route::post('addsong', array('before' => 'auth|isArtist', 'uses'=>'ArtistsController@addsong'));
Route::post('postimage', array('before' => 'auth', 'uses'=>'ArtistsController@postimage'));
Route::post('notify', 'ArtistsController@notify');
Route::post('upload', array('before' => 'auth|isArtist', 'uses'=>'ArtistsController@upload'));
Route::get('song/{id}', 'ArtistsController@song_download');
Route::post('addhit/{id}', 'ArtistsController@add_hit');
Route::get('art/{id}', 'ArtistsController@show_art');
Route::get('check_assembly', array('before' => 'auth|isArtist', 'uses'=>'ArtistsController@check_assembly'));
Route::get('track/{id}', 'ArtistsController@track');
Route::get('track/edit/{id}', array('before'=>'auth|fresh|isArtist','uses' => 'ArtistsController@edit_song'));
Route::get('track/delete/{id}', array('before' => 'auth|isArtist', 'uses'=>'ArtistsController@delete_song'));
Route::post('like/{id}', array('before' => 'auth', 'uses'=>'ArtistsController@like'));
Route::post('link/delete/{id}',  array('before' => 'auth|isArtist', 'uses'=>'ArtistsController@delete_link'));
Route::post('event/new',  array('before' => 'auth|isArtist', 'uses'=>'ArtistsController@new_event'));

Route::get('fb', 'SocialController@login');
Route::get('fb/post', 'SocialController@post');

Route::group(array('prefix' => 'artist'), function()
{
    Route::get('/{artist}', 'ArtistsController@showprofile');

});


// Charity related routes
Route::post('charity', 'UsersController@store');
Route::get('charity/new', array('before' => 'auth|isCharity', 'uses'=>'CharityController@new_charity'));
Route::get('/charity/settings',  array('before' => 'auth|isCharity', 'uses'=>'CharityController@settings'));
Route::post('/charity/settings',  array('before' => 'auth|isCharity', 'uses'=>'CharityController@postsettings'));
Route::get('charity/approve/{id}', [
    'before' => 'auth|isCharity',
    'uses' => 'CharityController@approve'
  ]);
  Route::get('charity/decline/{id}', [
      'before' => 'auth|isCharity',
      'uses' => 'CharityController@decline'
    ]);
Route::group(array('prefix' => 'charity'), function()
{

    Route::get('/{charity}', 'CharityController@show');

});


// Customer related routes
Route::get('customer/new', 'CustomerController@new_customer');
Route::post('customer/new', 'UsersController@new_customer');
Route::get('c/{profile}', 'CustomerController@show');
Route::get('artist/{artist}/follow', array('before' => 'auth|isCustomer', 'uses'=>'ArtistsController@follow'));
Route::get('hide/{id}', array('before' => 'auth|isCustomer', 'uses'=>'CustomerController@hide_elem'));
Route::get('wall/reset', array('before' => 'auth|isCustomer', 'uses'=>'CustomerController@reset_wall'));
Route::post('report',  array('before' => 'auth', 'uses'=>'ArtistsController@report'));
Route::get('customer/settings', array('before' => 'auth|isCustomer', 'uses'=>'CustomerController@settings'));
Route::post('customer/settings', array('before' => 'auth|isCustomer', 'uses'=>'CustomerController@post_settings'));
Route::post('add-to-cart/{song}', array('before' => 'canbuy', 'uses'=>'CustomerController@add_to_cart'));
Route::post('add-bundle-to-cart/{bundle}', array('before' => 'canbuy', 'uses'=>'CustomerController@add_bundle_to_cart'));
Route::post('remove-from-cart/{song}', array('before' => 'canbuy', 'uses'=>'CustomerController@remove_from_cart'));
Route::post('remove-bundle-from-cart/{bundle}', array('before' => 'canbuy', 'uses'=>'CustomerController@remove_bundle_from_cart'));
Route::get('cart/view', array('before' => 'canbuy', 'uses'=>'CustomerController@viewcart'));
Route::get('cart/view-bundle', array('before' => 'canbuy', 'uses'=>'CustomerController@viewcart_bundle'));
Route::get('checkout', array(
    'as' => 'payment',
    'uses' => 'PayPalController@checkout',
    'before' => 'canbuy'
));

// this is after make the payment, PayPal redirect back to your site
Route::get('payment/status', array(
    'as' => 'payment.status',
    'uses' => 'PayPalController@status',
    'before' => 'canbuy'
));

Route::get('purchase/{code}',  [
    'uses' => 'CustomerController@purchase_msg',
    'before' => 'canbuy'
]);

Route::get('settings', function() {

    if(Auth::check()) {
        if(Auth::user()->type == 'artist') {
            return Redirect::to('profile/settings');
        }

        if(Auth::user()->type == 'customer') {
            return Redirect::to('customer/settings');
        }

        if(Auth::user()->type == 'charity') {
            return Redirect::to('charity/settings');
        }
    }


    return Redirect::to('/');
});

Route::get('migrate', function() {
    Artisan::call('migrate', array('--force' => true));
});

Route::get('thumbnail/{id}', 'ArtistsController@showthumbnail');
Route::get('small/thumbnail/{id}', 'ArtistsController@smallthumbnail');
Route::get('socialize/{provider}', 'UsersController@socialize');

Route::get('download/{code}', function($code) {
    $d = Download::where('url', '=', $code)->first();
    if(! is_null ($d)) {
        $song = Song::withTrashed()->where('id', '=', $d->song)->first();
        if($song){
            $path = $song->path . "/" . $song->original_name;
            return Response::download($path, $song->title . ".mp3");
        }
        else {
            App::abort('404');
        }
    }
    else {
        App::abort('404');
    }

});


Route::get('admin', [
    'uses' => 'AdminController@index',
    'before' => 'auth|admin'
]);

Route::post('admin', [
    'uses' => 'AdminController@index',
    'before' => 'auth|admin'
]);

Route::get('admin/transaction/status/{id}', [
    'uses' => 'AdminController@change_status',
    'before' => 'auth|admin'
]);



Route::get('admin/users', [
    'uses' => 'AdminController@list_users',
    'before' => 'auth|admin'
]);
Route::get('admin/user/{id}/promote', [
    'uses' => 'AdminController@promote_user',
    'before' => 'auth|admin'
]);
Route::get('admin/user/{id}/demote', [
    'uses' => 'AdminController@demote_user',
    'before' => 'auth|admin'
]);
Route::get('admin/user/{id}/delete', [
    'uses' => 'AdminController@delete_user',
    'before' => 'auth|admin'
]);
Route::get('admin/user/{id}/suspend', [
    'uses' => 'AdminController@suspend_user',
    'before' => 'auth|admin'
]);
Route::get('admin/user/{id}/unsuspend', [
    'uses' => 'AdminController@unsuspend_user',
    'before' => 'auth|admin'
]);
Route::post('admin/user/promote', [
    'uses' => 'AdminController@promote_user_post',
    'before' => 'auth|admin'
]);

Route::get('admin/transactions', [
    'uses' => 'AdminController@transactions',
    'before' => 'auth|admin'
]);


Route::get('admin/pages', [
    'uses' => 'AdminController@list_pages',
    'before' => 'auth|admin'
]);
Route::get('admin/pages/add', [
    'uses' => 'AdminController@add_page_form',
    'before' => 'auth|admin'
]);
Route::get('admin/page/{id}/edit', [
    'uses' => 'AdminController@edit_page_form',
    'before' => 'auth|admin'
]);
Route::get('admin/page/{id}/publish', [
    'uses' => 'AdminController@publish_page',
    'before' => 'auth|admin'
]);
Route::get('admin/page/{id}/unpublish', [
    'uses' => 'AdminController@unpublish_page',
    'before' => 'auth|admin'
]);
Route::get('admin/page/{id}/delete', [
    'uses' => 'AdminController@delete_page',
    'before' => 'auth|admin'
]);
Route::post('admin/page/post', [
    'uses' => 'AdminController@post_page',
    'before' => 'auth|admin'
]);
Route::post('admin/page/edit', [
    'uses' => 'AdminController@edit_page_post',
    'before' => 'auth|admin'
]);
Route::get('admin/news', [
    'uses' => 'AdminController@list_news',
    'before' => 'auth|admin'
]);
Route::get('admin/news/add', [
    'uses' => 'AdminController@add_news_form',
    'before' => 'auth|admin'
]);
Route::get('admin/news/unpublish/{id}', [
    'uses' => 'AdminController@news_unpublish',
    'before' => 'auth|admin'
]);
Route::get('admin/news/{id}/delete', [
    'uses' => 'AdminController@news_delete',
    'before' => 'auth|admin'
]);
Route::get('admin/news/edit/{id}', [
    'uses' => 'AdminController@news_edit',
    'before' => 'auth|admin'
]);
Route::get('admin/news/publish/{id}', [
    'uses' => 'AdminController@news_publish',
    'before' => 'auth|admin'
]);
Route::post('admin/news/post', [
    'uses' => 'AdminController@post_news',
    'before' => 'auth|admin'
]);
Route::post('admin/news/edit', [
    'uses' => 'AdminController@post_news_edit',
    'before' => 'auth|admin'
]);

Route::get('email', function() {
    return View::make('emails.activation');
});

Route::get('admin/menu', [
    'uses' => 'AdminController@list_menus',
    'before' => 'auth|admin'
]);
Route::get('admin/menu/add', [
    'uses' => 'AdminController@add_menu',
    'before' => 'auth|admin'
]);
Route::post('admin/menu/post', [
    'uses' => 'AdminController@post_menu',
    'before' => 'auth|admin'
]);

Route::get('admin/menu/{id}/delete', [
    'uses' => 'AdminController@delete_menu',
    'before' => 'auth|admin'
]);


Route::get('admin/ads', [
    'uses' => 'AdminController@list_ads',
    'before' => 'auth|admin'
]);

Route::post('admin/ads/post', [
    'uses' => 'AdminController@post_ads',
    'before' => 'auth|admin'
]);

Route::get('admin/admins', [
    'uses' => 'AdminController@list_admins',
    'before' => 'auth|admin'
]);

Route::post('admin/admins/promote', [
    'uses' => 'AdminController@admin_promote',
    'before' => 'auth|admin'
]);

Route::get('admin/admins/{id}/demote', [
    'uses' => 'AdminController@admin_demote',
    'before' => 'auth|admin'
]);

Route::get('box/{id}/{timestamp}', function($id){
    $file = storage_path().'/ads/box' . $id.".png";
    $empty = storage_path().'/ads/noimage.png';
    if(file_exists($file)) {
        return Response::download($file);
    }
    else {
        return Response::download($empty);
    }
});


Route::get('news/art/{id}', 'MainController@news_art');
Route::get('featured/art/{id}', 'MainController@featured_art');


/*
 * Hubs
 */

Route::get('hubs/{slug}', [
    'uses' => 'HubController@show',
]);

Route::get('hubs/{slug}/follow', array('before' => 'auth', 'uses'=>'HubController@follow'));

Route::get('admin/hubs/', [
    'uses' => 'AdminController@list_hubs',
    'before' => 'auth|admin'
]);

Route::get('admin/hubs/add', [
    'uses' => 'AdminController@add_hub',
    'before' => 'auth|admin'
]);

Route::get('admin/hubs/edit/{id}', [
    'uses' => 'AdminController@edit_hub',
    'before' => 'auth|admin'
]);

Route::get('hub/picture/{id}', [
   'uses' => 'HubController@picture'
]);

Route::post('admin/hubs/post', [
    'uses' => 'AdminController@post_hub',
    'before' => 'auth|admin'
]);


/*
 * Handling 404 errors
 */

App::missing(function($exception)
{
    return Response::view('404', [], 404);
});
