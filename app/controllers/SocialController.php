<?php
class SocialController extends BaseController {

    private $instance;
    private $user;
    private $permissions;
    private $url_app;
    public function __construct()
    {
        $application = array(
            'appId' => '597013577119542',
            'secret' => 'b23d9a0c7d8b440b28a311cae0f73d71'
        );

        $this->permissions = 'publish_stream';
        $this->url_app = 'http://me.dev/fb';
        $this->instance = FacebookConnect::getFacebook($application);

    }

    public function login()
    {
        $this->user = FacebookConnect::getUser($this->permissions, $this->url_app);
        dd($this->user);
    }

    public function post()
    {
        $message = array(
            'link'    => 'http://laravel-test.local/',
            'message' => 'test message',
            'picture'   => 'http://laravel-test.local/test.gif',
            'name'    => 'test Title',
            'description' => 'test description',
            'access_token' => $this->user['access_token'] // form FacebookConnect::getUser();
        );

        FacebookConnect::postToFacebook($message, 'feed');
    }
}