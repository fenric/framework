<?php
/**
 * It's free open-source software released under the MIT License.
 *
 * @author Anatoly Fenric <a.fenric@gmail.com>
 * @copyright Copyright (c) 2013-2017 by Fenric Laboratory
 * @license https://github.com/fenric/framework/blob/master/LICENSE.md
 * @link https://github.com/fenric/framework
 */

/**
 * Import classes
 */
use Fenric\{Collection, Console, Event, Logger, Query, Request, Response, Router, Session, View};

/**
 * Main class of framework
 */
final class Fenric
{

	/**
	 * Версия фреймворка
	 */
	const VERSION = '2.0.0-dev';

	/**
	 * Зарегистрированные пути фреймворка
	 */
	private $paths = [];

	/**
	 * Зарегистрированные службы фреймворка
	 */
	private $services = [];

	/**
	 * Зарегистрированные загрузчики классов фреймворка
	 */
	private $classLoaders = [];

	/**
	 * Зарегистрированные обработчики неперехваченных исключений
	 */
	private $uncaughtExceptionHandlers = [];

	/**
	 * Идентификатор приложения по умолчанию
	 */
	private $applicationId = 'default';

	/**
	 * Инициализация фреймворка
	 */
	public function init() : void
	{
		$appId = getenv('APP_ID') ?: $this->applicationId;

		$this->setApplicationId($appId);
		$this->registerBasePaths();
		$this->registerBaseServices();
		$this->registerBaseClassLoaders();
	}

	/**
	 * Расширенная инициализация фреймворка
	 */
	public function advancedInit() : void
	{
		$this->init();
		$this->autoload();
		$this->handleErrors();
		$this->handleUncaughtExceptions();
	}

	/**
	 * Регистрация базовых путей фреймворка
	 */
	public function registerBasePaths() : void
	{
		$this->registerPath('.', function() : string
		{
			return realpath(__DIR__ . '/..');
		});

		$this->registerPath('app', function() : string
		{
			return $this->path('.', 'app');
		});

		$this->registerPath('public', function() : string
		{
			return $this->path('.', 'public', $this->getApplicationId());
		});

		$this->registerPath('bin', function() : string
		{
			return $this->path('app', 'bin', $this->getApplicationId());
		});

		$this->registerPath('cache', function() : string
		{
			return $this->path('app', 'cache', $this->getApplicationId());
		});

		$this->registerPath('configs', function() : string
		{
			return $this->path('app', 'configs', $this->getApplicationId());
		});

		$this->registerPath('locales', function() : string
		{
			return $this->path('app', 'locales', $this->getApplicationId());
		});

		$this->registerPath('log', function() : string
		{
			return $this->path('app', 'log', $this->getApplicationId());
		});

		$this->registerPath('res', function() : string
		{
			return $this->path('app', 'res', $this->getApplicationId());
		});

		$this->registerPath('views', function() : string
		{
			return $this->path('app', 'views', $this->getApplicationId());
		});

		$this->registerPath('assets', function() : string
		{
			return $this->path('public', 'assets');
		});

		$this->registerPath('upload', function() : string
		{
			return $this->path('public', 'upload');
		});

		$this->registerPath('core', function() : string
		{
			return __DIR__;
		});
	}

