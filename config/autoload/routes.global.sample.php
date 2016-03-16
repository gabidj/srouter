<?php

return [
    'dependencies' => [
        'invokables' => [
            Zend\Expressive\Router\RouterInterface::class => GabiDJ\Expressive\SRouter::class,
        ],
        // Map middleware -> factories here
       'factories' => [
            App\Action\YourIndexAction::class => App\Action\YourIndexFactory::class,
        ]
    ],

    'routes' => [
//         Example:
//         [
//             'name' => 'home',
//             'path' => '/',
//             'middleware' => App\Action\HomePageAction::class,
//             'allowed_methods' => ['GET'],
//         ],
		[
			'name' => 'index',
			'path' => '/',
			'middleware' => App\Action\YourIndexAction::class,
			'allowed_methods' => ['POST'],
		],
		[
			'name' => 'api.ping',
			'path' => '/api/ping/',
			'middleware' => App\Action\PingAction::class,
			'allowed_methods' => ['GET'],
	]
];

