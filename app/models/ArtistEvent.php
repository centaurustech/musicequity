<?php
class ArtistEvent extends \LaravelBook\Ardent\Ardent  {
	protected $fillable = ['name', 'location', 'date'];
    protected $table = 'events';
    public $autoHydrateEntityFromInput = true;    // hydrates on new entries' validation
    public $forceEntityHydrationFromInput = true; // hydrates whenever validation is called
    public static $rules = [
        'name' => 'required|between:4,26',
        'location' => 'required|between:4,128',
        'date' => 'required|between:9,10'
    ];

    public function beforeSave()
    {
        $this->artist = Auth::user()->id;
        $this->date = new Carbon\Carbon( str_replace( "/", "-", $this->date ) );
    }

    public function getartist()
    {
        return $this->hasOne('User', 'id', 'artist');
    }

    public function getDateAttribute($date){
        if(!$this->getDirty()) {
            $date = new \Carbon\Carbon($date);
            $date = $date->diffForHumans();
        }
        return $date;
    }

}