<?php

namespace GabiDJ\Expressive\SRouter;

use Zend\Expressive\Router\RouterInterface as RouterInterface;
use Zend\Expressive\Router\Route as Route;
use Zend\Expressive\Router\RouteResult as RouteResult;
use Zend\Diactoros\ServerRequest as ServerRequest;
use Zend\Stratigility\MiddlewareInterface;

class RouterTest extends \PhpUnit_Framework_TestCase
{
	const URI_PREFIX = 'http://localhost:8080';
	
	private $_isRouterPrepared = false;
	private $_router;
	
	private function _prepareRouter()
	{
		if(!$this->_isRouterPrepared)
		{
			$methods = array('POST','GET');
			$this->_router = new SRouter();
			$mockMiddleware = function(){};
			$this->_router->addRoute(new Route('/', $mockMiddleware, $methods, 'index'));
			$this->_router->addRoute(new Route('/api/ping', $mockMiddleware, $methods,'api.ping'));
			$this->_router->addRoute(new Route('/api/ping/2/', $mockMiddleware, $methods,'api.ping2'));
			$this->_isRouterPrepared = true; 
		}
	}
	
	/** @dataProvider provideTestRouteParse */
	public function testRouteMatch($url, $expectedRouteName)
	{
		$uri = self::URI_PREFIX.$url;
		$request = new ServerRequest(['PATH_INFO'=>$uri], [], $uri, 'POST') ;
		
		$this->_prepareRouter();
		
		$matchRouteResult = $this->_router->match($request);
		$matchedRouteName = $matchRouteResult->getMatchedRouteName();
		$this->assertEquals($matchedRouteName, $expectedRouteName);
	}
	
	/** @dataProvider provideGenerateUri */
	public function testGenerateUri($routeName, $substitutions, $expectedRouteUri)
	{
		$this->_prepareRouter();
		$uri = $this->_router->generateUri($routeName, $substitutions);
		$this->assertEquals($uri, $expectedRouteUri);
	}
	
	
	
	public function provideTestRouteParse()
	{
		return [
			
			['/', 'index'] ,
			['/api/ping', 'api.ping'] ,
			['/api/ping/', 'api.ping'] ,
			['/api/ping/2', 'api.ping2'] ,
			['/api/ping/2/', 'api.ping2'] ,
			['/api/ping/2//', 'api.ping2'] ,
			['/api/ping/2/a/b', 'api.ping2'] ,
		];
	}
	
	public function provideGenerateUri()
	{
		$replacements = ['.'=>'/']; 
		return [
			['index', $replacements, '/index'] ,
			['api.ping', $replacements,'/api/ping'] ,
			['api.ping2', $replacements, '/api/ping2'] ,
		];
	}
}
