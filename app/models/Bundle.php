<?php

class Bundle extends \Eloquent {
	protected $fillable = [];

    public function songs()
    {
        return $this->hasMany('BundleList', 'bundle_id', 'id');
    }

    public function getDateHumanAttribute()
    {
        $date = new \Carbon\Carbon($this->created_at);
        $date = $date->diffForHumans();
        return $date;
    }

    public function delete()
    {
        $this->songs()->delete();
        return parent::delete();
    }

    public function author()
    {
        return $this->hasOne('User', 'id', 'artist');
    }

}