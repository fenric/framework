<?php
/**
 * It's free open-source software released under the MIT License.
 *
 * @author Anatoly Fenric <anatoly.fenric@gmail.com>
 * @copyright Copyright (c) 2013-2018 by Fenric Laboratory
 * @license https://github.com/fenric/framework/blob/master/LICENSE.md
 * @link https://github.com/fenric/framework
 */

namespace Fenric;

/**
 * Import classes
 */
use Closure;
use ReflectionClass;
use ReflectionFunction;

/**
 * Router
 */
class Router
{

	/**
	 * Карта маршрутов
	 */
	protected $map = [];

	/**
	 * Группы маршрутов
	 */
	protected $groups = [];

	/**
	 * Шаблоны параметров маршрутов
	 */
	protected $patterns = [];

	/**
	 * Контроллер по умолчанию
	 */
	protected $default;

	/**
	 * Загрузка карты маршрутов
	 */
	public function map() : self
	{
		$this->group(function()
		{
			$this->prefix('/api');

			$this->namespace('\\Fenric\\Controllers\\Api\\');

			$this->middleware(function($request, $response)
			{
				return fenric('event::router.api.middleware')->run([$request, $response]);
			});

			if (fenric('/routes/api.local.php')->isFile())
			{
				require fenric('/routes/api.local.php')->getRealPath();
			}
			else if (fenric('/routes/api.php')->isFile())
			{
				require fenric('/routes/api.php')->getRealPath();
			}
			else if (fenric('/routes/api.example.php')->isFile())
			{
				require fenric('/routes/api.example.php')->getRealPath();
			}
		});

		$this->group(function()
		{
			$this->namespace('\\Fenric\\Controllers\\');

			$this->middleware(function($request, $response)
			{
				return fenric('event::router.web.middleware')->run([$request, $response]);
			});

			if (fenric('/routes/web.local.php')->isFile())
			{
				require fenric('/routes/web.local.php')->getRealPath();
			}
			else if (fenric('/routes/web.php')->isFile())
			{
				require fenric('/routes/web.php')->getRealPath();
			}
			else if (fenric('/routes/web.example.php')->isFile())
			{
				require fenric('/routes/web.example.php')->getRealPath();
			}
		});

		return $this;
	}

	/**
	 * Установка глобального шаблона для параметра маршрутов
	 */
	public function pattern(string $key, string $pattern) : self
	{
		$this->patterns[$key] = $pattern;

		return $this;
	}

	/**
	 * Установка контроллера для домашнего адреса
	 */
	public function home($controller) : self
	{
		return $this->get('/', $controller);
	}

	/**
	 * Установка контроллера по умолчанию
	 */
	public function default($controller) : self
	{
		$this->default = $controller;

		return $this;
	}

	/**
	 * Добавление маршрута соответствующего HTTP методу OPTIONS
	 */
	public function options(string $location, $controller) : self
	{
		return $this->add(['OPTIONS'], $location, $controller);
	}

	/**
	 * Добавление маршрута соответствующего HTTP методу HEAD
	 */
	public function head(string $location, $controller) : self
	{
		return $this->add(['HEAD'], $location, $controller);
	}

	/**
	 * Добавление маршрута соответствующего HTTP методу GET
	 */
	public function get(string $location, $controller) : self
	{
		return $this->add(['GET'], $location, $controller);
	}

	/**
	 * Добавление маршрута соответствующего HTTP методу POST
	 */
	public function post(string $location, $controller) : self
	{
		return $this->add(['POST'], $location, $controller);
	}

	/**
	 * Добавление маршрута соответствующего HTTP методу PATCH
	 */
	public function patch(string $location, $controller) : self
	{
		return $this->add(['PATCH'], $location, $controller);
	}

	/**
	 * Добавление маршрута соответствующего HTTP методу DELETE
	 */
	public function delete(string $location, $controller) : self
	{
		return $this->add(['DELETE'], $location, $controller);
	}

	/**
	 * Добавление маршрута соответствующего HTTP методу PUT
	 */
	public function put(string $location, $controller) : self
	{
		return $this->add(['PUT'], $location, $controller);
	}

	/**
	 * Добавление маршрута соответствующего безопасному HTTP методу
	 */
	public function safe(string $location, $controller) : self
	{
		return $this->add(['GET', 'POST'], $location, $controller);
	}

	/**
	 * Добавление маршрута соответствующего любому HTTP методу
	 */
	public function any(string $location, $controller) : self
	{
		$methods = ['OPTIONS', 'HEAD', 'GET', 'POST', 'PATCH', 'DELETE', 'PUT'];

		return $this->add($methods, $location, $controller);
	}

