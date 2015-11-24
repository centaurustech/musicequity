<?php
class Activation extends Eloquent {

    /**
     * Table name
     *
     * @var string
     */
    protected $table = 'activations';

    /**
     * Generate activation code for the user
     *
     * @param $user
     * @return bool
     */
    public function generate($user){

        $code = substr(md5(microtime()), 0, 255);

        $this->user = $user->id;
        $this->code = $code;

        return true;

    }
}