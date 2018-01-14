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
 * Console
 */
class Console extends Collection
{

	/**
	 * Конструктор класса
	 */
	public function __construct(array $argv)
	{
		$args = [];

		if ($argc = count($argv))
		{
			for ($i = 1; $i < $argc; $i++)
			{
				if (strpos($argv[$i], '=') !== false)
				{
					list($key, $args[$key]) = explode('=', $argv[$i], 2);

					continue;
				}

				$args[$argv[$i]] = true;
			}
		}

		parent::__construct($args);
	}

	/**
	 * Создание строки черного цвета
	 */
	public function black(string $string) : string
	{
		return $this->foreground(0, $string);
	}

	/**
	 * Создание строки красного цвета
	 */
	public function red(string $string) : string
	{
		return $this->foreground(1, $string);
	}

	/**
	 * Создание строки зелёного цвета
	 */
	public function green(string $string) : string
	{
		return $this->foreground(2, $string);
	}

	/**
	 * Создание строки жёлтого цвета
	 */
	public function yellow(string $string) : string
	{
		return $this->foreground(3, $string);
	}

	/**
	 * Создание строки синего цвета
	 */
	public function blue(string $string) : string
	{
		return $this->foreground(4, $string);
	}

	/**
	 * Создание строки пурпурного цвета
	 */
	public function purple(string $string) : string
	{
		return $this->foreground(5, $string);
	}

	/**
	 * Создание строки бирюзового цвета
	 */
	public function cyan(string $string) : string
	{
		return $this->foreground(6, $string);
	}

	/**
	 * Создание строки белого цвета
	 */
	public function white(string $string) : string
	{
		return $this->foreground(7, $string);
	}

	/**
	 * Создание полужирной строки
	 */
	public function bold(string $string) : string
	{
		return sprintf("\033[1m%s\033[22m", $string);
	}

	/**
	 * Создание подчеркнутой строки
	 */
	public function underline(string $string) : string
	{
		return sprintf("\033[4m%s\033[24m", $string);
	}

	/**
	 * Определение цвета для переднего фона
	 */
	public function foreground(int $color, string $string) : string
	{
		return sprintf("\033[9%dm%s", $color, $string);
	}

	/**
	 * Определение цвета для заднего фона
	 */
	public function background(int $color, string $string) : string
	{
		return sprintf("\033[10%dm%s", $color, $string);
	}

	/**
	 * Скрытие вводных символов
	 */
	public function hide() : void
	{
		fwrite(STDOUT, "\033[8m");
	}

	/**
	 * Отображение вводных символов
	 */
	public function show() : void
	{
		fwrite(STDOUT, "\033[28m");
	}

	/**
	 * Запись данных в поток вывода
	 */
	public function write(string $string, string $end = PHP_EOL) : void
	{
		fwrite(STDOUT, $string . "\033[0m" . $end);
	}

	/**
	 * Чтение данных из потока ввода
	 */
	public function read() : string
	{
		return trim(fgets(STDIN));
	}
}