	/**
	 * Добавление маршрута
	 */
	public function add(array $methods, string $location, $controller) : self
	{
		end($this->groups);

		$key = key($this->groups);

		reset($this->groups);

		if (isset($this->groups[$key]['prefix']))
		{
			$location = $this->groups[$key]['prefix'] . $location;
		}

		if (isset($this->groups[$key]['namespace']) && is_string($controller))
		{
			$controller = $this->groups[$key]['namespace'] . $controller;
		}

		if (isset($this->groups[$key]['middleware']))
		{
			$middleware = $this->groups[$key]['middleware'];
		}

		$this->map[] = [$methods, $location, $controller, $middleware ?? function()
		{
			return true;
		}];

		return $this;
	}

	/**
	 * Создание группы маршрутов
	 */
	public function group(Closure $callback) : void
	{
		$last = [];

		if (count($this->groups) > 0)
		{
			$last = end($this->groups);

			reset($this->groups);
		}

		$this->groups[] = $last;

		$callback();

		array_pop($this->groups);
	}

	/**
	 * Установка префикса для группы маршрутов
	 */
	public function prefix(string $prefix) : self
	{
		end($this->groups);

		$key = key($this->groups);

		reset($this->groups);

		if (isset($this->groups[$key]['prefix']))
		{
			$prefix = $this->groups[$key]['prefix'] . $prefix;
		}

		$this->groups[$key]['prefix'] = $prefix;

		return $this;
	}

	/**
	 * Установка пространства имен для группы маршрутов
	 */
	public function namespace(string $namespace) : self
	{
		end($this->groups);

		$key = key($this->groups);

		reset($this->groups);

		if (isset($this->groups[$key]['namespace']))
		{
			$namespace = $this->groups[$key]['namespace'] . $namespace;
		}

		$this->groups[$key]['namespace'] = $namespace;

		return $this;
	}

	/**
	 * Установка промежуточной логики для группы маршрутов
	 */
	public function middleware(Closure $middleware) : self
	{
		end($this->groups);

		$key = key($this->groups);

		reset($this->groups);

		if (isset($this->groups[$key]['middleware']))
		{
			$previous = $this->groups[$key]['middleware'];

			$middleware = function(...$args) use($previous, $middleware) : bool
			{
				return $previous(...$args) && $middleware(...$args);
			};
		}

		$this->groups[$key]['middleware'] = $middleware;

		return $this;
	}

	/**
	 * Запуск маршрутизатора
	 */
	public function run(Request $request = null, Response $response = null) : bool
	{
		$request = $request ?: fenric('request');
		$response = $response ?: fenric('response');

		if (count($this->map) > 0)
		{
			foreach ($this->map as $route)
			{
				list($methods, $location, $controller, $middleware) = $route;

				if (in_array($request->method(), $methods))
				{
					$pattern = $this->convertRoutePathToRegularExpression($request->root() . $location);

					if (preg_match($pattern, $request->path(), $parameters))
					{
						$request->parameters->upgrade($parameters)->filter();

						if ($middleware($request, $response))
						{
							if ($this->execute($controller, $request, $response))
							{
								return true;
							}
						}
					}
				}
			}
		}

		if ($this->execute($this->default, $request, $response))
		{
			return true;
		}

		/**
		 * @todo maybe, throw exception?
		 */

		return false;
	}

	/**
	 * Выполнение контроллера
	 */
	protected function execute($controller, Request $request, Response $response) : bool
	{
		if (is_string($controller))
		{
			if (class_exists($controller))
			{
				$reflector = new ReflectionClass($controller);

				if ($reflector->isInstantiable())
				{
					if ($reflector->isSubclassOf(Controller::class))
					{
						$instance = $reflector->newInstance($request, $response);

						if ($instance->preInit())
						{
							$instance->init();
							$instance->render();

							return true;
						}
					}
				}
			}
		}

		if (is_callable($controller))
		{
			$reflector = new ReflectionFunction($controller);

			if ($reflector->isUserDefined())
			{
				$callback = $reflector->getClosure();

				$callback($request, $response);

				return true;
			}
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

		$routePath = str_replace(['(', '*', '%', ')'], ['(?:', '[^/]*', '.*', ')?'], $routePath);

		$routePath = preg_replace_callback('/<(\w+)>/', $creatingSubpatternsOfParameters, $routePath);

		return '#^' . $routePath . '$#u';
	}

	/**
	 * Извлечение регулярных выражений из параметров пути маршрута
	 */
	protected function extractRegularExpressionsFromRoutePathParameters(string & $routePath) : array
	{
		$extractedExpressions = [];

		$expressionForSearchParameters = '/<(\w+)(?::([^<>]+))?>/';

		if (preg_match_all($expressionForSearchParameters, $routePath, $matches, PREG_SET_ORDER))
		{
			$routePath = preg_replace($expressionForSearchParameters, '<\1>', $routePath);

			foreach ($matches as $match)
			{
				$extractedExpressions[$match[1]] = $match[2] ?? $this->patterns[$match[1]] ?? '[^/]+';
			}
		}

		return $extractedExpressions;
	}
}
