<?php
/**
 * It is free open-source software released under the MIT License.
 *
 * @author       Anatoly Nekhay <a.fenric@gmail.com>
 * @copyright    Copyright (c) 2013-2016 by Fenric Laboratory
 * @license      https://github.com/fenric/framework/blob/master/LICENSE.md
 * @link         https://github.com/fenric/framework
 */

namespace Fenric;

/**
 * Router
 */
class Router
{

	/**
	 * Карта маршрутов
	 *
	 * @var     array
	 * @access  protected
	 */
	protected $routes = [];

	/**
	 * Добавление маршрута соответствующего HTTP методу OPTIONS
	 *
	 * @param   string   $location
	 * @param   string   $controller
	 *
	 * @access  public
	 * @return  object
	 */
	public function options($location, $controller)
	{
		return $this->add(['OPTIONS'], $location, $controller);
	}

	/**
	 * Добавление маршрута соответствующего HTTP методу HEAD
	 *
	 * @param   string   $location
	 * @param   string   $controller
	 *
	 * @access  public
	 * @return  object
	 */
	public function head($location, $controller)
	{
		return $this->add(['HEAD'], $location, $controller);
	}

	/**
	 * Добавление маршрута соответствующего HTTP методу GET
	 *
	 * @param   string   $location
	 * @param   string   $controller
	 *
	 * @access  public
	 * @return  object
	 */
	public function get($location, $controller)
	{
		return $this->add(['GET'], $location, $controller);
	}

	/**
	 * Добавление маршрута соответствующего HTTP методу POST
	 *
	 * @param   string   $location
	 * @param   string   $controller
	 *
	 * @access  public
	 * @return  object
	 */
	public function post($location, $controller)
	{
		return $this->add(['POST'], $location, $controller);
	}

	/**
	 * Добавление маршрута соответствующего HTTP методу PUT
	 *
	 * @param   string   $location
	 * @param   string   $controller
	 *
	 * @access  public
	 * @return  object
	 */
	public function put($location, $controller)
	{
		return $this->add(['PUT'], $location, $controller);
	}

	/**
	 * Добавление маршрута соответствующего HTTP методу PATCH
	 *
	 * @param   string   $location
	 * @param   string   $controller
	 *
	 * @access  public
	 * @return  object
	 */
	public function patch($location, $controller)
	{
		return $this->add(['PATCH'], $location, $controller);
	}

	/**
	 * Добавление маршрута соответствующего HTTP методу DELETE
	 *
	 * @param   string   $location
	 * @param   string   $controller
	 *
	 * @access  public
	 * @return  object
	 */
	public function delete($location, $controller)
	{
		return $this->add(['DELETE'], $location, $controller);
	}

	/**
	 * Добавление маршрута соответствующего безопасному HTTP методу
	 *
	 * @param   string   $location
	 * @param   string   $controller
	 *
	 * @access  public
	 * @return  object
	 */
	public function safe($location, $controller)
	{
		return $this->add(['GET', 'POST'], $location, $controller);
	}

	/**
	 * Добавление маршрута соответствующего любому HTTP методу
	 *
	 * @param   string   $location
	 * @param   string   $controller
	 *
	 * @access  public
	 * @return  object
	 */
	public function any($location, $controller)
	{
		$methods = ['OPTIONS', 'HEAD', 'GET', 'POST', 'PUT', 'PATCH', 'DELETE'];

		return $this->add($methods, $location, $controller);
	}

	/**
	 * Добавление маршрута
	 *
	 * @param   array    $methods
	 * @param   string   $location
	 * @param   string   $controller
	 *
	 * @access  public
	 * @return  object
	 */
	public function add(array $methods, $location, $controller)
	{
		$this->routes[] = [$methods, $location, $controller];

		return $this;
	}

	/**
	 * Определение маршрута соответствующего текущему HTTP запросу
	 *
	 * @param   object  $request
	 *
	 * @access  public
	 * @return  mixed
	 */
	public function match(Request $request)
	{
		if (count($this->routes) > 0)
		{
			foreach ($this->routes as $route)
			{
				list($methods, $location, $controller) = $route;

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
									$parameters = array_filter($parameters, function($value)
									{
										return strlen($value) > 0;
									});

									return ['controller' => $controller, 'parameters' => $parameters];
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
	 *
	 * @param   object  $request
	 * @param   object  $response
	 *
	 * @access  public
	 * @return  bool
	 */
	public function run(Request $request, Response $response)
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
							$controller->init();
							$controller->run();
							$controller->render();

							return true;
						}
					}
				}
			}
		}
	}

	/**
	 * Преобразование пути маршрута в регулярное выражение
	 *
	 * @param   string   $routePath
	 *
	 * @access  protected
	 * @return  string
	 */
	protected function convertRoutePathToRegularExpression($routePath)
	{
		$extractedExpressions = $this->extractRegularExpressionsFromRoutePathParameters($routePath);

		$creatingSubpatternsOfParameters = function($match) use($extractedExpressions)
		{
			return '(?<' . $match[1] . '>' . $extractedExpressions[$match[1]] . ')';
		};

		$routePath = addcslashes($routePath, '\.+?[^]${}=!|:-#');

		$routePath = str_replace('*', '.*', $routePath);

		$routePath = str_replace(['(', ')'], ['(?:', ')?'], $routePath);

		$routePath = preg_replace_callback('/<(\w+)>/', $creatingSubpatternsOfParameters, $routePath);

		return '#^' . $routePath . '$#u';
	}

	/**
	 * Извлечение регулярных выражений из параметров пути маршрута
	 *
	 * @param   string   $routePath
	 *
	 * @access  protected
	 * @return  array
	 */
	protected function extractRegularExpressionsFromRoutePathParameters(& $routePath)
	{
		$extractedExpressions = [];

		$expressionForSearchParameters = '/<(\w+)(?::([^>]+))?>/';

		if (preg_match_all($expressionForSearchParameters, $routePath, $matches, PREG_SET_ORDER))
		{
			$routePath = preg_replace($expressionForSearchParameters, '<\1>', $routePath);

			foreach ($matches as $match)
			{
				$extractedExpressions[$match[1]] = isset($match[2]) ? $match[2] : '[^/]+';
			}
		}

		return $extractedExpressions;
	}
}
