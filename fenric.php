<?php
/**
 * It is free open-source software released under the MIT License.
 *
 * @author Anatoly Fenric <a.fenric@gmail.com>
 * @copyright Copyright (c) 2013-2016 by Fenric Laboratory
 * @license https://github.com/fenric/framework/blob/master/LICENSE.md
 * @link https://github.com/fenric/framework
 */

/**
 * Import classes
 */
use Fenric\Collection;
use Fenric\Event;
use Fenric\Logger;
use Fenric\Request;
use Fenric\Response;
use Fenric\Router;
use Fenric\Session;
use Fenric\View;

/**
 * Main class of framework
 */
final class Fenric
{

	/**
	 * Версия фреймворка
	 */
	const VERSION = '1.9.0-dev';

	/**
	 * Зарегистрированные пути фреймворка
	 *
	 * @var     array
	 * @access  private
	 */
	private $paths = [];

	/**
	 * Зарегистрированные службы фреймворка
	 *
	 * @var     array
	 * @access  private
	 */
	private $services = [];

	/**
	 * Зарегистрированные загрузчики классов фреймворка
	 *
	 * @var     array
	 * @access  private
	 */
	private $classLoaders = [];

	/**
	 * Зарегистрированные пользовательские обработчики ошибок
	 *
	 * @var     array
	 * @access  private
	 */
	private $errorHandlers = [];

	/**
	 * Зарегистрированные пользовательские обработчики неперехваченных исключений
	 *
	 * @var     array
	 * @access  private
	 */
	private $uncaughtExceptionHandlers = [];

	/**
	 * Зарегистрированные пользовательские обработчики фатальных ошибок
	 *
	 * @var     array
	 * @access  private
	 */
	private $fatalErrorHandlers = [];

	/**
	 * Идентификатор приложения по умолчанию
	 *
	 * @var     string
	 * @access  private
	 */
	private $applicationId = 'default';

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
				return strcasecmp(PHP_SAPI, 'cli') === 0;
				break;

			case 'test' :
				return strcasecmp(getenv('ENVIRONMENT'), 'test') === 0;
				break;

			case 'production' :
				return strcasecmp(getenv('ENVIRONMENT'), 'production') === 0;
				break;

			case 'development' :
				return strcasecmp(getenv('ENVIRONMENT'), 'development') === 0;
				break;

			case 'linux' :
				return strcasecmp(PHP_OS, 'linux') === 0;
				break;

			case 'macintosh' :
				return strcasecmp(PHP_OS, 'darwin') === 0;
				break;

