<?php
/**
 * It is free open-source software released under the MIT License.
 *
 * @author       Anatoly Nekhay <a.fenric@gmail.com>
 * @copyright    Copyright (c) 2013-2016 by Fenric Laboratory
 * @license      https://github.com/fenric/framework/blob/master/LICENSE.md
 * @link         https://github.com/fenric/framework
 */

/**
 * Импортирование классов
 */
use Fenric\Collection;
use Fenric\Event;
use Fenric\Logger;
use Fenric\Request;
use Fenric\Response;
use Fenric\Router;
use Fenric\View;

/**
 * Основной класс фреймворка
 */
final class Fenric
{

	/**
	 * Версия фреймворка
	 */
	const VERSION = '1.7.1-dev';

	/**
	 * Опции фреймворка
	 *
	 * @var     array
	 * @access  protected
	 */
	protected $options = [];

	/**
	 * Службы фреймворка
	 *
	 * @var     array
	 * @access  protected
	 */
	protected $services = [];

	/**
	 * Конструктор класса
	 *
	 * @access  public
	 * @return  void
	 */
	public function __construct()
	{
		/**
		 * Окружение фреймворка
		 *
		 * @var string
		 */
		$this->options['env'] = 'development';

		/**
		 * Автозагрузка внутренних классов
		 *
		 * @var bool
		 */
		$this->options['autoload.enabled'] = true;

		/**
		 * Автозагрузчик внутренних классов
		 *
		 * @var callable
		 */
		$this->options['autoload.handler'] = null;

		/**
		 * Правила в порядке приоритетности по которым осуществляется поиск и загрузка внутренних классов
		 *
		 * @var array
		 */
		$this->options['autoload.rules'] = [':app/classes/:class.php', ':system/classes/:class.php'];

		/**
		 * Обработка ошибок
		 *
		 * @var bool
		 */
		$this->options['handling.error.enabled'] = true;

		/**
		 * Обработчик ошибок
		 *
		 * @var callable
		 */
		$this->options['handling.error.handler'] = null;

		/**
		 * Формат журналирования ошибки
		 *
		 * @var string
		 */
		$this->options['handling.error.log.format'] = '%s in file `%s` on line `%d`.';

		/**
		 * Обработка неперехваченных исключений
		 *
		 * @var bool
		 */
		$this->options['handling.uncaught.exception.enabled'] = true;

		/**
		 * Обработчик неперехваченных исключений
		 *
		 * @var callable
		 */
		$this->options['handling.uncaught.exception.handler'] = null;

		/**
		 * Формат журналирования неперехваченного исключения
		 *
		 * @var string
		 */
		$this->options['handling.uncaught.exception.log.format'] = '%s in file `%s` on line `%d`.';

		/**
		 * Обработка фатальных ошибок
		 *
		 * @var bool
		 */
		$this->options['handling.fatal.error.enabled'] = true;

		/**
		 * Обработчик фатальных ошибок
		 *
		 * @var callable
		 */
		$this->options['handling.fatal.error.handler'] = null;

		/**
		 * Режим протоколирования фатальных ошибок
		 *
		 * @var int
		 */
		$this->options['handling.fatal.error.mode'] = E_ERROR | E_PARSE | E_CORE_ERROR | E_CORE_WARNING | E_COMPILE_ERROR | E_COMPILE_WARNING | E_USER_ERROR;

		/**
		 * Формат журналирования фатальной ошибки
		 *
		 * @var string
		 */
		$this->options['handling.fatal.error.log.format'] = '%s in file `%s` on line `%d`.';

		/**
		 * Язык локализации
		 *
		 * @var string
		 */
		$this->options['locale.language'] = 'en_US';

		/**
		 * Первоисточник языка локализации
		 *
		 * @var string
		 */
		$this->options['locale.original'] = 'ru_RU';

		/**
		 * Путь к родительской директории фреймворка
		 *
		 * @var Closure : string
		 */
		$this->options['paths']['.'] = function()
		{
			return dirname(__DIR__) . DIRECTORY_SEPARATOR;
		};

		/**
		 * Путь к директории приложения созданного на базе фреймворка
		 *
		 * @var Closure : string
		 */
		$this->options['paths']['app'] = function()
		{
			return $this->path('.') . 'app' . DIRECTORY_SEPARATOR;
		};

		/**
		 * Путь к публичной директории фреймворка
		 *
		 * @var Closure : string
		 */
		$this->options['paths']['public'] = function()
		{
			return $this->path('.') . 'public' . DIRECTORY_SEPARATOR;
		};

		/**
		 * Путь к системной директории фреймворка
		 *
		 * @var Closure : string
		 */
		$this->options['paths']['system'] = function()
		{
			return $this->path('.') . 'system' . DIRECTORY_SEPARATOR;
		};

		/**
		 * Путь к директории с исполняемыми файлами
		 *
		 * @var Closure : string
		 */
		$this->options['paths']['bin'] = function()
		{
			return $this->path('app') . 'bin' . DIRECTORY_SEPARATOR;
		};

		/**
		 * Путь к директории с конфигурационными файлами
		 *
		 * @var Closure : string
		 */
		$this->options['paths']['configs'] = function()
		{
			return $this->path('app') . 'configs' . DIRECTORY_SEPARATOR;
		};

		/**
		 * Путь к директории с локализационными файлами
		 *
		 * @var Closure : string
		 */
		$this->options['paths']['locales'] = function()
		{
			return $this->path('app') . 'locales' . DIRECTORY_SEPARATOR;
		};

		/**
		 * Путь к директории с представлениями
		 *
		 * @var Closure : string
		 */
		$this->options['paths']['views'] = function()
		{
			return $this->path('app') . 'views' . DIRECTORY_SEPARATOR;
		};
	}

