<?php
/**
 * (c) Fenric Lab, 2010-2016
 *
 * @product      Fenric Framework
 * @author       Anatoly Nekhay E.
 * @email        support@fenric.ru
 * @site         http://fenric.ru/
 */

namespace Fenric;

/**
 * Import classes
 */
use Closure;

/**
 * Router
 */
class Router extends Object
{

	/**
	 * Карта маршрутов
	 *
	 * @var     array
	 * @access  protected
	 */
	protected $routes = [];

	/**
	 * Экземпляр HTTP объекта для обработки запросов клиента
	 *
	 * @var     object
	 * @access  protected
	 */
	protected $request;

	/**
	 * Экземпляр HTTP объекта для генерации ответа клиенту
	 *
	 * @var     object
	 * @access  protected
	 */
	protected $response;

	/**
	 * Конструктор класса
	 *
	 * @param   object  $request
	 * @param   object  $response
	 *
	 * @access  public
	 * @return  void
	 */
	public function __construct(Request $request, Response $response)
	{
		$this->request = $request;

		$this->response = $response;
	}

	/**
	 * Получение экземпляра HTTP объекта для обработки запросов клиента
	 *
	 * @access  public
	 * @return  object
	 */
	public function getRequest()
	{
		return $this->request;
	}

	/**
	 * Получение экземпляра HTTP объекта для генерации ответа клиенту
	 *
	 * @access  public
	 * @return  object
	 */
	public function getResponse()
	{
		return $this->response;
	}

	/**
	 * Добавление маршрута соответствующего HTTP методу OPTIONS
	 *
	 * @param   string   $location
	 * @param   string   $controller
	 * @param   Closure  $satellite
	 *
	 * @access  public
	 * @return  object
	 */
	public function options($location, $controller, Closure $satellite = null)
	{
		return $this->add(['OPTIONS'], $location, $controller, $satellite);
	}

	/**
	 * Добавление маршрута соответствующего HTTP методу HEAD
	 *
	 * @param   string   $location
	 * @param   string   $controller
	 * @param   Closure  $satellite
	 *
	 * @access  public
	 * @return  object
	 */
	public function head($location, $controller, Closure $satellite = null)
	{
		return $this->add(['HEAD'], $location, $controller, $satellite);
	}

	/**
	 * Добавление маршрута соответствующего HTTP методу GET
	 *
	 * @param   string   $location
	 * @param   string   $controller
	 * @param   Closure  $satellite
	 *
	 * @access  public
	 * @return  object
	 */
	public function get($location, $controller, Closure $satellite = null)
	{
		return $this->add(['GET'], $location, $controller, $satellite);
	}

	/**
	 * Добавление маршрута соответствующего HTTP методу POST
	 *
	 * @param   string   $location
	 * @param   string   $controller
	 * @param   Closure  $satellite
	 *
	 * @access  public
	 * @return  object
	 */
	public function post($location, $controller, Closure $satellite = null)
	{
		return $this->add(['POST'], $location, $controller, $satellite);
	}

	/**
	 * Добавление маршрута соответствующего HTTP методу PUT
	 *
	 * @param   string   $location
	 * @param   string   $controller
	 * @param   Closure  $satellite
	 *
	 * @access  public
	 * @return  object
	 */
	public function put($location, $controller, Closure $satellite = null)
	{
		return $this->add(['PUT'], $location, $controller, $satellite);
	}

	/**
	 * Добавление маршрута соответствующего HTTP методу PATCH
	 *
	 * @param   string   $location
	 * @param   string   $controller
	 * @param   Closure  $satellite
	 *
	 * @access  public
	 * @return  object
	 */
	public function patch($location, $controller, Closure $satellite = null)
	{
		return $this->add(['PATCH'], $location, $controller, $satellite);
	}

	/**
	 * Добавление маршрута соответствующего HTTP методу DELETE
	 *
	 * @param   string   $location
	 * @param   string   $controller
	 * @param   Closure  $satellite
	 *
	 * @access  public
	 * @return  object
	 */
	public function delete($location, $controller, Closure $satellite = null)
	{
		return $this->add(['DELETE'], $location, $controller, $satellite);
	}

