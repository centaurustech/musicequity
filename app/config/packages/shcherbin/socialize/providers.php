<?php

return array(

	/*
	|--------------------------------------------------------------------------
	| List of Providers
	|--------------------------------------------------------------------------
	|
	| Providers, which can be used by the application with auth data
	|
	*/

	'services' => [
		'facebook' => [
			'enabled' => true,
			'id' => '374988506005372',
			'secret' => '28ba6e71da0c35d88c38901b83264b72',
			'scopes' => ['email', 'public_profile'],
			'version' => '2.1',
            'additional' => [
                'display' => 'popup'
            ]
		],
		'google' => [
			'enabled' => true,
			'id' => '507802949053-ddp5li792ruvub6cgqokp3joaljss3cl.apps.googleusercontent.com',
			'secret' => '9sragdoSc4sprOP2ClkCb1VE',
			'scopes' => ['profile', 'email']
		],
		'vkontakte' => [
			'enabled' => false,
			'id' => '',
			'secret' => '',
			'additional' => [
				'v' => '5.25'
			]
		]
	]

);