	/**
	 * Инициализация фреймворка
	 *
	 * @param   array    $options
	 *
	 * @access  public
	 * @return  void
	 */
	public function init(array $options = [])
	{
		$this->options = array_replace_recursive($this->options, $options);

		/**
		 * Регистрация загрузчика классов
		 */
		if ($this->options['autoload.enabled'])
		{
			if (! is_callable($this->options['autoload.handler']))
			{
				$this->options['autoload.handler'] = [$this, 'autoload'];
			}

			spl_autoload_register($this->options['autoload.handler'], true, true);
		}

		/**
		 * Регистрация обработчика ошибок
		 */
		if ($this->options['handling.error.enabled'])
		{
			if (! is_callable($this->options['handling.error.handler']))
			{
				$this->options['handling.error.handler'] = [$this, 'handleError'];
			}

			set_error_handler($this->options['handling.error.handler']);
		}

		/**
		 * Регистрация обработчика неперехваченных исключений
		 */
		if ($this->options['handling.uncaught.exception.enabled'])
		{
			if (! is_callable($this->options['handling.uncaught.exception.handler']))
			{
				$this->options['handling.uncaught.exception.handler'] = [$this, 'handleUncaughtException'];
			}

			set_exception_handler($this->options['handling.uncaught.exception.handler']);
		}

		/**
		 * Регистрация обработчика фатальных ошибок
		 */
		if ($this->options['handling.fatal.error.enabled'])
		{
			if (! is_callable($this->options['handling.fatal.error.handler']))
			{
				$this->options['handling.fatal.error.handler'] = [$this, 'handleFatalError'];
			}

			register_shutdown_function($this->options['handling.fatal.error.handler']);
		}

		/**
		 * Регистрация в контейнере фреймворка службы для работы с событиями
		 */
		$this->registerResolvableSharedService('event', function($resolver = 'default')
		{
			return new Event();
		});

		/**
		 * Регистрация в контейнере фреймворка службы для работы с журналом
		 */
		$this->registerResolvableSharedService('logger', function($resolver = 'default')
		{
			return new Logger();
		});

		/**
		 * Регистрация в контейнере фреймворка службы для работы с конфигурационными файлами
		 */
		$this->registerResolvableSharedService('config', function($resolver = 'default')
		{
			if (file_exists($this->path('configs', "$resolver.local.php")))
			{
				return new Collection(include $this->path('configs', "$resolver.local.php"));
			}
			if (file_exists($this->path('configs', "$resolver.php")))
			{
				return new Collection(include $this->path('configs', "$resolver.php"));
			}

			throw new RuntimeException(sprintf('Unable to find config «%s».', $resolver));
		});

		/**
		 * Регистрация в контейнере фреймворка службы для работы с локализационными файлами
		 */
		$this->registerResolvableSharedService('locale', function($resolver = 'default')
		{
			if (file_exists($this->path('locales', $this->options['locale.language'], "$resolver.php")))
			{
				return new Collection(include $this->path('locales', $this->options['locale.language'], "$resolver.php"));
			}
			if (file_exists($this->path('locales', $this->options['locale.original'], "$resolver.php")))
			{
				return new Collection(include $this->path('locales', $this->options['locale.original'], "$resolver.php"));
			}

			throw new RuntimeException(sprintf('Unable to find locale messages «%s» for language: %s => %s.',
				$resolver, $this->options['locale.language'], $this->options['locale.original']));
		});

		/**
		 * Регистрация в контейнере фреймворка службы для обработки запроса клиента
		 */
		$this->registerDisposableSharedService('request', function()
		{
			$content = file_get_contents('php://input');

			return new Request($_GET, $_POST, $_FILES, $_COOKIE, $_ENV + $_SERVER, [], $content);
		});

		/**
		 * Регистрация в контейнере фреймворка службы для генерации ответа клиенту
		 */
		$this->registerDisposableSharedService('response', function()
		{
			$headers[] = 'X-Powered-By: Fenric framework';

			return new Response(200, $headers, null);
		});

		/**
		 * Регистрация в контейнере фреймворка службы для работы с маршрутизатором
		 */
		$this->registerResolvableSharedService('router', function($resolver = 'default')
		{
			return new Router();
		});

		/**
		 * Регистрация в контейнере фреймворка службы для работы с представлениями
		 */
		$this->registerResolvableSharedService('view', function($resolver, array $variables = null)
		{
			$variables = (array) $variables;

			if (file_exists($this->path('views', "$resolver.local.phtml")))
			{
				return new View($this->path('views', "$resolver.local.phtml"), $variables);
			}
			if (file_exists($this->path('views', "$resolver.phtml")))
			{
				return new View($this->path('views', "$resolver.phtml"), $variables);
			}

			throw new RuntimeException(sprintf('Unable to find view «%s».', $resolver));
		});
	}

