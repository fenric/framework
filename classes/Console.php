<?php
/**
 * It's free open-source software released under the MIT License.
 *
 * @author Anatoly Fenric <a.fenric@gmail.com>
 * @copyright Copyright (c) 2013-2017 by Fenric Laboratory
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
	 * Черный цвет
	 *
	 * @var   int
	 */
	const BLACK = 0;

	/**
	 * Красный цвет
	 *
	 * @var   int
	 */
	const RED = 1;

	/**
	 * Зеленный цвет
	 *
	 * @var   int
	 */
	const GREEN = 2;

	/**
	 * Желтый цвет
	 *
	 * @var   int
	 */
	const YELLOW = 3;

	/**
	 * Синий цвет
	 *
	 * @var   int
	 */
	const BLUE = 4;

	/**
	 * Пурпурный цвет
	 *
	 * @var   int
	 */
	const PURPLE = 5;

	/**
	 * Бирюзовый цвет
	 *
	 * @var   int
	 */
	const CYAN = 6;

	/**
	 * Белый цвет
	 *
	 * @var   int
	 */
	const WHITE = 7;

	/**
	 * Конструктор класса
	 *
	 * @param   array   $argv
	 *
	 * @access  public
	 * @return  void
	 */
	public function __construct(array $argv)
	{
		if ($argc = count($argv))
		{
			for ($i = 1; $i < $argc; $i++)
			{
				if (false !== strpos($argv[$i], '='))
				{
					list($key, $value) = explode('=', $argv[$i], 2);

					$this->set($key, $value);

					continue;
				}

				$this->set($argv[$i], true);
			}
		}
	}

	/**
	 * Вывод в консоль строки с символами чёрного цвета
	 *
	 * @param   string   $string
	 *
	 * @access  public
	 * @return  void
	 */
	public function black($string)
	{
		$this->put($this->foreground($string, self::BLACK));
	}

	/**
	 * Вывод в консоль строки с символами красного цвета
	 *
	 * @param   string   $string
	 *
	 * @access  public
	 * @return  void
	 */
	public function red($string)
	{
		$this->put($this->foreground($string, self::RED));
	}

	/**
	 * Вывод в консоль строки с символами зелёного цвета
	 *
	 * @param   string   $string
	 *
	 * @access  public
	 * @return  void
	 */
	public function green($string)
	{
		$this->put($this->foreground($string, self::GREEN));
	}

	/**
	 * Вывод в консоль строки с символами жёлтого цвета
	 *
	 * @param   string   $string
	 *
	 * @access  public
	 * @return  void
	 */
	public function yellow($string)
	{
		$this->put($this->foreground($string, self::YELLOW));
	}

	/**
	 * Вывод в консоль строки с символами синего цвета
	 *
	 * @param   string   $string
	 *
	 * @access  public
	 * @return  void
	 */
	public function blue($string)
	{
		$this->put($this->foreground($string, self::BLUE));
	}

	/**
	 * Вывод в консоль строки с символами пурпурного цвета
	 *
	 * @param   string   $string
	 *
	 * @access  public
	 * @return  void
	 */
	public function purple($string)
	{
		$this->put($this->foreground($string, self::PURPLE));
	}

	/**
	 * Вывод в консоль строки с символами бирюзового цвета
	 *
	 * @param   string   $string
	 *
	 * @access  public
	 * @return  void
	 */
	public function cyan($string)
	{
		$this->put($this->foreground($string, self::CYAN));
	}

	/**
	 * Вывод в консоль строки с символами белого цвета
	 *
	 * @param   string   $string
	 *
	 * @access  public
	 * @return  void
	 */
	public function white($string)
	{
		$this->put($this->foreground($string, self::WHITE));
	}

	/**
	 * Вывод в консоль строки с символами разных цветов
	 *
	 * @param   string   $string
	 *
	 * @access  public
	 * @return  void
	 */
	public function motley($string)
	{
		$characters = str_split($string);

		foreach ($characters as $i => $character)
		{
			$characters[$i] = $this->foreground($character, mt_rand(0, 7));
		}

		$this->put(implode($characters));
	}

	/**
	 * Вывод в консоль вопроса
	 *
	 * @param   string   $question
	 * @param   string   $parameter
	 * @param   bool     $required
	 * @param   bool     $password
	 *
	 * @access  public
	 * @return  bool
	 */
	public function ask($question, $parameter, $required = false, $password = false)
	{
		$this->put($question);

		$input = $this->read($password);

		if (strlen($input) > 0)
		{
			$this->set($parameter, $input);

			return true;
		}

		else while ($required)
		{
			if ($this->ask($question, $parameter, false, $password))
			{
				return true;
			}
		}

		return false;
	}

	/**
	 * Вывод в консоль подтверждения
	 *
	 * @param   string   $message
	 *
	 * @access  public
	 * @return  bool
	 */
	public function confirm($message = null)
	{
		$this->put($message ?: sprintf('Please confirm the action, enter [%s] or [%s]',
			$this->bold($this->underline('yes')), $this->bold($this->underline('no'))));

		$input = strtolower($this->read());

		if (in_array($input, ['true', 'yes', 'y', 'on', '1'], true))
		{
			return true;
		}

		else if (in_array($input, ['false', 'no', 'n', 'off', '0'], true))
		{
			return false;
		}

		return $this->confirm($message);
	}

	/**
	 * Определение цвета для переднего фона
	 *
	 * @param   string   $string
	 * @param   int      $foreground
	 *
	 * @access  public
	 * @return  string
	 */
	public function foreground($string, $foreground)
	{
		if (fenric()->is('windows'))
		{
			return $string;
		}

		return sprintf("\033[3%dm%s", $foreground, $string);
	}

	/**
	 * Определение цвета для заднего фона
	 *
	 * @param   string   $string
	 * @param   int      $background
	 *
	 * @access  public
	 * @return  string
	 */
	public function background($string, $background)
	{
		if (fenric()->is('windows'))
		{
			return $string;
		}

		return sprintf("\033[4%dm%s", $background, $string);
	}

	/**
	 * Определение полужирной строки
	 *
	 * @param   string   $string
	 *
	 * @access  public
	 * @return  string
	 */
	public function bold($string)
	{
		if (fenric()->is('windows'))
		{
			return $string;
		}

		return sprintf("\033[1m%s\033[22m", $string);
	}

	/**
	 * Определение подчеркнутой строки
	 *
	 * @param   string   $string
	 *
	 * @access  public
	 * @return  string
	 */
	public function underline($string)
	{
		if (fenric()->is('windows'))
		{
			return $string;
		}

		return sprintf("\033[4m%s\033[24m", $string);
	}

	/**
	 * Определение инвертированной строки
	 *
	 * @param   string   $string
	 *
	 * @access  public
	 * @return  string
	 */
	public function invert($string)
	{
		if (fenric()->is('windows'))
		{
			return $string;
		}

		return sprintf("\033[7m%s\033[27m", $string);
	}

	/**
	 * Вывод строки в консоль
	 *
	 * @param   string   $string
	 *
	 * @access  public
	 * @return  void
	 */
	public function put($string)
	{
		if (! fenric()->is('windows'))
		{
			$string .= "\033[0m";
		}

		fwrite(STDOUT, $string . PHP_EOL);
	}

	/**
	 * Чтение входной строки из консоли
	 *
	 * @param   bool   $password
	 *
	 * @access  public
	 * @return  string
	 */
	public function read($password = false)
	{
		if (! fenric()->is('windows') && $password)
		{
			fwrite(STDOUT, "\033[8m");
		}

		$input = trim(fgets(STDIN));

		if (! fenric()->is('windows') && $password)
		{
			fwrite(STDOUT, "\033[28m");
		}

		return $input;
	}
}
