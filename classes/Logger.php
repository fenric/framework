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
		return fenric()->path('log', date('Y'), date('m'), date('d'), $this->getName() . '.log');
	}

	/**
	 * Добавление сообщения в журнал
	 */
	public function add(string $type, string $message, array $context = []) : void
	{
		$message = fenric()->interpolate($message, $context);

		$this->messages[] = [$type, $message, microtime(true)];
	}

	/**
	 * Добавление в журнал сообщения сгенерированного с целью информирования
	 */
	public function info(string $message, array $context = []) : void
	{
		$this->add(self::INFO, $message, $context);
	}

	/**
	 * Добавление в журнал сообщения сгенерированного при возникновении ошибки высокого уровня
	 */
	public function error(string $message, array $context = []) : void
	{
		$this->add(self::ERROR, $message, $context);
	}

	/**
	 * Добавление в журнал сообщения сгенерированного при возникновении ошибки среднего уровня
	 */
	public function warning(string $message, array $context = []) : void
	{
		$this->add(self::WARNING, $message, $context);
	}

	/**
	 * Добавление в журнал сообщения сгенерированного при возникновении ошибки низкого уровня
	 */
	public function notice(string $message, array $context = []) : void
	{
		$this->add(self::NOTICE, $message, $context);
	}

	/**
	 * Добавление в журнал сообщения сгенерированного в процессе отладки
	 */
	public function debug(string $message, array $context = []) : void
	{
		$this->add(self::DEBUG, $message, $context);
	}

	/**
	 * Очистка журнала
	 */
	public function clear() : void
	{
		$this->messages = [];
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
