<?php

use Illuminate\Auth\UserTrait;
use Illuminate\Auth\UserInterface;
use Illuminate\Auth\Reminders\RemindableTrait;
use Illuminate\Auth\Reminders\RemindableInterface;
use LaravelBook\Ardent\Ardent;

class User extends \LaravelBook\Ardent\Ardent implements UserInterface, RemindableInterface {

	use UserTrait, RemindableTrait, SoftDeletingTrait;

	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'users';

    /**
     * Adding purge filters
     *
     * @param array $attributes
     */

    function __construct($attributes = array()) {
        parent::__construct($attributes);

        $this->purgeFilters[] = function($key) {
            $purge = array('repassword', 'agree');
            return ! in_array($key, $purge);
        };
    }

    public function songs()
    {
        return $this->hasMany('Song', 'artist', 'id');
    }

    public function getPicture()
    {
        return $this->hasOne('Picture', 'id', 'picture');
    }

    public function charity()
    {
        return $this->hasOne('Charity', 'id', 'charity_id');
    }

    public function featured()
    {
        return $this->hasOne('Featured', 'artist', 'id');
    }

	/**
	 * The attributes excluded from the model's JSON form.
	 *
	 * @var array
	 */
	protected $hidden = array('password', 'remember_token');


    /**
     * Enable auto purge for redundant fields
     *
     * @var bool
     */
    public $autoPurgeRedundantAttributes = true;

    /**
     * Validation rules for users
     *
     * @var array
     */
    public static $rules = array(
        'type' => 'required|in:artist,charity,customer',
        'email' => 'required|email|unique:users,email',
        'password' => 'required|min:6',
        'repassword' => 'same:password',
        'agree' => 'accepted',
        'profile_url' => 'unique:users,profile_url'
    );

    /**
     * Mass assignment fields
     *
     * @var array
     */
    protected $fillable = array('email','name', 'password', 'type', 'agree', 'password_confirmation', 'profile_url', 'fresh');

    /**
     * Enable Ardent AutoHydrate service
     *
     * @var bool
     */
    public $autoHydrateEntityFromInput = true;

    /**
     * Set password for auto-hashing
     *
     * @var array
     */
    public static $passwordAttributes  = array('password');

    /**
     * Enable auto-hashing
     *
     * @var bool
     */
    public $autoHashPasswordAttributes = true;

    /**
     * Retrieving user web links
     *
     * @return mixed
     */
    public function weblinks()
    {
        return $this->hasMany('WebLink', 'user', 'id');
    }

    public function followers(){
        return $this->hasMany('Follow', 'artist', 'id');
    }

    public function follows()
    {
        return $this->hasMany('Follow', 'user', 'id');
    }

    public function getPromoLinkAttribute()
    {
        $value = $this->promo;
        if($value != '') {
            preg_match(
                '/[\\?\\&]v=([^\\?\\&]+)/',
                $value,
                $matches
            );
            @$promo = $matches[1];

            return $promo;
        }
        else return $value;
    }

    public function getNameAllAttribute($name) {
        if( $this->type == 'artist' ) {
            return $this->artist_name;
        }
        else {
            return $name;
        }
    }

    /**
     *  Cascade delete songs
     */

    public function delete()
    {
        $this->songs()->delete();

        return parent::delete();
    }





}
