<?php

class Featured extends \Eloquent {
	protected $fillable = [];
    protected $table = 'featured';

    public function songinfo()
    {
        return $this->hasOne('Song', 'id', 'track');
    }

    public function artistinfo()
    {
        return $this->hasOne('User', 'id', 'artist');
    }
}