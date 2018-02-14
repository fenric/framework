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

	public const STYLE_BOLD         = [1, 22];
	public const STYLE_UNDERLINE    = [4, 24];
	public const STYLE_BLINK        = [5, 25];
	public const STYLE_REVERSE      = [7, 27];
	public const STYLE_HIDDEN       = [8, 28];

	public const FOREGROUND_BLACK   = [30, 39];
	public const FOREGROUND_RED     = [31, 39];
	public const FOREGROUND_GREEN   = [32, 39];
	public const FOREGROUND_YELLOW  = [33, 39];
	public const FOREGROUND_BLUE    = [34, 39];
	public const FOREGROUND_PURPLE  = [35, 39];
	public const FOREGROUND_CYAN    = [36, 39];
	public const FOREGROUND_WHITE   = [37, 39];
	public const FOREGROUND_DEFAULT = [39, 39];
	public const FOREGROUND_RESET   = [39, 39];

	public const BACKGROUND_BLACK   = [40, 49];
	public const BACKGROUND_RED     = [41, 49];
	public const BACKGROUND_GREEN   = [42, 49];
	public const BACKGROUND_YELLOW  = [43, 49];
	public const BACKGROUND_BLUE    = [44, 49];
	public const BACKGROUND_PURPLE  = [45, 49];
	public const BACKGROUND_CYAN    = [46, 49];
	public const BACKGROUND_WHITE   = [47, 49];
	public const BACKGROUND_DEFAULT = [49, 49];
	public const BACKGROUND_RESET   = [49, 49];

	/**
	 * Консольные команды
	 */
	protected $commands = [];

	/**
	 * Конструктор класса
	 */
	public function __construct(array $tokens)
	{
		// Всегда содержит имя файла
		unset($tokens[0]);

		parent::__construct(
			$this->parse($tokens)
		);
	}

	/**
	 * Создание жирной строки
	 */
	public function bold(string $string) : string
	{
		return $this->style($string, [
			self::STYLE_BOLD,
		]);
	}

	/**
	 * Создание подчеркнутой строки
	 */
	public function underline(string $string) : string
	{
		return $this->style($string, [
			self::STYLE_UNDERLINE,
		]);
	}

	/**
	 * Создание мигающей строки
	 */
	public function blink(string $string) : string
	{
		return $this->style($string, [
			self::STYLE_BLINK,
		]);
	}

	/**
	 * Создание строки черного цвета
	 */
	public function black(string $string) : string
	{
		return $this->style($string, [
			self::FOREGROUND_BLACK,
		]);
	}

	/**
	 * Создание строки красного цвета
	 */
	public function red(string $string) : string
	{
		return $this->style($string, [
			self::FOREGROUND_RED,
		]);
	}

	/**
	 * Создание строки зелёного цвета
	 */
	public function green(string $string) : string
	{
		return $this->style($string, [
			self::FOREGROUND_GREEN,
		]);
	}

	/**
	 * Создание строки жёлтого цвета
	 */
	public function yellow(string $string) : string
	{
		return $this->style($string, [
			self::FOREGROUND_YELLOW,
		]);
	}

	/**
	 * Создание строки синего цвета
	 */
	public function blue(string $string) : string
	{
		return $this->style($string, [
			self::FOREGROUND_BLUE,
		]);
	}

	/**
	 * Создание строки пурпурного цвета
	 */
	public function purple(string $string) : string
	{
		return $this->style($string, [
			self::FOREGROUND_PURPLE,
		]);
	}

	/**
	 * Создание строки бирюзового цвета
	 */
	public function cyan(string $string) : string
	{
		return $this->style($string, [
			self::FOREGROUND_CYAN,
		]);
	}

	/**
	 * Создание строки белого цвета
	 */
	public function white(string $string) : string
	{
		return $this->style($string, [
			self::FOREGROUND_WHITE,
		]);
	}

	/**
	 * Вывод информации
	 */
	public function info(string $string)
	{
		return $this->line($this->style($string, [
			self::FOREGROUND_GREEN,
		]));
	}

	/**
	 * Вывод сообщения
	 */
	public function comment(string $string)
	{
		return $this->line($this->style($string, [
			self::FOREGROUND_YELLOW,
		]));
	}

	/**
	 * Вывод предупреждения
	 */
	public function warning(string $string)
	{
		return $this->line($this->style($string, [
			self::FOREGROUND_BLACK,
			self::BACKGROUND_YELLOW,
		]));
	}

	/**
	 * Вывод ошибки
	 */
	public function error(string $string)
	{
		return $this->line($this->style($string, [
			self::FOREGROUND_WHITE,
			self::BACKGROUND_RED,
		]));
	}

	/**
	 * Чтение очищенной от пробельных символов строки из потока ввода
	 */
	public function read()
	{
		return trim($this->stdin());
	}

	/**
	 * Запись в поток вывода строки с последующим разрывом строки
	 */
	public function line(string $string)
	{
		return $this->stdout($string . PHP_EOL);
	}

	/**
	 * Чтение потока ввода
	 */
	public function stdin()
	{
		return fgets(STDIN);
	}

	/**
	 * Запись в поток вывода
	 */
	public function stdout(string $string)
	{
		return fwrite(STDOUT, $this->format($string));
	}

	/**
	 * Запись в поток вывода ошибок
	 */
	public function stderr(string $string)
	{
		return fwrite(STDERR, $this->format($string));
	}

	/**
	 * Стилизация строки
	 */
	public function style(string $string, array $styles) : string
	{
		$open = $close = [];

		foreach ($styles as $style)
		{
			list($open[], $close[]) = $style;
		}

		return sprintf("\033[%sm%s\033[%sm", join(';', $open), $string, join(';', $close));
	}

	/**
	 * Разбор вводных токенов
	 */
	public function parse(array $tokens) : array
	{
		$result = [];
		$lastOption = '';

		foreach ($tokens as $token)
		{
			// Short option
			if (strlen($token) > 1)
			{
				if (strcmp($token[0], '-') === 0)
				{
					if (strcmp($token[1], '-') !== 0)
					{
						$options = substr($token, 1);
						$lastOption = substr($token, -1);
						$equalpos = strpos($token, '=');
						$value = true;

						if ($equalpos !== false)
						{
							$options = substr($token, 1, $equalpos - 1);
							$lastOption = '';
							$value = substr($token, $equalpos + 1);
						}

						$length = strlen($options);

						for ($i = 0; $i < $length; $i++)
						{
							$result[$options[$i]] = $value;
						}

						continue;
					}
				}
			}

			// Long option
			if (strlen($token) > 2)
			{
				if (strcmp($token[0], '-') === 0)
				{
					if (strcmp($token[1], '-') === 0)
					{
						$option = substr($token, 2);
						$lastOption = substr($token, 2);
						$equalpos = strpos($token, '=');
						$value = true;

						if ($equalpos !== false)
						{
							$option = substr($token, 2, $equalpos - 2);
							$lastOption = '';
							$value = substr($token, $equalpos + 1);
						}

						$result[$option] = $value;

						continue;
					}
				}
			}

			// Option value
			if (strlen($lastOption) > 0)
			{
				$result[$lastOption] = $token;

				$lastOption = '';

				continue;
			}

			// Arguments...
			$result[] = $token;
		}

		return $result;
	}
}
