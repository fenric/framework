<?php
/**
 * It's free open-source software released under the MIT License.
 *
 * @author Anatoly Fenric <anatoly.fenric@gmail.com>
 * @copyright Copyright (c) 2013-2018 by Fenric Laboratory
 * @license https://github.com/fenric/framework/blob/master/LICENSE.md
 * @link https://github.com/fenric/framework
 */

/**
 * Import classes
 */
use Fenric\{
	Collection,
	Console,
	Event,
	Logger,
	Query,
	Request,
	Response,
	Router,
	View
};

/**
 * Main class of the framework
 */
final class Fenric
{

	/**
	 * Версия фреймворка
	 */
	public const VERSION = '2.0.6';

	/**
	 * Зарегистрированные пути
	 */
	private $paths = [];

	/**
	 * Зарегистрированные службы
	 */
	private $services = [];

	/**
	 * Зарегистрированные загрузчики классов
	 */
	private $classLoaders = [];

	/**
	 * Зарегистрированные обработчики неперехваченных исключений
	 */
	private $uncaughtExceptionHandlers = [];

	/**
	 * Конструктор класса
	 */
	public function __construct()
	{
		getenv('ENVIRONMENT') or
		putenv('ENVIRONMENT=development');
	}

	/**
	 * Инициализация фреймворка
	 */
	public function init() : void
	{
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
	 * Регистрация базовых путей
	 */
	public function registerBasePaths() : void
	{
		$this->registerPath('.', function() : string
		{
			return realpath(__DIR__ . '/..');
		});

		$this->registerPath('app', function() : string
		{
			return $this->path('.', 'app')->getRealPath();
		});

		$this->registerPath('public', function() : string
		{
			return $this->path('.', 'public')->getRealPath();
		});

		$this->registerPath('vendor', function() : string
		{
			return $this->path('.', 'vendor')->getRealPath();
		});

		$this->registerPath('bin', function() : string
		{
			return $this->path('app', 'bin')->getRealPath();
		});

		$this->registerPath('cache', function() : string
		{
			return $this->path('app', 'cache')->getRealPath();
		});

		$this->registerPath('configs', function() : string
		{
			return $this->path('app', 'configs')->getRealPath();
		});

		$this->registerPath('locales', function() : string
		{
			return $this->path('app', 'locales')->getRealPath();
		});

		$this->registerPath('log', function() : string
		{
			return $this->path('app', 'log')->getRealPath();
		});

		$this->registerPath('resources', function() : string
		{
			return $this->path('app', 'resources')->getRealPath();
		});

		$this->registerPath('routes', function() : string
		{
			return $this->path('app', 'routes')->getRealPath();
		});

		$this->registerPath('views', function() : string
		{
			return $this->path('app', 'views')->getRealPath();
		});

		$this->registerPath('assets', function() : string
		{
			return $this->path('public', 'assets')->getRealPath();
		});

		$this->registerPath('upload', function() : string
		{
			return $this->path('public', 'upload')->getRealPath();
		});

		$this->registerPath('core', function() : string
		{
			return __DIR__;
		});
	}

	/**
	 * Регистрация базовых служб
	 */
	public function registerBaseServices() : void
	{
		/**
		 * Регистрация именованной службы для работы с конфигурационными файлами
		 */
		$this->registerResolvableSharedService('config', function(string $resolver = 'default') : Collection
		{
			if ($this->path('configs', "{$resolver}.local.php")->isFile())
			{
				return new Collection(include $this->path('configs', "{$resolver}.local.php")->getRealPath());
			}

			if ($this->path('configs', getenv('ENVIRONMENT'), "{$resolver}.php")->isFile())
			{
				return new Collection(include $this->path('configs', getenv('ENVIRONMENT'), "{$resolver}.php")->getRealPath());
			}

			if ($this->path('configs', "{$resolver}.php")->isFile())
			{
				return new Collection(include $this->path('configs', "{$resolver}.php")->getRealPath());
			}

			if ($this->path('configs', "{$resolver}.example.php")->isFile())
			{
				return new Collection(include $this->path('configs', "{$resolver}.example.php")->getRealPath());
			}

			throw new RuntimeException(sprintf('Unable to find config [%s].', $resolver));
		});

		/**
		 * Регистрация именованной службы для работы с локализационными файлами
		 */
		$this->registerResolvableSharedService('locale', function(string $resolver = 'default') : Collection
		{
			if ($this->path('locales', $this->getApplicationLanguage(), "{$resolver}.local.php")->isFile())
			{
				return new Collection(include $this->path('locales', $this->getApplicationLanguage(), "{$resolver}.local.php")->getRealPath());
			}

			if ($this->path('locales', $this->getApplicationDefaultLanguage(), "{$resolver}.local.php")->isFile())
			{
				return new Collection(include $this->path('locales', $this->getApplicationDefaultLanguage(), "{$resolver}.local.php")->getRealPath());
			}

			if ($this->path('locales', $this->getApplicationLanguage(), "{$resolver}.php")->isFile())
			{
				return new Collection(include $this->path('locales', $this->getApplicationLanguage(), "{$resolver}.php")->getRealPath());
			}

			if ($this->path('locales', $this->getApplicationDefaultLanguage(), "{$resolver}.php")->isFile())
			{
				return new Collection(include $this->path('locales', $this->getApplicationDefaultLanguage(), "{$resolver}.php")->getRealPath());
			}

			throw new RuntimeException(sprintf('Unable to find locale [%s].', $resolver));
		});

		/**
		 * Регистрация именованной службы для работы с событиями
		 */
		$this->registerResolvableSharedService('event', function(string $resolver = 'default') : Event
		{
			return new Event($resolver);
		});

		/**
		 * Регистрация именованной службы для работы с журналами
		 */
		$this->registerResolvableSharedService('logger', function(string $resolver = 'default') : Logger
		{
			return new Logger($resolver);
		});

		/**
		 * Регистрация одиночной службы для обработки HTTP запроса
		 */
		$this->registerDisposableSharedService('request', function() : Request
		{
			return new Request();
		});

		/**
		 * Регистрация одиночной службы для генерации HTTP ответа
		 */
		$this->registerDisposableSharedService('response', function() : Response
		{
			return new Response();
		});

		/**
		 * Регистрация одиночной службы для работы с маршрутизатором
		 */
		$this->registerDisposableSharedService('router', function() : Router
		{
			return new Router();
		});

		/**
		 * Регистрация простой службы для работы с представлениями
		 */
		$this->registerSharedService('view', function(string $resolver, array $variables = null) : View
		{
			return new View($resolver, $variables ?: []);
		});

		/**
		 * Регистрация простой службы для работы с конструктором SQL запросов
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
		 * Регистрация одиночной службы для работы с консолью
		 */
		$this->is('cli') and $this->registerDisposableSharedService('console', function() : Console
		{
			$request = $this->callSharedService('request');

			$arguments = $request->environment->get('argv', []);

			return new Console($arguments);
		});
	}

	/**
	 * Регистрация базовых загрузчиков классов
	 */
	public function registerBaseClassLoaders() : void
	{
		$this->registerClassLoader(function(string $filename) : bool
		{
			if ($this->path('app', 'classes', "{$filename}.local.php")->isFile())
			{
				require_once $this->path('app', 'classes', "{$filename}.local.php")->getRealPath();

				return true;
			}

			if ($this->path('app', 'classes', "{$filename}.php")->isFile())
			{
				require_once $this->path('app', 'classes', "{$filename}.php")->getRealPath();

				return true;
			}

			if ($this->path('app', 'classes', "{$filename}.example.php")->isFile())
			{
				require_once $this->path('app', 'classes', "{$filename}.example.php")->getRealPath();

				return true;
			}

			if ($this->path('core', 'classes', "{$filename}.php")->isFile())
			{
				require_once $this->path('core', 'classes', "{$filename}.php")->getRealPath();

				return true;
			}

			return false;
		});
	}

	/**
	 * Сборка пути
	 */
	public function path(string ...$parts) : SplFileInfo
	{
		$ds = DIRECTORY_SEPARATOR;

		$alias = array_shift($parts) ?: '.';

		if (isset($this->paths[$alias]))
		{
			$built = $this->paths[$alias]();

			if (count($parts) > 0)
			{
				$built .= $ds . join($ds, $parts);
			}

			return new SplFileInfo($built);
		}

		throw new RuntimeException(sprintf('Alias path [%s] is not registered.', $alias));
	}

	/**
	 * Регистрация пути
	 */
	public function registerPath(string $alias, callable $builder) : void
	{
		$this->paths[$alias] = function() use($builder) : string
		{
			return call_user_func($builder);
		};
	}

	/**
	 * Регистрация службы
	 */
	public function registerSharedService(string $alias, callable $service) : void
	{
		$this->services['shared'][$alias] = function() use($service)
		{
			return call_user_func_array($service, func_get_args());
		};
	}

	/**
	 * Регистрация одиночной службы
	 */
	public function registerDisposableSharedService(string $alias, callable $service) : void
	{
		$this->registerSharedService($alias, function() use($alias, $service)
		{
			static $storage = [];

			if (empty($storage[$alias]))
			{
				$storage[$alias] = call_user_func_array($service, func_get_args());
			}

			return $storage[$alias];
		});
	}

	/**
	 * Регистрация именованной службы
	 */
	public function registerResolvableSharedService(string $alias, callable $service) : void
	{
		$this->registerSharedService($alias, function(string $resolver = null) use($alias, $service)
		{
			static $storage = [];

			if (empty($storage[$alias][$resolver]))
			{
				$storage[$alias][$resolver] = call_user_func_array($service, func_get_args());
			}

			return $storage[$alias][$resolver];
		});
	}

	/**
	 * Разрегистрация службы
	 */
	public function unregisterSharedService(string $alias) : bool
	{
		if (isset($this->services['shared'][$alias]))
		{
			unset($this->services['shared'][$alias]);

			return true;
		}

		return false;
	}

	/**
	 * Существование службы
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
	 * Вызов службы
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
	 * Регистрация загрузчика классов
	 */
	public function registerClassLoader(callable $loader) : void
	{
		$this->classLoaders[] = function() use($loader) : bool
		{
			return call_user_func_array($loader, func_get_args());
		};
	}

	/**
	 * Регистрация приоритетного загрузчика классов
	 */
	public function registerPrimaryClassLoader(callable $loader) : void
	{
		array_unshift($this->classLoaders, function() use($loader) : bool
		{
			return call_user_func_array($loader, func_get_args());
		});
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
	 * Автозагрузка классов
	 */
	public function autoload() : void
	{
		spl_autoload_register(function($class)
		{
			$ds = DIRECTORY_SEPARATOR;

			if (strncmp('Fenric\\', $class, 7) === 0)
			{
				$filename = strtr(substr($class, 7), '\\', $ds);

				if (count($this->classLoaders))
				{
					foreach ($this->classLoaders as $loader)
					{
						if ($loader($filename, $class))
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
		set_error_handler(function($severity, $message, $file, $line) : void
		{
			throw new ErrorException($message, 0, $severity, $file, $line);
		});
	}

	/**
	 * Обработка неперехваченных исключений
	 */
	public function handleUncaughtExceptions() : void
	{
		set_exception_handler(function(Throwable $e) : void
		{
			$format = '%s: %s in file %s on line %d.';

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
	 * Установка языка приложения
	 */
	public function setApplicationLanguage(string $language) : void
	{
		$this->callSharedService('config', ['app'])->set('language', $language);
	}

	/**
	 * Получение языка приложения
	 */
	public function getApplicationLanguage(string $default = 'ru') : string
	{
		return $this->callSharedService('config', ['app'])->get('language', $default);
	}

	/**
	 * Установка языка приложения по умолчанию
	 */
	public function setApplicationDefaultLanguage(string $language) : void
	{
		$this->callSharedService('config', ['app'])->set('language.default', $language);
	}

	/**
	 * Получение языка приложения по умолчанию
	 */
	public function getApplicationDefaultLanguage(string $default = 'en-us') : string
	{
		return $this->callSharedService('config', ['app'])->get('language.default', $default);
	}

	/**
	 * Локализация сообщения
	 */
	public function translate(string $section, string $message, array $context = []) : string
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
			$substitutable["{{$key}}"] = $value;
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

			case 'local' :
				return 0 === strcasecmp(getenv('ENVIRONMENT'), 'local');
				break;

			case 'dev' :
			case 'development' :
				return 0 === strcasecmp(getenv('ENVIRONMENT'), 'development');
				break;

			case 'test' :
			case 'testing' :
				return 0 === strcasecmp(getenv('ENVIRONMENT'), 'testing');
				break;

			case 'stage' :
			case 'staging' :
				return 0 === strcasecmp(getenv('ENVIRONMENT'), 'staging');
				break;

			case 'prod' :
			case 'production' :
				return 0 === strcasecmp(getenv('ENVIRONMENT'), 'production');
				break;

			case 'unix' :
			case 'linux' :
				return 0 === strcasecmp(PHP_OS, 'linux');
				break;

			case 'mac' :
			case 'darwin' :
				return 0 === strcasecmp(PHP_OS, 'darwin');
				break;

			case 'win' :
			case 'windows' :
				return 0 === strncasecmp(PHP_OS, 'win', 3);
				break;
		}

		return false;
	}
}

/**
 * Main function of the framework
 */
function fenric(string $alias = null, $params = null)
{
	static $self;

	if (empty($self))
	{
		$self = new Fenric();
	}

	if (isset($alias))
	{
		if (strcmp($alias, '/') === 0)
		{
			return $self->path('.');
		}

		if (strpos($alias, '/') === 0)
		{
			$alias = substr($alias, 1);

			$segments = explode('/', $alias);

			return $self->path(...$segments);
		}

		if (strpos($alias, '::') !== false)
		{
			list($alias, $resolver) = explode('::', $alias, 2);

			return $self->callSharedService($alias, [$resolver, $params]);
		}

		return $self->callSharedService($alias, (array) $params);
	}

	return $self;
}
