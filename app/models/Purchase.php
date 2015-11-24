<?php

class Purchase extends \Eloquent {
	protected $fillable = [];

    public function getWhenAttribute()
    {
        $date = new \Carbon\Carbon($this->created_at);
        $date = $date->diffForHumans();
        return $date;
    }

    public function getDateHumanAttribute()
    {
        $date = new \Carbon\Carbon($this->created_at);
        $date = $date->diffForHumans();
        return $date;
    }

    public function customerinfo()
    {
        return $this->hasOne('User', 'id', 'customer');
    }

    public function artistinfo()
    {
        return $this->hasOne('User', 'id', 'artist');
    }

    public function songinfo()
    {
        return $this->hasOne('Song', 'id', 'song');
    }
    public function bundleinfo()
    {
        return $this->hasOne('Bundle', 'id', 'song');
    }
}