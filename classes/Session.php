<?php
/**
 * It is free open-source software released under the MIT License.
 *
 * @author Anatoly Fenric <a.fenric@gmail.com>
 * @copyright Copyright (c) 2013-2016 by Fenric Laboratory
 * @license https://github.com/fenric/framework/blob/master/LICENSE.md
 * @link https://github.com/fenric/framework
 */

namespace Fenric;

/**
 * Import classes
 */
use SessionHandlerInterface;

/**
 * Session
 */
class Session extends Collection
{

	/**
	 * Запуск сессии
	 *
	 * @param   object   $handler
	 *
	 * @access  public
	 * @return  bool
	 */
	public function start(SessionHandlerInterface $handler)
	{
		if ($this->isReady())
		{
			if (session_set_save_handler($handler, false))
			{
				if (session_start())
				{
					$this->update($_SESSION);

					return true;
				}
			}
		}

		return false;
	}

	/**
	 * Перезапуск сессии
	 *
	 * @access  public
	 * @return  bool
	 */
	public function restart()
	{
		if ($this->isStarted())
		{
			if (session_regenerate_id(true))
			{
				return true;
			}
		}

		return false;
	}

	/**
	 * Разрушение сессии
	 *
	 * @access  public
	 * @return  bool
	 */
	public function destroy()
	{
		if ($this->isStarted())
		{
			if (session_destroy())
			{
				return true;
			}
		}

		return false;
	}

	/**
	 * Закрытие сессии
	 *
	 * @access  public
	 * @return  void
	 */
	public function close()
	{
		if ($this->isStarted())
		{
			$_SESSION = $this->all();

			session_write_close();
		}
	}

	/**
	 * Готов ли механизм сессий к запуску
	 *
	 * @access  public
	 * @return  bool
	 */
	public function isReady()
	{
		if (session_status() === PHP_SESSION_NONE)
		{
			return true;
		}

		return false;
	}

	/**
	 * Запущен ли механизм сессий
	 *
	 * @access  public
	 * @return  bool
	 */
	public function isStarted()
	{
		if (session_status() === PHP_SESSION_ACTIVE)
		{
			return true;
		}

		return false;
	}
}
