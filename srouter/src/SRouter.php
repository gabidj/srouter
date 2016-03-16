<?php

namespace GabiDJ\Expressive\SRouter;

use Zend\Expressive\Router\RouterInterface as RouterInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Zend\Expressive\Router\Route as Route;
use Zend\Expressive\Router\RouteResult as RouteResult;

class SRouter implements RouterInterface
{
	/**
	 * Routes list 
	 * @var Route[]
	 */
	private $_routes = array();
	
	/**
	 * Used for forbidding route adding after match() or generateUri() is called
	 * @var bool
	 */
	private $_forbidRouteAdd = false;
	
	/**
	 * Injectable constructor.
	 * @param Router|null $router
	 */
	public function __construct(SRouter $router = null)
	{
		if(null !== $router)
		{
			return $router;
		}
	}
	
	/**
	 * Add a route.
	 *
	 * This method adds a route against which the underlying implementation may
	 * match. Implementations MUST aggregate route instances, but MUST NOT use
	 * the details to inject the underlying router until `match()` and/or
	 * `generateUri()` is called.  This is required to allow consumers to
	 * modify route instances before matching (e.g., to provide route options,
	 * inject a name, etc.).
	 *
	 * The method MUST raise Exception\RuntimeException if called after either `match()`
	 * or `generateUri()` have already been called, to ensure integrity of the
	 * router between invocations of either of those methods.
	 *
	 * @param Route $route
	 * @throws Exception\RuntimeException when called after match() or
	 *     generateUri() have been called.
	 */
	public function addRoute(Route $route)
	{
		if($this->_forbidRouteAdd)
		{
			throw new Exception('called after match() or generateUri() have been called');
		}
		$routeName = $route->getName();
		// currently allowing all methods
		#$methods = $route->getAllowedMethods();
		
		if(isset($this->_routes[$routeName] ))
		{
			throw new \Exception('Route already defined');
		}
		$this->_routes[$routeName] = $route;
	}
	
	/**
	 * Match a request against the known routes.
	 *
	 * Implementations will aggregate required information from the provided
	 * request instance, and pass them to the underlying router implementation;
	 * when done, they will then marshal a `RouteResult` instance indicating
	 * the results of the matching operation and return it to the caller.
	 *
	 * @param  Request $request
	 * @return RouteResult
	 */
	public function match(Request $request)
	{
		$this->_forbidRouteAdd = true;
		$params = $request->getServerParams();
		$path = $params['PATH_INFO'];
		
		// get the matched route
		$matchedRoute = $this->_matchPathToRoutePath($path);

		// check if route exists
		if($matchedRoute instanceof Route)
		{
			return RouteResult::fromRouteMatch($matchedRoute->getName(), $matchedRoute->getMiddleware(), $params);
		}
		return RouteResult::fromRouteFailure();
	}
	
	/**
	 * Generate a URI from the named route.
	 *
	 * Takes the named route and any substitutions, and attempts to generate a
	 * URI from it.
	 *
	 * The URI generated MUST NOT be escaped. If you wish to escape any part of
	 * the URI, this should be performed afterwards; consider passing the URI
	 * to league/uri to encode it.
	 *
	 * @see https://github.com/auraphp/Aura.Router#generating-a-route-path
	 * @see http://framework.zend.com/manual/current/en/modules/zend.mvc.routing.html
	 * @param string $name
	 * @param array $substitutions
	 * @return string
	 * @throws \Exception if unable to generate the given URI.
	 */
	public function generateUri($name, array $substitutions = [])
	{
		if(empty($substitutions ))
		{
			throw new \Exception('No substitutions found. URI cannot be generated');
		}
		$uri = $name; 
		foreach($substitutions as $search => $replace)
		{
			$uri = str_replace($search, $replace, $uri);
		}
		return '/'. $uri;
	}
	
	/**
	 * Match Path through Routes
	 * 
	 * Simple text search matching
	 * Returns the matched route, or false if no route works
	 * The routing does "forgive" 2 double slashes, but just once
	 * 
	 * @access private
	 * @param string $path
	 * @param bool $routeToIndexOnFail [optional] - default is false, used for routing to '/' in case no route matched
	 * @return mixed(Route|bool)
	 */
	private function _matchPathToRoutePath($path, $routeToIndexOnFail = false)
	{
		// how many slashes to "forgive" 
		$count = 1;
		// add an extra slash 
		$parsedPath = str_replace('//', '/', $path, $count).'/';
		$lastStrlen = -1; 
		// no route 
		$lastRoute = false;
		foreach ($this->_routes as $route)
		{
			$routePath = $route->getPath();
			if($routePath == $parsedPath || $routePath.'/' == $parsedPath)
			{
				return $route;
			}
			$stripos = stripos($parsedPath, $routePath);
			
			$currentStrlen = strlen($routePath);
			if($stripos !== false && $currentStrlen > $lastStrlen)
			{
				$lastStrlen = $currentStrlen;
				$lastRoute = $route;
			}
		}
		if($lastStrlen > 0)
		{
			return $lastRoute;
		}
		if($routeToIndexOnFail == true)
		{
			return $this->_matchPathToRoutePath('/');
		}
		return false;
	}
}