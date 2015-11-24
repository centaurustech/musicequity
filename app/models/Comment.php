<?php

use LaravelBook\Ardent\Ardent;
use Carbon\Carbon;
class Comment extends \LaravelBook\Ardent\Ardent {
	protected $fillable = [];

    /**
     * Validation rules for comments
     *
     * @var array
     */
    public static $rules = array(
        'comment' => 'required',
    );

    public function getinfo()
    {
        return $this->hasOne('User', 'id', 'user');
    }


    public function getCreatedAtAttribute($date){

        $date = new \Carbon\Carbon($date);
        $date = $date->diffForHumans();
        return $date;
    }

}