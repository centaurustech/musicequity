<?php

class Report extends \LaravelBook\Ardent\Ardent  {
    protected $fillable = ['reason', 'explanation', 'user'];
    public $autoHydrateEntityFromInput = true;    // hydrates on new entries' validation
    public $forceEntityHydrationFromInput = true; // hydrates whenever validation is called
    public static $rules = [
        'reason' => 'required|between:4,36',
        'explanation' => 'required|between:4,500',
        'user' => 'required|exists:users,id'
    ];
}