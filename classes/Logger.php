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
 * Logger
 */
class Logger
{

	/**
	 * Сообщение генерируемое с целью информирования
	 */
	const INFO = 'info';

	/**
	 * Сообщение генерируемое при возникновении ошибки высокого уровня
	 */
	const ERROR = 'error';

	/**
	 * Сообщение генерируемое при возникновении ошибки среднего уровня
	 */
	const WARNING = 'warning';

	/**
	 * Сообщение генерируемое при возникновении ошибки низкого уровня
	 */
	const NOTICE = 'notice';

	/**
	 * Сообщение генерируемое в процессе отладки
	 */
	const DEBUG = 'debug';

	/**
	 * Имя журнала
	 *
	 * @var     string
	 * @access  protected
	 */
	protected $name;

	/**
	 * Сообщения журнала
	 *
	 * @var     array
	 * @access  protected
	 */
	protected $messages = [];

	/**
	 * Конструктор класса
	 *
	 * @param   string   $name
	 *
	 * @access  public
	 * @return  void
	 */
	public function __construct($name)
	{
		$this->name = $name;

		register_shutdown_function(function()
		{
			$this->save();
		});
	}

	/**
	 * Получение имени журнала
	 *
	 * @access  public
	 * @return  string
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * Получение файла журнала
	 *
	 * @access  public
	 * @return  string
	 */
	public function getFile()
	{
		return fenric()->path('log', date('Y'), date('m'), date('d'), $this->getName() . '.log');
	}

	/**
	 * Добавление сообщения в журнал
	 *
	 * @param   string   $type
	 * @param   string   $message
	 * @param   array    $context
	 *
	 * @access  public
	 * @return  void
	 */
	public function add($type, $message, array $context = [])
	{
		$message = fenric()->interpolate($message, $context);

		$this->messages[] = [$type, $message, microtime(true)];
	}

	/**
	 * Добавление в журнал сообщения сгенерированного с целью информирования
	 *
	 * @param   string   $message
	 * @param   array    $context
	 *
	 * @access  public
	 * @return  void
	 */
	public function info($message, array $context = [])
	{
		$this->add(self::INFO, $message, $context);
	}

	/**
	 * Добавление в журнал сообщения сгенерированного при возникновении ошибки высокого уровня
	 *
	 * @param   string   $message
	 * @param   array    $context
	 *
	 * @access  public
	 * @return  void
	 */
	public function error($message, array $context = [])
	{
		$this->add(self::ERROR, $message, $context);
	}

	/**
	 * Добавление в журнал сообщения сгенерированного при возникновении ошибки среднего уровня
	 *
	 * @param   string   $message
	 * @param   array    $context
	 *
	 * @access  public
	 * @return  void
	 */
	public function warning($message, array $context = [])
	{
		$this->add(self::WARNING, $message, $context);
	}

	/**
	 * Добавление в журнал сообщения сгенерированного при возникновении ошибки низкого уровня
	 *
	 * @param   string   $message
	 * @param   array    $context
	 *
	 * @access  public
	 * @return  void
	 */
	public function notice($message, array $context = [])
	{
		$this->add(self::NOTICE, $message, $context);
	}

	/**
	 * Добавление в журнал сообщения сгенерированного в процессе отладки
	 *
	 * @param   string   $message
	 * @param   array    $context
	 *
	 * @access  public
	 * @return  void
	 */
	public function debug($message, array $context = [])
	{
		$this->add(self::DEBUG, $message, $context);
	}

	/**
	 * Добавление в журнал сообщения сгенерированного PHP
	 *
	 * @param   int      $type
	 * @param   string   $message
	 *
	 * @access  public
	 * @return  void
	 *
	 * @see     http://php.net/manual/ru/errorfunc.constants.php
	 */
	public function php($type, $message)
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
	 * Очистка журнала
	 *
	 * @access  public
	 * @return  void
	 */
	public function clear()
	{
		$this->messages = [];
	}

	/**
	 * Получение всех сообщений журнала
	 *
	 * @access  public
	 * @return  array
	 */
	public function all()
	{
		return $this->messages;
	}

	/**
	 * Получение количества сообщений журнала
	 *
	 * @access  public
	 * @return  int
	 */
	public function count()
	{
		return count($this->messages);
	}

	/**
	 * Сохранение журнала
	 *
	 * @access  public
	 * @return  void
	 */
	public function save()
	{
		if ($this->count() > 0)
		{
			$file = $this->getFile();

			$folder = pathinfo($file, PATHINFO_DIRNAME);

			if (is_dir($folder) or mkdir($folder, 0755, true))
			{
				if ($handle = fopen($file, 'a'))
				{
					foreach ($this->all() as $row)
					{
						fwrite($handle, sprintf('[%s] [%s] %s', date('Y-m-d H:i:s', $row[2]), $row[0], $row[1]) . PHP_EOL);
					}

					fclose($handle);
				}
			}
		}
	}
}
