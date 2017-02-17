<?php
/**
 * It's free open-source software released under the MIT License.
 *
 * @author Anatoly Fenric <a.fenric@gmail.com>
 * @copyright Copyright (c) 2013-2017 by Fenric Laboratory
 * @license https://github.com/fenric/framework/blob/master/LICENSE.md
 * @link https://github.com/fenric/framework
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
	 *
	 * @var     array
	 * @access  protected
	 */
	protected $routes = [];

	/**
	 * Добавление маршрута соответствующего HTTP методу OPTIONS
	 *
	 * @param   string    $location
	 * @param   string    $controller
	 * @param   Closure   $eavesdropper
	 *
	 * @access  public
	 * @return  object
	 */
	public function options($location, $controller, Closure $eavesdropper = null)
	{
		return $this->add(['OPTIONS'], $location, $controller, $eavesdropper);
	}

	/**
	 * Добавление маршрута соответствующего HTTP методу HEAD
	 *
	 * @param   string    $location
	 * @param   string    $controller
	 * @param   Closure   $eavesdropper
	 *
	 * @access  public
	 * @return  object
	 */
	public function head($location, $controller, Closure $eavesdropper = null)
	{
		return $this->add(['HEAD'], $location, $controller, $eavesdropper);
	}

	/**
	 * Добавление маршрута соответствующего HTTP методу GET
	 *
	 * @param   string    $location
	 * @param   string    $controller
	 * @param   Closure   $eavesdropper
	 *
	 * @access  public
	 * @return  object
	 */
	public function get($location, $controller, Closure $eavesdropper = null)
	{
		return $this->add(['GET'], $location, $controller, $eavesdropper);
	}

	/**
	 * Добавление маршрута соответствующего HTTP методу POST
	 *
	 * @param   string    $location
	 * @param   string    $controller
	 * @param   Closure   $eavesdropper
	 *
	 * @access  public
	 * @return  object
	 */
	public function post($location, $controller, Closure $eavesdropper = null)
	{
		return $this->add(['POST'], $location, $controller, $eavesdropper);
	}

	/**
	 * Добавление маршрута соответствующего HTTP методу PUT
	 *
	 * @param   string    $location
	 * @param   string    $controller
	 * @param   Closure   $eavesdropper
	 *
	 * @access  public
	 * @return  object
	 */
	public function put($location, $controller, Closure $eavesdropper = null)
	{
		return $this->add(['PUT'], $location, $controller, $eavesdropper);
	}

	/**
	 * Добавление маршрута соответствующего HTTP методу PATCH
	 *
	 * @param   string    $location
	 * @param   string    $controller
	 * @param   Closure   $eavesdropper
	 *
	 * @access  public
	 * @return  object
	 */
	public function patch($location, $controller, Closure $eavesdropper = null)
	{
		return $this->add(['PATCH'], $location, $controller, $eavesdropper);
	}

	/**
	 * Добавление маршрута соответствующего HTTP методу DELETE
	 *
	 * @param   string    $location
	 * @param   string    $controller
	 * @param   Closure   $eavesdropper
	 *
	 * @access  public
	 * @return  object
	 */
	public function delete($location, $controller, Closure $eavesdropper = null)
	{
		return $this->add(['DELETE'], $location, $controller, $eavesdropper);
	}

	/**
	 * Добавление маршрута соответствующего безопасному HTTP методу
	 *
	 * @param   string    $location
	 * @param   string    $controller
	 * @param   Closure   $eavesdropper
	 *
	 * @access  public
	 * @return  object
	 */
	public function safe($location, $controller, Closure $eavesdropper = null)
	{
		return $this->add(['GET', 'POST'], $location, $controller, $eavesdropper);
	}

	/**
	 * Добавление маршрута соответствующего любому HTTP методу
	 *
	 * @param   string    $location
	 * @param   string    $controller
	 * @param   Closure   $eavesdropper
	 *
	 * @access  public
	 * @return  object
	 */
	public function any($location, $controller, Closure $eavesdropper = null)
	{
		$methods = ['OPTIONS', 'HEAD', 'GET', 'POST', 'PUT', 'PATCH', 'DELETE'];

		return $this->add($methods, $location, $controller, $eavesdropper);
	}

	/**
	 * Добавление маршрута
	 *
	 * @param   array     $methods
	 * @param   string    $location
	 * @param   string    $controller
	 * @param   Closure   $eavesdropper
	 *
	 * @access  public
	 * @return  object
	 */
	public function add(array $methods, $location, $controller, Closure $eavesdropper = null)
	{
		$this->routes[] = [$methods, $location, $controller, $eavesdropper];

		return $this;
	}

	/**
	 * Определение маршрута соответствующего текущему HTTP запросу
	 *
	 * @param   object   $request
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
									$parameters = array_filter($parameters, function($value)
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
	 *
	 * @param   object    $request
	 * @param   object    $response
	 * @param   Closure   $digression
	 *
	 * @access  public
	 * @return  bool
	 */
	public function run(Request $request, Response $response, Closure $digression = null)
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
							$controller->run();
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

		$routePath = str_replace(['(', '*', ')'], ['(?:', '.*', ')?'], $routePath);

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
