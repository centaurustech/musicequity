<?php
use Carbon\Carbon;
class Song extends \Eloquent {
    use SoftDeletingTrait;
	protected $fillable = [];

    public function getCreatedAtAttribute($date){

        //$date = new \Carbon\Carbon($date);
        //$date = $date->diffForHumans();
        return $date;
    }

    public function getDateHumanAttribute()
    {
        $date = new \Carbon\Carbon($this->created_at);
        $date = $date->diffForHumans();
        return $date;
    }

    public function comments()
    {
        return $this->hasMany('Comment', 'song', 'id');
    }

    public function author()
    {
        return $this->hasOne('User', 'id', 'artist');
    }

    public function likes()
    {
        return $this->hasMany('Like', 'song', 'id');
    }

}