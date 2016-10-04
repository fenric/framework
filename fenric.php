<?php
/**
 * It is free open-source software released under the MIT License.
 *
 * @author       Anatoly Nekhay <a.fenric@gmail.com>
 * @copyright    Copyright (c) 2013-2016 by Fenric Laboratory
 * @license      http://fenric.ru/license/
 * @link         http://fenric.ru/
 */

/**
 * Основной класс фреймворка
 */
final class Fenric
{

	/**
	 * Версия фреймворка
	 */
	const VERSION = '1.7.0-dev';

	/**
	 * Параметры фреймворка
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
	 * События фреймворка
	 *
	 * @var     array
	 * @access  protected
	 */
	protected $events = [];

	/**
	 * Журнал фреймворка
	 *
	 * @var     array
	 * @access  protected
	 */
	protected $log = [];

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
		$this->options['autoload']['enabled'] = true;

		/**
		 * Автозагрузчик внутренних классов
		 *
		 * @var callable
		 */
		$this->options['autoload']['handler'] = null;

		/**
		 * Пути в порядке приоритетности по которым осуществляется поиск и загрузка внутренних классов
		 *
		 * @var array
		 */
		$this->options['autoload']['pathmap'] = [':app/classes/:class.php', ':system/classes/:class.php'];

		/**
		 * Обработка ошибок
		 *
		 * @var bool
		 */
		$this->options['handling']['error']['enabled'] = true;

		/**
		 * Обработчик ошибок
		 *
		 * @var callable
		 */
		$this->options['handling']['error']['handler'] = null;

		/**
		 * Обработка неперехваченных исключений
		 *
		 * @var bool
		 */
		$this->options['handling']['exception']['enabled'] = true;

		/**
		 * Обработчик неперехваченных исключений
		 *
		 * @var callable
		 */
		$this->options['handling']['exception']['handler'] = null;

		/**
		 * Обработка фатальных ошибок
		 *
		 * @var bool
		 */
		$this->options['handling']['fatality']['enabled'] = true;

		/**
		 * Обработчик фатальных ошибок
		 *
		 * @var callable
		 */
		$this->options['handling']['fatality']['handler'] = null;

		/**
		 * Режим протоколирования фатальных ошибок
		 *
		 * @var int
		 */
		$this->options['handling']['fatality']['errmode'] = E_ERROR | E_PARSE | E_CORE_ERROR | E_CORE_WARNING | E_COMPILE_ERROR | E_COMPILE_WARNING | E_USER_ERROR;

