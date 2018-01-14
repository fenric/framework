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
 * Logger
 */
class Logger
{

	/**
	 * Сообщение генерируемое с целью информирования
	 */
	public const INFO = 'info';

	/**
	 * Сообщение генерируемое при возникновении ошибки высокого уровня
	 */
	public const ERROR = 'error';

	/**
	 * Сообщение генерируемое при возникновении ошибки среднего уровня
	 */
	public const WARNING = 'warning';

	/**
	 * Сообщение генерируемое при возникновении ошибки низкого уровня
	 */
	public const NOTICE = 'notice';

	/**
	 * Сообщение генерируемое в процессе отладки
	 */
	public const DEBUG = 'debug';

	/**
	 * Имя журнала
	 */
	protected $name;

	/**
	 * Сообщения журнала
	 */
	protected $messages = [];

	/**
	 * Конструктор класса
	 */
	public function __construct(string $name)
	{
		$this->name = $name;

		register_shutdown_function(function() : void
		{
			$this->save();
		});
	}

	/**
	 * Получение имени журнала
	 */
	public function getName() : string
	{
		return $this->name;
	}

	/**
	 * Получение файла журнала
	 */
	public function getFile() : string
	{
		return fenric()->path('log', date('Y'), date('m'), date('d'), $this->getName() . '.log')->getPathname();
	}

	/**
	 * Добавление сообщения в журнал
	 */
	public function add(string $type, string $message, array $context = []) : self
	{
		$message = fenric()->interpolate($message, $context);

		$this->messages[] = [$type, $message, microtime(true)];

		return $this;
	}

	/**
	 * Добавление в журнал сообщения сгенерированного с целью информирования
	 */
	public function info(string $message, array $context = []) : self
	{
		return $this->add(self::INFO, $message, $context);
	}

	/**
	 * Добавление в журнал сообщения сгенерированного при возникновении ошибки высокого уровня
	 */
	public function error(string $message, array $context = []) : self
	{
		return $this->add(self::ERROR, $message, $context);
	}

	/**
	 * Добавление в журнал сообщения сгенерированного при возникновении ошибки среднего уровня
	 */
	public function warning(string $message, array $context = []) : self
	{
		return $this->add(self::WARNING, $message, $context);
	}

	/**
	 * Добавление в журнал сообщения сгенерированного при возникновении ошибки низкого уровня
	 */
	public function notice(string $message, array $context = []) : self
	{
		return $this->add(self::NOTICE, $message, $context);
	}

	/**
	 * Добавление в журнал сообщения сгенерированного в процессе отладки
	 */
	public function debug(string $message, array $context = []) : self
	{
		return $this->add(self::DEBUG, $message, $context);
	}

	/**
	 * Очистка журнала
	 */
	public function clear() : self
	{
		$this->messages = [];

		return $this;
	}

	/**
	 * Получение всех сообщений журнала
	 */
	public function all() : array
	{
		return $this->messages;
	}

	/**
	 * Получение количества сообщений журнала
	 */
	public function count() : int
	{
		return count($this->messages);
	}

	/**
	 * Сохранение журнала
	 */
	public function save() : void
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
						$datetime = date('Y-m-d H:i:s', $row[2]);

						fwrite($handle, sprintf('[%s] [%s] %s', $datetime, $row[0], $row[1]) . PHP_EOL);
					}

					fclose($handle);
				}
			}
		}
	}
}