	/**
	 * Определение окружения
	 *
	 * @access  public
	 * @return  bool
	 */
	public function is()
	{
		switch (func_get_arg(0))
		{
			case 'cli' :
			case 'console' :
				return strcasecmp(PHP_SAPI, 'cli') === 0;
				break;

			case 'test' :
			case 'debug' :
				return strcasecmp($this->options['env'], 'test') === 0;
				break;

			case 'prod' :
			case 'production' :
				return strcasecmp($this->options['env'], 'production') === 0;
				break;

			case 'dev' :
			case 'development' :
				return strcasecmp($this->options['env'], 'development') === 0;
				break;
		}

		return false;
	}

	/**
	 * Определение пути
	 *
	 * @access  public
	 * @return  mixed
	 */
	public function path()
	{
		$parts = func_get_args();

		$alias = array_shift($parts) ?: '.';

		if (isset($this->options['paths'][$alias]))
		{
			$pathname = $this->options['paths'][$alias]();

			$pathname .= implode(DIRECTORY_SEPARATOR, $parts);

			return $pathname;
		}
	}

	/**
	 * Регистрация службы общего назначения
	 *
	 * @param   string   $alias
	 * @param   call     $callable
	 *
	 * @access  public
	 * @return  void
	 */
	public function registerSharedService($alias, callable $callable)
	{
		$this->services['shared'][$alias]['service'] = $callable;
	}

	/**
	 * Регистрация слушателя службы общего назначения
	 *
	 * @param   string   $alias
	 * @param   call     $callable
	 *
	 * @access  public
	 * @return  void
	 */
	public function registerListenerOfSharedService($alias, callable $callable)
	{
		$this->services['shared'][$alias]['listeners'][] = $callable;
	}

	/**
	 * Разрегистрация службы общего назначения
	 *
	 * @param   string   $alias
	 *
	 * @access  public
	 * @return  void
	 */
	public function unregisterSharedService($alias)
	{
		unset($this->services['shared'][$alias]['service']);
	}

	/**
	 * Разрегистрация слушателей службы общего назначения
	 *
	 * @param   string   $alias
	 *
	 * @access  public
	 * @return  void
	 */
	public function unregisterListenersOfSharedService($alias)
	{
		unset($this->services['shared'][$alias]['listeners']);
	}

	/**
	 * Является ли служба общего назначения зарегистрированной
	 *
	 * @param   string   $alias
	 *
	 * @access  public
	 * @return  bool
	 */
	public function doesExistsSharedService($alias)
	{
		return isset($this->services['shared'][$alias]['service']);
	}

	/**
	 * Является ли служба общего назначения прослушиваемой
	 *
	 * @param   string   $alias
	 *
	 * @access  public
	 * @return  bool
	 */
	public function doesListeningSharedService($alias)
	{
		return isset($this->services['shared'][$alias]['listeners']);
	}

	/**
	 * Полная разрегистрация службы общего назначения
	 *
	 * @param   string   $alias
	 *
	 * @access  public
	 * @return  bool
	 */
	public function forgetSharedService($alias)
	{
		if (isset($this->services['shared'][$alias]))
		{
			unset($this->services['shared'][$alias]);

			return true;
		}

		return false;
	}

	/**
	 * Вызов службы общего назначения
	 *
	 * @param   string   $alias
	 * @param   mixed    $params
	 *
	 * @access  public
	 * @return  mixed
	 *
	 * @throws  RuntimeException
	 */
	public function callSharedService($alias, $params = null)
	{
		$params = (array) $params;

		if (isset($this->services['shared'][$alias]['service']))
		{
			if (isset($this->services['shared'][$alias]['listeners']))
			{
				foreach ($this->services['shared'][$alias]['listeners'] as $listener)
				{
					call_user_func_array($listener, $params);
				}
			}

			return call_user_func_array($this->services['shared'][$alias]['service'], $params);
		}

		throw new RuntimeException(sprintf('Shared service «%s» is not registered.', $alias));
	}

