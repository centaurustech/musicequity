<?php

class Follow extends \Eloquent {
	protected $fillable = [];

    public function profile(){
        return $this->hasOne('User', 'id', 'user');
    }
}