	/**
	 * Регистрация базовых служб фреймворка
	 */
	public function registerBaseServices() : void
	{
		/**
		 * Регистрация в контейнере фреймворка именованной службы для работы с конфигурационными файлами
		 */
		$this->registerResolvableSharedService('config', function(string $resolver = 'default') : Collection
		{
			if (file_exists($this->path('configs', "{$resolver}.local.php")))
			{
				return new Collection(include $this->path('configs', "{$resolver}.local.php"));
			}

			if (file_exists($this->path('configs', 'test', "{$resolver}.php")) && $this->is('test'))
			{
				return new Collection(include $this->path('configs', 'test', "{$resolver}.php"));
			}

			if (file_exists($this->path('configs', 'production', "{$resolver}.php")) && $this->is('production'))
			{
				return new Collection(include $this->path('configs', 'production', "{$resolver}.php"));
			}

			if (file_exists($this->path('configs', 'development', "{$resolver}.php")) && $this->is('development'))
			{
				return new Collection(include $this->path('configs', 'development', "{$resolver}.php"));
			}

			if (file_exists($this->path('configs', "{$resolver}.php")))
			{
				return new Collection(include $this->path('configs', "{$resolver}.php"));
			}

			throw new RuntimeException(sprintf('Unable to find config [%s].', $resolver));
		});

		/**
		 * Регистрация в контейнере фреймворка именованной службы для работы с локализационными файлами
		 */
		$this->registerResolvableSharedService('locale', function(string $resolver = 'default') : Collection
		{
			if (file_exists($this->path('locales', $this->getApplicationLanguage(), "{$resolver}.local.php")))
			{
				return new Collection(include $this->path('locales', $this->getApplicationLanguage(), "{$resolver}.local.php"));
			}

			if (file_exists($this->path('locales', $this->getApplicationFallbackLanguage(), "{$resolver}.local.php")))
			{
				return new Collection(include $this->path('locales', $this->getApplicationFallbackLanguage(), "{$resolver}.local.php"));
			}

			if (file_exists($this->path('locales', $this->getApplicationLanguage(), "{$resolver}.php")))
			{
				return new Collection(include $this->path('locales', $this->getApplicationLanguage(), "{$resolver}.php"));
			}

			if (file_exists($this->path('locales', $this->getApplicationFallbackLanguage(), "{$resolver}.php")))
			{
				return new Collection(include $this->path('locales', $this->getApplicationFallbackLanguage(), "{$resolver}.php"));
			}

			throw new RuntimeException(sprintf('Unable to find locale [%s].', $resolver));
		});

		/**
		 * Регистрация в контейнере фреймворка именованной службы для работы с событиями
		 */
		$this->registerResolvableSharedService('event', function(string $resolver = 'default') : Event
		{
			return new Event($resolver);
		});

		/**
		 * Регистрация в контейнере фреймворка именованной службы для работы с журналами
		 */
		$this->registerResolvableSharedService('logger', function(string $resolver = 'default') : Logger
		{
			return new Logger($resolver);
		});

		/**
		 * Регистрация в контейнере фреймворка одиночной службы для обработки HTTP запроса
		 */
		$this->registerDisposableSharedService('request', function() : Request
		{
			return new Request();
		});

		/**
		 * Регистрация в контейнере фреймворка одиночной службы для генерации HTTP ответа
		 */
		$this->registerDisposableSharedService('response', function() : Response
		{
			return new Response();
		});

		/**
		 * Регистрация в контейнере фреймворка одиночной службы для работы с маршрутизатором
		 */
		$this->registerDisposableSharedService('router', function() : Router
		{
			return new Router();
		});

		/**
		 * Регистрация в контейнере фреймворка одиночной службы для работы с сессией
		 */
		$this->registerDisposableSharedService('session', function() : Session
		{
			return new Session();
		});

		/**
		 * Регистрация в контейнере фреймворка простой службы для работы с представлениями
		 */
		$this->registerSharedService('view', function(string $resolver, array $variables = null) : View
		{
			return new View($resolver, $variables ?: []);
		});

		/**
		 * Регистрация в контейнере фреймворка простой службы для работы с конструктором SQL запросов
		 */
		$this->registerSharedService('query', function(string $connection = 'default') : Query
		{
			static $connections;

			if (empty($connections[$connection]))
			{
				if ($this->callSharedService('config', ['database'])->exists($connection))
				{
					$options = $this->callSharedService('config', ['database'])->get($connection);

					if (isset($options['dsn'], $options['user'], $options['password']))
					{
						$options['parameters'][PDO::ATTR_ERRMODE] = PDO::ERRMODE_EXCEPTION;

						$connections[$connection] = new PDO($options['dsn'], $options['user'], $options['password'], $options['parameters']);
					}
					else throw new RuntimeException(sprintf('Connection [%s] configured incorrectly.', $connection));
				}
				else throw new RuntimeException(sprintf('Connection [%s] is not configured.', $connection));
			}

			return new Query($connections[$connection]);
		});

		/**
		 * Регистрация в контейнере фреймворка одиночной службы для работы с консолью
		 */
		$this->is('cli') and $this->registerDisposableSharedService('console', function() : Console
		{
			$request = $this->callSharedService('request');

			$arguments = $request->environment->get('argv', []);

			return new Console($arguments);
		});
	}

	/**
	 * Регистрация базовых загрузчиков классов фреймворка
	 */
	public function registerBaseClassLoaders() : void
	{
		$this->registerClassLoader(function(string $filename) : bool
		{
			if (file_exists($this->path('app', 'classes', $this->getApplicationId(), "{$filename}.php")))
			{
				require_once $this->path('app', 'classes', $this->getApplicationId(), "{$filename}.php");

				return true;
			}

			return false;
		});

		$this->registerClassLoader(function(string $filename) : bool
		{
			if (file_exists($this->path('app', 'classes.share', "{$filename}.php")))
			{
				require_once $this->path('app', 'classes.share', "{$filename}.php");

				return true;
			}

			return false;
		});

		$this->registerClassLoader(function(string $filename) : bool
		{
			if (file_exists($this->path('core', 'classes', "{$filename}.php")))
			{
				require_once $this->path('core', 'classes', "{$filename}.php");

				return true;
			}

			return false;
		});
	}