	/**
	 * Регистрация одноразовой службы общего назначения которая при запуске запустится только единожды
	 *
	 * @param   string   $alias
	 * @param   call     $callable
	 *
	 * @access  public
	 * @return  void
	 */
	public function registerDisposableSharedService($alias, callable $callable)
	{
		$this->registerSharedService($alias, function() use($callable)
		{
			static $output;

			if (empty($output))
			{
				$output = call_user_func_array($callable, func_get_args());
			}

			return $output;
		});
	}

	/**
	 * Регистрация распознаваемой службы общего назначения которая при запуске запустится только единожды
	 *
	 * @param   string   $alias
	 * @param   call     $callable
	 *
	 * @access  public
	 * @return  void
	 */
	public function registerResolvableSharedService($alias, callable $callable)
	{
		$this->registerSharedService($alias, function($resolver = null) use($callable)
		{
			static $output = [];

			if (empty($output[$resolver]))
			{
				$output[$resolver] = call_user_func_array($callable, func_get_args());
			}

			return $output[$resolver];
		});
	}

	/**
	 * Автозагрузчик классов (PSR-4)
	 *
	 * @param   string   $class
	 *
	 * @access  public
	 * @return  bool
	 */
	public function autoload($class)
	{
		if (strncmp('Fenric\\', $class, 7) === 0)
		{
			$logicalPath = strtr(substr($class, 7), '\\', '/');

			foreach ($this->options['autoload.rules'] as $maskedPath)
			{
				$search = [':app/', ':system/', ':class', '/'];

				$replace = [$this->path('app'), $this->path('system'), $logicalPath, DIRECTORY_SEPARATOR];

				$suspected = str_replace($search, $replace, $maskedPath);

				if (file_exists($suspected))
				{
					require_once $suspected;

					return true;
				}
			}
		}

		return false;
	}

	/**
	 * Обработчик ошибок
	 *
	 * @param   int      $type
	 * @param   string   $message
	 * @param   string   $file
	 * @param   int      $line
	 *
	 * @access  public
	 * @return  void
	 */
	public function handleError($type, $message, $file, $line)
	{
		if ($type & error_reporting())
		{
			$this->callSharedService('logger')
				->php($type, sprintf($this->options['handling.error.log.format'], $message, $file, $line));

			$this->callSharedService('event', 'fenric.system.error')
				->notifySubscribers(func_get_args());
		}
	}

	/**
	 * Обработчик неперехваченных исключений
	 *
	 * @param   object   $exception
	 *
	 * @access  public
	 * @return  void
	 */
	public function handleUncaughtException($exception)
	{
		$this->callSharedService('logger')
			->error(sprintf($this->options['handling.uncaught.exception.log.format'], $exception->getMessage(), $exception->getFile(), $exception->getLine()));

		$this->callSharedService('event', 'fenric.system.uncaught.exception')
			->notifySubscribers([$exception]);

		$this->callSharedService('event', 'fenric.system.emergency')
			->notifySubscribers([$exception->getMessage(), $exception->getFile(), $exception->getLine()]);
	}

	/**
	 * Обработчик фатальных ошибок
	 *
	 * @access  public
	 * @return  void
	 */
	public function handleFatalError()
	{
		if ($error = error_get_last())
		{
			if ($error['type'] & $this->options['handling.fatal.error.mode'])
			{
				$this->callSharedService('logger')
					->php($error['type'], sprintf($this->options['handling.fatal.error.log.format'], $error['message'], $error['file'], $error['line']));

				$this->callSharedService('event', 'fenric.system.fatal.error')
					->notifySubscribers([$error]);

				$this->callSharedService('event', 'fenric.system.emergency')
					->notifySubscribers([$error['message'], $error['file'], $error['line']]);
			}
		}
	}
}

/**
 * Основная функция фреймворка
 *
 * @param   mixed    $alias
 * @param   mixed    $params
 *
 * @access  public
 * @return  mixed
 */
function fenric($alias = null, $params = null)
{
	static $instance;

	if (is_null($instance))
	{
		$instance = new Fenric();
	}

	if (is_string($alias))
	{
		if (strpos($alias, '::') !== false)
		{
			list($alias, $resolver) = explode('::', $alias, 2);

			return $instance->callSharedService($alias, [$resolver, $params]);
		}

		return $instance->callSharedService($alias, $params);
	}

	return $instance;
}
