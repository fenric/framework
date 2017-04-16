<?php
/**
 * It's free open-source software released under the MIT License.
 *
 * @author Anatoly Fenric <a.fenric@gmail.com>
 * @copyright Copyright (c) 2013-2017 by Fenric Laboratory
 * @license https://github.com/fenric/framework.core/blob/master/LICENSE.md
 * @link https://github.com/fenric/framework.core
 */

namespace Fenric;

/**
 * Import classes
 */
use Closure;

/**
 * Router
 */
class Router
{

	/**
	 * Карта маршрутов
	 */
	protected $routes = [];

	/**
	 * Добавление маршрута соответствующего HTTP методу OPTIONS
	 */
	public function options(string $location, string $controller, Closure $eavesdropper = null) : self
	{
		return $this->add(['OPTIONS'], $location, $controller, $eavesdropper);
	}

	/**
	 * Добавление маршрута соответствующего HTTP методу HEAD
	 */
	public function head(string $location, string $controller, Closure $eavesdropper = null) : self
	{
		return $this->add(['HEAD'], $location, $controller, $eavesdropper);
	}

	/**
	 * Добавление маршрута соответствующего HTTP методу GET
	 */
	public function get(string $location, string $controller, Closure $eavesdropper = null) : self
	{
		return $this->add(['GET'], $location, $controller, $eavesdropper);
	}

	/**
	 * Добавление маршрута соответствующего HTTP методу POST
	 */
	public function post(string $location, string $controller, Closure $eavesdropper = null) : self
	{
		return $this->add(['POST'], $location, $controller, $eavesdropper);
	}

	/**
	 * Добавление маршрута соответствующего HTTP методу PATCH
	 */
	public function patch(string $location, string $controller, Closure $eavesdropper = null) : self
	{
		return $this->add(['PATCH'], $location, $controller, $eavesdropper);
	}

	/**
	 * Добавление маршрута соответствующего HTTP методу DELETE
	 */
	public function delete(string $location, string $controller, Closure $eavesdropper = null) : self
	{
		return $this->add(['DELETE'], $location, $controller, $eavesdropper);
	}

	/**
	 * Добавление маршрута соответствующего HTTP методу PUT
	 */
	public function put(string $location, string $controller, Closure $eavesdropper = null) : self
	{
		return $this->add(['PUT'], $location, $controller, $eavesdropper);
	}

	/**
	 * Добавление маршрута соответствующего безопасному HTTP методу
	 */
	public function safe(string $location, string $controller, Closure $eavesdropper = null) : self
	{
		return $this->add(['GET', 'POST'], $location, $controller, $eavesdropper);
	}

	/**
	 * Добавление маршрута соответствующего любому HTTP методу
	 */
	public function any(string $location, string $controller, Closure $eavesdropper = null) : self
	{
		$methods = ['OPTIONS', 'HEAD', 'GET', 'POST', 'PUT', 'PATCH', 'DELETE'];

		return $this->add($methods, $location, $controller, $eavesdropper);
	}

	/**
	 * Добавление маршрута
	 */
	public function add(array $methods, string $location, string $controller, Closure $eavesdropper = null) : self
	{
		$this->routes[] = [$methods, $location, $controller, $eavesdropper];

		return $this;
	}

	/**
	 * Определение маршрута соответствующего текущему HTTP запросу
	 */
	public function match(Request $request)
	{
		if (count($this->routes) > 0)
		{
			foreach ($this->routes as $route)
			{
				list($methods, $location, $controller, $eavesdropper) = $route;

				if (in_array($request->getMethod(), $methods))
				{
					if ($location = parse_url($location))
					{
						$location += ['path' => $request->getPath()];

						if (empty($location['host']) || strcmp($request->getHost(), $location['host']) === 0)
						{
							if (empty($location['port']) || strcmp($request->getPort(), $location['port']) === 0)
							{
								$expression = $this->convertRoutePathToRegularExpression($request->getRoot() . $location['path']);

								if (preg_match($expression, $request->getPath(), $parameters))
								{
									$parameters = array_filter($parameters, function($value) : bool
									{
										return strlen($value) > 0;
									});

									return ['controller' => $controller, 'eavesdropper' => $eavesdropper, 'parameters' => $parameters];
								}
							}
						}
					}
				}
			}
		}
	}

	/**
	 * Запуск маршрутизатора
	 */
	public function run(Request $request, Response $response, Closure $digression = null) : bool
	{
		$response->setStatus(404);

		if ($match = $this->match($request))
		{
			if (is_string($match['controller']))
			{
				if (class_exists($match['controller']))
				{
					if (is_subclass_of($match['controller'], Controller::class))
					{
						$request->parameters->update($match['parameters']);

						$response->setStatus(200);

						$controller = new $match['controller']($this, $request, $response);

						if ($controller->preInit())
						{
							if ($match['eavesdropper'] instanceof Closure)
							{
								$match['eavesdropper']($this, $request, $response, $controller);
							}

							$controller->init();
							$controller->render();

							return true;
						}
					}
				}
			}
		}

		if ($digression instanceof Closure)
		{
			$digression($this, $request, $response);
		}

		return false;
	}

	/**
	 * Преобразование пути маршрута в регулярное выражение
	 */
	protected function convertRoutePathToRegularExpression(string $routePath) : string
	{
		$extractedExpressions = $this->extractRegularExpressionsFromRoutePathParameters($routePath);

		$creatingSubpatternsOfParameters = function($match) use($extractedExpressions) : string
		{
			return '(?<' . $match[1] . '>' . $extractedExpressions[$match[1]] . ')';
		};

		$routePath = addcslashes($routePath, '\.+?[^]${}=!|:-#');

		$routePath = str_replace(['(', '*', ')'], ['(?:', '.*', ')?'], $routePath);

		$routePath = preg_replace_callback('/<(\w+)>/', $creatingSubpatternsOfParameters, $routePath);

		return '#^' . $routePath . '$#u';
	}

	/**
	 * Извлечение регулярных выражений из параметров пути маршрута
	 */
	protected function extractRegularExpressionsFromRoutePathParameters(string & $routePath) : array
	{
		$extractedExpressions = [];

		$expressionForSearchParameters = '/<(\w+)(?::([^>]+))?>/';

		if (preg_match_all($expressionForSearchParameters, $routePath, $matches, PREG_SET_ORDER))
		{
			$routePath = preg_replace($expressionForSearchParameters, '<\1>', $routePath);

			foreach ($matches as $match)
			{
				$extractedExpressions[$match[1]] = $match[2] ?? '[^/]+';
			}
		}

		return $extractedExpressions;
	}
}