	/**
	 * Сборка пути фреймворка
	 */
	public function path(string ...$parts) : string
	{
		$ds = DIRECTORY_SEPARATOR;

		$alias = array_shift($parts) ?: '.';

		if (isset($this->paths[$alias]))
		{
			$built = $this->paths[$alias]();

			if (count($parts) > 0)
			{
				$built .= $ds . implode($ds, $parts);
			}

			return $built;
		}

		throw new RuntimeException(sprintf('Alias path [%s] is not registered.', $alias));
	}

	/**
	 * Регистрация пути фреймворка
	 */
	public function registerPath(string $alias, callable $builder) : void
	{
		$this->paths[$alias] = function() use($builder) : string
		{
			return call_user_func($builder);
		};
	}

	/**
	 * Регистрация службы фреймворка
	 */
	public function registerSharedService(string $alias, callable $service) : void
	{
		$this->services['shared'][$alias] = function() use($service)
		{
			return call_user_func_array($service, func_get_args());
		};
	}

	/**
	 * Регистрация одиночной службы фреймворка
	 */
	public function registerDisposableSharedService(string $alias, callable $service) : void
	{
		$this->registerSharedService($alias, function() use($alias, $service)
		{
			if (empty($this->services['output.shared.disposable'][$alias]))
			{
				$this->services['output.shared.disposable'][$alias] = call_user_func_array($service, func_get_args());
			}

			return $this->services['output.shared.disposable'][$alias];
		});
	}

	/**
	 * Регистрация именованной службы фреймворка
	 */
	public function registerResolvableSharedService(string $alias, callable $service) : void
	{
		$this->registerSharedService($alias, function(string $resolver = null) use($alias, $service)
		{
			if (empty($this->services['output.shared.resolvable'][$alias][$resolver]))
			{
				$this->services['output.shared.resolvable'][$alias][$resolver] = call_user_func_array($service, func_get_args());
			}

			return $this->services['output.shared.resolvable'][$alias][$resolver];
		});
	}

	/**
	 * Разрегистрация службы фреймворка
	 */
	public function unregisterSharedService(string $alias) : bool
	{
		if (isset($this->services['shared'][$alias]))
		{
			unset($this->services['shared'][$alias]);

			unset($this->services['output.shared.disposable'][$alias]);
			unset($this->services['output.shared.resolvable'][$alias]);

			return true;
		}

		return false;
	}

	/**
	 * Проверка существования службы фреймворка
	 */
	public function existsSharedService(string $alias) : bool
	{
		if (isset($this->services['shared'][$alias]))
		{
			return true;
		}

		return false;
	}

	/**
	 * Вызов службы фреймворка
	 */
	public function callSharedService(string $alias, array $params = [])
	{
		if (isset($this->services['shared'][$alias]))
		{
			return $this->services['shared'][$alias](...$params);
		}

		throw new RuntimeException(sprintf('Shared service [%s] is not registered.', $alias));
	}

	/**
	 * Регистрация загрузчика классов фреймворка
	 */
	public function registerClassLoader(callable $loader) : void
	{
		$this->classLoaders[] = function() use($loader) : bool
		{
			return call_user_func_array($loader, func_get_args());
		};
	}

	/**
	 * Регистрация обработчика неперехваченных исключений
	 */
	public function registerUncaughtExceptionHandler(callable $handler) : void
	{
		$this->uncaughtExceptionHandlers[] = function() use($handler) : void
		{
			call_user_func_array($handler, func_get_args());
		};
	}

	/**
	 * Регистрация приоритетного загрузчика классов фреймворка
	 */
	public function registerPrimaryClassLoader(callable $loader) : void
	{
		array_unshift($this->classLoaders, function() use($loader) : bool
		{
			return call_user_func_array($loader, func_get_args());
		});
	}

	/**
	 * Регистрация приоритетного обработчика неперехваченных исключений
	 */
	public function registerPrimaryUncaughtExceptionHandler(callable $handler) : void
	{
		array_unshift($this->uncaughtExceptionHandlers, function() use($handler) : void
		{
			call_user_func_array($handler, func_get_args());
		});
	}

	/**
	 * Автозагрузка классов фреймворка
	 */
	public function autoload() : void
	{
		spl_autoload_register(function($class)
		{
			if (0 === strncmp('Fenric\\', $class, 7))
			{
				$filename = strtr(substr($class, 7), '\\', '/');

				if (count($this->classLoaders) > 0)
				{
					foreach ($this->classLoaders as $loader)
					{
						if ($loader($filename))
						{
							return true;
						}
					}
				}

				return false;
			}

		}, true, true);
	}

