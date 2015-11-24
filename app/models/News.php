<?php

class News extends \Eloquent {
	protected $fillable = [];

    public function getCreatedAtAttribute($date){

        $date = new \Carbon\Carbon($date);
        $date = $date->diffForHumans();
        return $date;
    }

    public function user()
    {
        return $this->hasOne('User', 'id', 'author');
    }
}