		/**
		 * Обработчик аварийной остановки
		 *
		 * @var callable
		 */
		$this->options['handling']['crash'] = null;

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
	public function init(array $options = null)
	{
		if (is_array($options))
		{
			$this->options = array_replace_recursive($this->options, $options);
		}

		if ($this->options['autoload']['enabled'])
		{
			if (! is_callable($this->options['autoload']['handler']))
			{
				$this->options['autoload']['handler'] = [$this, 'autoload'];
			}

			spl_autoload_register($this->options['autoload']['handler'], true, true);
		}

		if ($this->options['handling']['error']['enabled'])
		{
			if (! is_callable($this->options['handling']['error']['handler']))
			{
				$this->options['handling']['error']['handler'] = [$this, 'errorHandler'];
			}

			set_error_handler($this->options['handling']['error']['handler']);
		}

		if ($this->options['handling']['exception']['enabled'])
		{
			if (! is_callable($this->options['handling']['exception']['handler']))
			{
				$this->options['handling']['exception']['handler'] = [$this, 'exceptionHandler'];
			}

			set_exception_handler($this->options['handling']['exception']['handler']);
		}

		if ($this->options['handling']['fatality']['enabled'])
		{
			if (! is_callable($this->options['handling']['fatality']['handler']))
			{
				$this->options['handling']['fatality']['handler'] = [$this, 'fatalityHandler'];
			}

			register_shutdown_function($this->options['handling']['fatality']['handler']);
		}
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
			// Загружено ли PHP расширение
			case 'ext' :
			case 'extension' :
				return extension_loaded(func_get_arg(1));
				break;

			// Запущен ли фреймворк в операционной системе семейства «Windows»
			case 'win' :
			case 'windows' :
				return strncasecmp(PHP_OS, 'win', 3) === 0;
				break;

			case 'not win' :
			case 'not windows' :
				return strncasecmp(PHP_OS, 'win', 3) !== 0;
				break;

			// Запущен ли фреймворк в консоли
			case 'cli' :
			case 'console' :
				return strcasecmp(PHP_SAPI, 'cli') === 0;
				break;

			case 'not cli' :
			case 'not console' :
				return strcasecmp(PHP_SAPI, 'cli') !== 0;
				break;

			// Запущен ли фреймворк в режиме отладки
			case 'test' :
			case 'debug' :
				return strcasecmp($this->options['env'], 'test') === 0;
				break;

			case 'not test' :
			case 'not debug' :
				return strcasecmp($this->options['env'], 'test') !== 0;
				break;

			// Запущен ли фреймворк на «production» сервере
			case 'prod' :
			case 'production' :
				return strcasecmp($this->options['env'], 'production') === 0;
				break;

			case 'not prod' :
			case 'not production' :
				return strcasecmp($this->options['env'], 'production') !== 0;
				break;

			// Запущен ли фреймворк на «development» сервере
			case 'dev' :
			case 'development' :
				return strcasecmp($this->options['env'], 'development') === 0;
				break;

			case 'not dev' :
			case 'not development' :
				return strcasecmp($this->options['env'], 'development') !== 0;
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
		$this->services['shared'][$alias]['service'] = null;
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
		$this->services['shared'][$alias]['listeners'] = null;
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
	public function registerResolveredSharedService($alias, callable $callable)
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
	 * Регистрация слушателя события
	 *
	 * @param   string   $groupname
	 * @param   string   $eventname
	 * @param   call     $listener
	 *
	 * @access  public
	 * @return  void
	 */
	public function registerEventListener($groupname, $eventname, callable $listener)
	{
		$this->events[$groupname][$eventname][] = $listener;
	}

	/**
	 * Разрегистрация слушателей события
	 *
	 * @param   string   $groupname
	 * @param   string   $eventname
	 *
	 * @access  public
	 * @return  void
	 */
	public function unregisterEventListeners($groupname, $eventname)
	{
		$this->events[$groupname][$eventname] = null;
	}

	/**
	 * Вызов слушателей события
	 *
	 * @param   string   $groupname
	 * @param   string   $eventname
	 * @param   mixed    $params
	 *
	 * @access  public
	 * @return  bool
	 */
	public function dispatchEvent($groupname, $eventname, $params = null)
	{
		$params = (array) $params;

		if (isset($this->events[$groupname][$eventname]))
		{
			foreach ($this->events[$groupname][$eventname] as $listener)
			{
				if (false === call_user_func_array($listener, $params))
				{
					return false;
				}
			}
		}

		return true;
	}

	/**
	 * Добавление PHP сообщения в журнал
	 *
	 * @param   int      $type
	 * @param   string   $message
	 *
	 * @access  public
	 * @return  void
	 *
	 * @see     http://php.net/manual/errorfunc.constants.php
	 */
	public function eLog($type, $message)
	{
		switch ($type)
		{
			case E_ERROR :
			case E_PARSE :
			case E_CORE_ERROR :
			case E_COMPILE_ERROR :
			case E_USER_ERROR :
			case E_RECOVERABLE_ERROR :
				$this->error($message);
				break;

			case E_WARNING :
			case E_CORE_WARNING :
			case E_COMPILE_WARNING :
			case E_USER_WARNING :
				$this->warning($message);
				break;

			case E_NOTICE :
			case E_USER_NOTICE :
				$this->notice($message);
				break;

			case E_STRICT :
			case E_DEPRECATED :
			case E_USER_DEPRECATED :
				$this->debug($message);
				break;
		}
	}

	/**
	 * Добавление сообщения в журнал сгенерированного с целью информирования
	 *
	 * @param   string   $message
	 *
	 * @access  public
	 * @return  void
	 */
	public function info($message)
	{
		$this->log[] = ['info', $message, microtime(true)];
	}

	/**
	 * Добавление сообщения в журнал сгенерированного при возникновении ошибки высокого уровня
	 *
	 * @param   string   $message
	 *
	 * @access  public
	 * @return  void
	 */
	public function error($message)
	{
		$this->log[] = ['error', $message, microtime(true)];
	}

	/**
	 * Добавление сообщения в журнал сгенерированного при возникновении ошибки среднего уровня
	 *
	 * @param   string   $message
	 *
	 * @access  public
	 * @return  void
	 */
	public function warning($message)
	{
		$this->log[] = ['warning', $message, microtime(true)];
	}

	/**
	 * Добавление сообщения в журнал сгенерированного при возникновении ошибки низкого уровня
	 *
	 * @param   string   $message
	 *
	 * @access  public
	 * @return  void
	 */
	public function notice($message)
	{
		$this->log[] = ['notice', $message, microtime(true)];
	}

	/**
	 * Добавление сообщения в журнал сгенерированного в процессе отладки
	 *
	 * @param   string   $message
	 *
	 * @access  public
	 * @return  void
	 */
	public function debug($message)
	{
		$this->log[] = ['debug', $message, microtime(true)];
	}

	/**
	 * Получение журнала фреймворка
	 *
	 * @access  public
	 * @return  array
	 */
	public function log()
	{
		return $this->log;
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

			foreach ($this->options['autoload']['pathmap'] as $maskedPath)
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
	 *
	 * @throws  ErrorException
	 */
	public function errorHandler($type, $message, $file, $line)
	{
		if ($type & error_reporting())
		{
			throw new ErrorException($message, 0, $type, $file, $line);
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
	public function exceptionHandler($exception)
	{
		$this->eLog(E_USER_ERROR, sprintf('%s (%s #%d)', $exception->getMessage(), $exception->getFile(), $exception->getLine()));

		$this->crash($exception->getMessage(), $exception->getFile(), $exception->getLine());
	}

	/**
	 * Обработчик фатальных ошибок
	 *
	 * @access  public
	 * @return  void
	 */
	public function fatalityHandler()
	{
		if ($error = error_get_last())
		{
			if ($error['type'] & $this->options['handling']['fatality']['errmode'])
			{
				$this->eLog($error['type'], sprintf('%s (%s #%d)', $error['message'], $error['file'], $error['line']));

				$this->crash($error['message'], $error['file'], $error['line']);
			}
		}
	}

	/**
	 * Аварийная остановка приложения
	 *
	 * @param   string   $message
	 * @param   string   $file
	 * @param   int      $line
	 * @param   int      $code
	 *
	 * @access  public
	 * @return  void
	 */
	public function crash($message, $file, $line, $code = 1)
	{
		if (is_callable($this->options['handling']['crash']))
		{
			call_user_func($this->options['handling']['crash'], $message, $file, $line);
		}

		exit($code);
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

			$params = [$resolver, $params];
		}

		return $instance->callSharedService($alias, $params);
	}

	return $instance;
}