	/**
	 * Обработка ошибок
	 */
	public function handleErrors() : void
	{
		set_error_handler(function($severity, $message, $file, $line)
		{
			throw new ErrorException($message, 0, $severity, $file, $line);
		});
	}

	/**
	 * Обработка неперехваченных исключений
	 */
	public function handleUncaughtExceptions() : void
	{
		set_exception_handler(function($e)
		{
			$format = 'Uncaught exception %s: %s in file %s on line %d.';

			$this->callSharedService('logger', ['errors'])->error(
				sprintf($format, get_class($e), $e->getMessage(), $e->getFile(), $e->getLine()) . PHP_EOL . $e->getTraceAsString()
			);

			if (count($this->uncaughtExceptionHandlers) > 0)
			{
				foreach ($this->uncaughtExceptionHandlers as $handler)
				{
					$handler($e);
				}
			}
		});
	}

	/**
	 * Установка идентификатора приложения
	 */
	public function setApplicationId(string $applicationId) : void
	{
		$this->applicationId = $applicationId;
	}

	/**
	 * Получение идентификатора приложения
	 */
	public function getApplicationId() : string
	{
		return $this->applicationId;
	}

	/**
	 * Установка языка приложения
	 */
	public function setApplicationLanguage(string $language) : void
	{
		$this->callSharedService('config', ['app'])->set('language', $language);
	}

	/**
	 * Получение языка приложения
	 */
	public function getApplicationLanguage(string $default = 'ru_RU') : string
	{
		return $this->callSharedService('config', ['app'])->get('language', $default);
	}

	/**
	 * Установка запасного языка приложения
	 */
	public function setApplicationFallbackLanguage(string $language) : void
	{
		$this->callSharedService('config', ['app'])->set('language.fallback', $language);
	}

	/**
	 * Получение запасного языка приложения
	 */
	public function getApplicationFallbackLanguage(string $default = 'en_US') : string
	{
		return $this->callSharedService('config', ['app'])->get('language.fallback', $default);
	}

	/**
	 * Локализация сообщения
	 */
	public function t(string $section, string $message, array $context = []) : string
	{
		if ($this->callSharedService('locale', [$section])->exists($message))
		{
			$message = $this->callSharedService('locale', [$section])->get($message);
		}

		return $this->interpolate($message, $context);
	}

	/**
	 * Интерполяция сообщения
	 */
	public function interpolate(string $message, array $context = []) : string
	{
		$substitutable = [];

		foreach ($context as $key => $value)
		{
			$substitutable['{' . $key . '}'] = $value;
		}

		return strtr($message, $substitutable);
	}

	/**
	 * Определение окружения
	 */
	public function is() : bool
	{
		switch (func_get_arg(0))
		{
			case 'cli' :
				return 0 === strcasecmp(PHP_SAPI, 'cli');
				break;

			case 'test' :
				return 0 === strcasecmp(getenv('ENVIRONMENT'), 'test');
				break;

			case 'production' :
				return 0 === strcasecmp(getenv('ENVIRONMENT'), 'production');
				break;

			case 'development' :
				return 0 === strcasecmp(getenv('ENVIRONMENT'), 'development');
				break;

			case 'linux' :
				return 0 === strcasecmp(PHP_OS, 'linux');
				break;

			case 'macintosh' :
				return 0 === strcasecmp(PHP_OS, 'darwin');
				break;

			case 'windows' :
				return 0 === strncasecmp(PHP_OS, 'win', 3);
				break;

			case $this->getApplicationId() :
				return true;
				break;
		}

		return false;
	}
}

/**
 * Основная функция фреймворка
 */
function fenric($alias = null, $params = null)
{
	static $self;

	if (is_null($self))
	{
		$self = new Fenric();
	}

	if (is_string($alias))
	{
		$params = (array) $params;

		if (strpos($alias, '::') !== false)
		{
			list($alias, $resolver) = explode('::', $alias, 2);

			return $self->callSharedService($alias, [$resolver, $params]);
		}

		return $self->callSharedService($alias, $params);
	}

	return $self;
}

/**
 * Настройка окружения фреймворка
 */
switch (getenv('ENVIRONMENT'))
{
	case 'test' :
		error_reporting(E_ALL);
		ini_set('display_errors', 'On');
		break;

	case 'production' :
		error_reporting(E_ALL);
		ini_set('display_errors', 'Off');
		break;

	case 'development' :
		error_reporting(E_ALL);
		ini_set('display_errors', 'On');
		break;

	default :
		error_reporting(E_ALL);
		ini_set('display_errors', 'On');
		putenv('ENVIRONMENT=development');
		break;
}
