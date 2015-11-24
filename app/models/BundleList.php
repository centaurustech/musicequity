<?php

class BundleList extends \Eloquent {
	protected $fillable = [];

    protected $table = 'bundle_list';

    public function item()
    {
        return $this->hasOne('Song', 'id', 'song');
    }
}