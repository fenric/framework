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
	 */
	public function start(SessionHandlerInterface $handler) : bool
	{
		if ($this->isReady())
		{
			$options = fenric('config::session')->all();

			if (session_set_save_handler($handler, false))
			{
				if (session_start($options))
				{
					$this->update($_SESSION);

					register_shutdown_function(function() : void
					{
						$this->close();
					});

					return true;
				}
			}
		}

		return false;
	}

	/**
	 * Перезапуск сессии
	 */
	public function restart() : bool
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
	 */
	public function destroy() : bool
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
	 */
	public function close() : void
	{
		if ($this->isStarted())
		{
			$_SESSION = $this->all();

			session_write_close();
		}
	}

	/**
	 * Готов ли механизм сессий к запуску
	 */
	public function isReady() : bool
	{
		if (session_status() === PHP_SESSION_NONE)
		{
			return true;
		}

		return false;
	}

	/**
	 * Запущен ли механизм сессий
	 */
	public function isStarted() : bool
	{
		if (session_status() === PHP_SESSION_ACTIVE)
		{
			return true;
		}

		return false;
	}

	/**
	 * Получение идентификатора сессии
	 */
	public function getId() : string
	{
		return session_id();
	}
}