	/**
	 * Добавление маршрута соответствующего безопасному HTTP методу
	 *
	 * @param   string   $location
	 * @param   string   $controller
	 * @param   Closure  $satellite
	 *
	 * @access  public
	 * @return  object
	 */
	public function safe($location, $controller, Closure $satellite = null)
	{
		return $this->add(['GET', 'POST'], $location, $controller, $satellite);
	}

	/**
	 * Добавление маршрута соответствующего любому HTTP методу
	 *
	 * @param   string   $location
	 * @param   string   $controller
	 * @param   Closure  $satellite
	 *
	 * @access  public
	 * @return  object
	 */
	public function any($location, $controller, Closure $satellite = null)
	{
		$methods = ['OPTIONS', 'HEAD', 'GET', 'POST', 'PUT', 'PATCH', 'DELETE'];

		return $this->add($methods, $location, $controller, $satellite);
	}

	/**
	 * Добавление маршрута
	 *
	 * @param   array    $methods
	 * @param   string   $location
	 * @param   string   $controller
	 * @param   Closure  $satellite
	 *
	 * @access  public
	 * @return  object
	 */
	public function add(array $methods, $location, $controller, Closure $satellite = null)
	{
		$this->routes[] = [$methods, $location, $controller, $satellite];

		return $this;
	}

	/**
	 * Определение маршрута соответствующего текущему HTTP запросу
	 *
	 * @access  public
	 * @return  array
	 */
	public function match()
	{
		if (count($this->routes) > 0)
		{
			foreach ($this->routes as $route)
			{
				list($methods, $location, $controller, $satellite) = $route;

				if (in_array($this->getRequest()->getMethod(), $methods, true))
				{
					if ($location = parse_url($location))
					{
						$location +=
						[
							'host' => $this->getRequest()->getHost(),
							'port' => $this->getRequest()->getPort(),
							'path' => $this->getRequest()->getPath(),
						];

						if (strcmp($this->getRequest()->getHost(), $location['host']) === 0)
						{
							if (strcmp($this->getRequest()->getPort(), $location['port']) === 0)
							{
								if (strlen($this->getRequest()->getRoot()) > 0)
								{
									$location['path'] = $this->getRequest()->getRoot() . $location['path'];
								}

								$expression = $this->convertRoutePathToRegularExpression($location['path']);

								if (preg_match($expression, $this->getRequest()->getPath(), $parameters))
								{
									$parameters = new Collection(array_filter($parameters, function($value)
									{
										return strlen($value) > 0;
									}));

									return ['parameters' => $parameters, 'controller' => $controller, 'satellite' => $satellite];
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
	 * @access  public
	 * @return  bool
	 *
	 * @throws  RouterException
	 */
	public function run()
	{
		$code = 404;

		if ($route = $this->match())
		{
			if (is_string($route['controller']))
			{
				if (class_exists($route['controller']))
				{
					if (is_subclass_of($route['controller'], Controller::class))
					{
						$controller = new $route['controller']($this, $route['parameters']);

						if ($route['satellite'] instanceof Closure)
						{
							$route['satellite']($controller);
						}

						if ($controller->preInit())
						{
							$controller->trigger('beforeInit');
							$controller->init();
							$controller->trigger('afterInit');

							$controller->trigger('beforeRun');
							$controller->run();
							$controller->trigger('afterRun');

							$controller->trigger('beforeRender');
							$controller->render();
							$controller->trigger('afterRender');

							return true;
						}

						$code = $controller->code;
					}
				}
			}
		}

		throw new RouterException($code);
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

		$routePath = (substr_count($routePath, '(') !== substr_count($routePath, ')')) ? addcslashes($routePath, '()') : str_replace(['(', ')'], ['(?:', ')?'], $routePath);

		$routePath = (substr_count($routePath, '<') !== substr_count($routePath, '>')) ? addcslashes($routePath, '<>') : preg_replace_callback('/<(\w+)>/', $creatingSubpatternsOfParameters, $routePath);

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