			case 'windows' :
				return strncasecmp(PHP_OS, 'win', 3) === 0;
				break;
		}

		return false;
	}

	/**
	 * Инициализация фреймворка
	 *
	 * @access  public
	 * @return  void
	 */
	public function init()
	{
		$this->setApplicationId(getenv('APP_ID') ?: 'default');

		$this->registerBasePaths();

		$this->registerBaseServices();

		$this->registerBaseClassLoaders();
	}

	/**
	 * Расширенная инициализация фреймворка
	 *
	 * @access  public
	 * @return  void
	 */
	public function advancedInit()
	{
		$this->init();

		$this->autoload();

		$this->handleErrors();

		$this->handleUncaughtExceptions();

		$this->handleFatalErrors();

		$this->loggingErrors();

		$this->loggingUncaughtExceptions();

		$this->loggingFatalErrors();
	}

	/**
	 * Регистрация базовых путей фреймворка
	 *
	 * @access  public
	 * @return  void
	 */
	public function registerBasePaths()
	{
		$this->registerPath('.', function()
		{
			return dirname(__DIR__);
		});

		$this->registerPath('app', function()
		{
			return $this->path('.', 'app');
		});

		$this->registerPath('system', function()
		{
			return $this->path('.', 'system');
		});

		$this->registerPath('public', function()
		{
			return $this->path('.', 'public', $this->getApplicationId());
		});

		$this->registerPath('cache', function()
		{
			return $this->path('app', 'cache', $this->getApplicationId());
		});

		$this->registerPath('configs', function()
		{
			return $this->path('app', 'configs', $this->getApplicationId());
		});

		$this->registerPath('locales', function()
		{
			return $this->path('app', 'locales', $this->getApplicationId());
		});

		$this->registerPath('log', function()
		{
			return $this->path('app', 'log', $this->getApplicationId());
		});

		$this->registerPath('res', function()
		{
			return $this->path('app', 'res', $this->getApplicationId());
		});

		$this->registerPath('views', function()
		{
			return $this->path('app', 'views', $this->getApplicationId());
		});
	}

	/**
	 * Регистрация базовых служб фреймворка
	 *
	 * @access  public
	 * @return  void
	 */
	public function registerBaseServices()
	{
		$this->registerResolvableSharedService('config', function($resolver = 'default')
		{
			if (file_exists($this->path('configs', "$resolver.local.php")))
			{
				return new Collection(include $this->path('configs', "$resolver.local.php"));
			}

			if (file_exists($this->path('configs', 'test', "$resolver.php")) && $this->is('test'))
			{
				return new Collection(include $this->path('configs', 'test', "$resolver.php"));
			}

			if (file_exists($this->path('configs', 'development', "$resolver.php")) && $this->is('development'))
			{
				return new Collection(include $this->path('configs', 'development', "$resolver.php"));
			}

			if (file_exists($this->path('configs', 'production', "$resolver.php")) && $this->is('production'))
			{
				return new Collection(include $this->path('configs', 'production', "$resolver.php"));
			}

			if (file_exists($this->path('configs', "$resolver.php")))
			{
				return new Collection(include $this->path('configs', "$resolver.php"));
			}

			throw new RuntimeException(sprintf('Unable to find config «%s».', $resolver));
		});

		$this->registerResolvableSharedService('locale', function($resolver = 'default')
		{
			if (file_exists($this->path('locales', $this->getApplicationLanguage(), "$resolver.php")))
			{
				return new Collection(include $this->path('locales', $this->getApplicationLanguage(), "$resolver.php"));
			}

			if (file_exists($this->path('locales', $this->getApplicationFallbackLanguage(), "$resolver.php")))
			{
				return new Collection(include $this->path('locales', $this->getApplicationFallbackLanguage(), "$resolver.php"));
			}

			throw new RuntimeException(sprintf('Unable to find locale messages «%s» for languages «%s» and «%s».',
				$resolver, $this->getApplicationLanguage(), $this->getApplicationFallbackLanguage()));
		});

		$this->registerResolvableSharedService('event', function($resolver = 'default')
		{
			return new Event($resolver);
		});

		$this->registerResolvableSharedService('logger', function($resolver = 'default')
		{
			return new Logger($resolver);
		});

		$this->registerDisposableSharedService('request', function()
		{
			return new Request();
		});

		$this->registerDisposableSharedService('response', function()
		{
			return new Response();
		});

		$this->registerDisposableSharedService('router', function()
		{
			return new Router();
		});

		$this->registerDisposableSharedService('session', function()
		{
			return new Session();
		});

		$this->registerSharedService('view', function($name, array $variables = null)
		{
			return new View($name, $variables);
		});
	}

	/**
	 * Регистрация базовых загрузчиков классов фреймворка
	 *
	 * @access  public
	 * @return  void
	 */
	public function registerBaseClassLoaders()
	{
		$this->registerClassLoader(function($filename)
		{
			if (file_exists($this->path('app', 'classes', $this->getApplicationId(), "$filename.php")))
			{
				require_once $this->path('app', 'classes', $this->getApplicationId(), "$filename.php");

				return true;
			}
		});

		$this->registerClassLoader(function($filename)
		{
			if (file_exists($this->path('app', 'classes.share', "$filename.php")))
			{
				require_once $this->path('app', 'classes.share', "$filename.php");

				return true;
			}
		});

		$this->registerClassLoader(function($filename)
		{
			if (file_exists($this->path('system', 'classes', "$filename.php")))
			{
				require_once $this->path('system', 'classes', "$filename.php");

				return true;
			}
		});
	}

	/**
	 * Сборка пути фреймворка
	 *
	 * @access  public
	 * @return  string
	 *
	 * @throws  RuntimeException
	 */
	public function path()
	{
		$parts = func_get_args();

		$alias = array_shift($parts) ?: '.';

		if (isset($this->paths[$alias]))
		{
			$built = $this->paths[$alias]();

			if (count($parts) > 0)
			{
				$built .= DIRECTORY_SEPARATOR;

				$built .= implode(DIRECTORY_SEPARATOR, $parts);
			}

			return $built;
		}

		throw new RuntimeException(sprintf('Alias path «%s» is not registered.', $alias));
	}

	/**
	 * Регистрация пути фреймворка
	 *
	 * @param   string     $alias
	 * @param   callable   $builder
	 *
	 * @access  public
	 * @return  void
	 */
	public function registerPath($alias, callable $builder)
	{
		$this->paths[$alias] = function() use($builder)
		{
			return call_user_func($builder);
		};
	}

	/**
	 * Регистрация службы фреймворка
	 *
	 * @param   string     $alias
	 * @param   callable   $service
	 *
	 * @access  public
	 * @return  void
	 */
	public function registerSharedService($alias, callable $service)
	{
		$this->services['shared'][$alias] = function() use($service)
		{
			return call_user_func_array($service, func_get_args());
		};
	}

	/**
	 * Регистрация одиночной службы фреймворка
	 *
	 * @param   string     $alias
	 * @param   callable   $service
	 *
	 * @access  public
	 * @return  void
	 */
	public function registerDisposableSharedService($alias, callable $service)
	{
		$this->registerSharedService($alias, function() use($alias, $service)
		{
			if (empty($this->services['output.shared.disposable'][$alias]))
			{
				$output = call_user_func_array($service, func_get_args());

				$this->services['output.shared.disposable'][$alias] = $output;
			}

			return $this->services['output.shared.disposable'][$alias];
		});
	}

	/**
	 * Регистрация именованной службы фреймворка
	 *
	 * @param   string     $alias
	 * @param   callable   $service
	 *
	 * @access  public
	 * @return  void
	 */
	public function registerResolvableSharedService($alias, callable $service)
	{
		$this->registerSharedService($alias, function($resolver = null) use($alias, $service)
		{
			if (empty($this->services['output.shared.resolvable'][$alias][$resolver]))
			{
				$output = call_user_func_array($service, func_get_args());

				$this->services['output.shared.resolvable'][$alias][$resolver] = $output;
			}

			return $this->services['output.shared.resolvable'][$alias][$resolver];
		});
	}

	/**
	 * Разрегистрация службы фреймворка
	 *
	 * @param   string   $alias
	 *
	 * @access  public
	 * @return  bool
	 */
	public function unregisterSharedService($alias)
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
	 *
	 * @param   string   $alias
	 *
	 * @access  public
	 * @return  bool
	 */
	public function existsSharedService($alias)
	{
		if (isset($this->services['shared'][$alias]))
		{
			return true;
		}

		return false;
	}

	/**
	 * Вызов службы фреймворка
	 *
	 * @param   string   $alias
	 * @param   array    $params
	 *
	 * @access  public
	 * @return  mixed
	 *
	 * @throws  RuntimeException
	 */
	public function callSharedService($alias, array $params = [])
	{
		if (isset($this->services['shared'][$alias]))
		{
			return call_user_func_array($this->services['shared'][$alias], $params);
		}

		throw new RuntimeException(sprintf('Shared service «%s» is not registered.', $alias));
	}

	/**
	 * Регистрация загрузчика классов фреймворка
	 *
	 * @param   callable   $classLoader
	 *
	 * @access  public
	 * @return  void
	 */
	public function registerClassLoader(callable $classLoader)
	{
		$this->classLoaders[] = function() use($classLoader)
		{
			return call_user_func_array($classLoader, func_get_args());
		};
	}

	/**
	 * Регистрация пользовательского обработчика ошибок
	 *
	 * @param   callable   $handler
	 *
	 * @access  public
	 * @return  void
	 */
	public function registerErrorHandler(callable $handler)
	{
		$this->errorHandlers[] = function() use($handler)
		{
			return call_user_func_array($handler, func_get_args());
		};
	}

	/**
	 * Регистрация пользовательского обработчика неперехваченных исключений
	 *
	 * @param   callable   $handler
	 *
	 * @access  public
	 * @return  void
	 */
	public function registerUncaughtExceptionHandler(callable $handler)
	{
		$this->uncaughtExceptionHandlers[] = function() use($handler)
		{
			return call_user_func_array($handler, func_get_args());
		};
	}

	/**
	 * Регистрация пользовательского обработчика фатальных ошибок
	 *
	 * @param   callable   $handler
	 *
	 * @access  public
	 * @return  void
	 */
	public function registerFatalErrorHandler(callable $handler)
	{
		$this->fatalErrorHandlers[] = function() use($handler)
		{
			return call_user_func_array($handler, func_get_args());
		};
	}

	/**
	 * Автозагрузка классов фреймворка
	 *
	 * @access  public
	 * @return  void
	 */
	public function autoload()
	{
		spl_autoload_register(function($class)
		{
			if (0 === strncmp('Fenric\\', $class, 7))
			{
				$logicalFilename = strtr(substr($class, 7), '\\', '/');

				if (count($this->classLoaders) > 0)
				{
					foreach ($this->classLoaders as $classLoader)
					{
						if (call_user_func($classLoader, $logicalFilename))
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
	 *
	 * @access  public
	 * @return  void
	 */
	public function handleErrors()
	{
		set_error_handler(function($type, $message, $file, $line)
		{
			if (count($this->errorHandlers) > 0)
			{
				foreach ($this->errorHandlers as $handler)
				{
					call_user_func($handler, $type, $message, $file, $line);
				}
			}
		});
	}

	/**
	 * Обработка неперехваченных исключений
	 *
	 * @access  public
	 * @return  void
	 */
	public function handleUncaughtExceptions()
	{
		set_exception_handler(function($exception)
		{
			if (count($this->uncaughtExceptionHandlers) > 0)
			{
				foreach ($this->uncaughtExceptionHandlers as $handler)
				{
					call_user_func($handler, $exception, $exception->getMessage(), $exception->getFile(), $exception->getLine());
				}
			}
		});
	}

	/**
	 * Обработка фатальных ошибок
	 *
	 * @access  public
	 * @return  void
	 */
	public function handleFatalErrors()
	{
		register_shutdown_function(function()
		{
			if ($error = error_get_last())
			{
				if ($error['type'] & E_PARSE | E_ERROR | E_CORE_ERROR | E_COMPILE_ERROR | E_USER_ERROR)
				{
					if (count($this->fatalErrorHandlers) > 0)
					{
						foreach ($this->fatalErrorHandlers as $handler)
						{
							call_user_func($handler, $error['type'], $error['message'], $error['file'], $error['line']);
						}
					}
				}
			}
		});
	}

	/**
	 * Журналирование ошибок
	 *
	 * @param   string   $format
	 *
	 * @access  public
	 * @return  void
	 */
	public function loggingErrors($format = '%s in file %s on line %d.')
	{
		$this->registerErrorHandler(function($type, $message, $file, $line) use($format)
		{
			$this->callSharedService('logger', ['errors'])->php($type, sprintf($format, $message, $file, $line));
		});
	}

	/**
	 * Журналирование неперехваченных исключений
	 *
	 * @param   string   $format
	 *
	 * @access  public
	 * @return  void
	 */
	public function loggingUncaughtExceptions($format = 'Uncaught exception %s: %s in file %s on line %d.')
	{
		$this->registerUncaughtExceptionHandler(function($exception, $message, $file, $line) use($format)
		{
			$this->callSharedService('logger', ['errors'])->error(sprintf($format, get_class($exception), $message, $file, $line));
		});
	}

	/**
	 * Журналирование фатальных ошибок
	 *
	 * @param   string   $format
	 *
	 * @access  public
	 * @return  void
	 */
	public function loggingFatalErrors($format = 'Fatal error: %s in file %s on line %d.')
	{
		$this->registerFatalErrorHandler(function($type, $message, $file, $line) use($format)
		{
			$this->callSharedService('logger', ['errors'])->php($type, sprintf($format, $message, $file, $line));
		});
	}

	/**
	 * Установка идентификатора приложения
	 *
	 * @param   string   $applicationId
	 *
	 * @access  public
	 * @return  void
	 */
	public function setApplicationId($applicationId)
	{
		$this->applicationId = $applicationId;
	}

	/**
	 * Получение идентификатора приложения
	 *
	 * @access  public
	 * @return  string
	 */
	public function getApplicationId()
	{
		return $this->applicationId;
	}

	/**
	 * Установка языка приложения
	 *
	 * @param   string   $language
	 *
	 * @access  public
	 * @return  void
	 */
	public function setApplicationLanguage($language)
	{
		$this->callSharedService('config', ['app'])->set('language', $language);
	}

	/**
	 * Установка запасного языка приложения
	 *
	 * @param   string   $language
	 *
	 * @access  public
	 * @return  void
	 */
	public function setApplicationFallbackLanguage($language)
	{
		$this->callSharedService('config', ['app'])->set('language.fallback', $language);
	}

	/**
	 * Получение языка приложения
	 *
	 * @param   string   $default
	 *
	 * @access  public
	 * @return  string
	 */
	public function getApplicationLanguage($default = 'ru_RU')
	{
		return $this->callSharedService('config', ['app'])->get('language', $default);
	}

	/**
	 * Получение запасного языка приложения
	 *
	 * @param   string   $default
	 *
	 * @access  public
	 * @return  string
	 */
	public function getApplicationFallbackLanguage($default = 'en_US')
	{
		return $this->callSharedService('config', ['app'])->get('language.fallback', $default);
	}

	/**
	 * Локализация сообщения
	 *
	 * @param   string   $section
	 * @param   string   $message
	 * @param   array    $context
	 *
	 * @access  public
	 * @return  string
	 */
	public function t($section, $message, array $context = [])
	{
		if ($this->callSharedService('locale', [$section])->has($message))
		{
			$message = $this->callSharedService('locale', [$section])->get($message);
		}

		return $this->interpolate($message, $context);
	}

	/**
	 * Интерполяция сообщения
	 *
	 * @param   string   $message
	 * @param   array    $context
	 *
	 * @access  public
	 * @return  string
	 */
	public function interpolate($message, array $context = [])
	{
		$substitutable = [];

		foreach ($context as $key => $value)
		{
			$substitutable['{' . $key . '}'] = $value;
		}

		return strtr($message, $substitutable);
	}
}

/**
 * Main function of framework
 *
 * @param   mixed   $alias
 * @param   mixed   $params
 *
 * @return  mixed
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
