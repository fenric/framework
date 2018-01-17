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
 * Import classes
 */
use SessionHandlerInterface;

/**
 * Session
 */
class Session extends Collection
{

	/**
	 * Handles the session
	 */
	public function handle(SessionHandlerInterface $handler) : bool
	{
		if ($this->isReady())
		{
			if (session_set_save_handler($handler, false))
			{
				return true;
			}
		}

		return false;
	}

	/**
	 * Starts the session
	 */
	public function start() : bool
	{
		if ($this->isReady())
		{
			fenric('event::session.before.start')->run([$this]);

			if (session_start(fenric('config::session')->toArray()))
			{
				fenric('event::session.after.start')->run([$this]);

				$this->update($_SESSION);

				register_shutdown_function(function() : void
				{
					$this->close();
				});

				return true;
			}
		}

		return false;
	}

	/**
	 * Restarts the session
	 */
	public function restart() : bool
	{
		if ($this->isStarted())
		{
			fenric('event::session.before.restart')->run([$this]);

			if (session_regenerate_id(true))
			{
				fenric('event::session.after.restart')->run([$this]);

				return true;
			}
		}

		return false;
	}

	/**
	 * Destroys the session
	 */
	public function destroy() : bool
	{
		if ($this->isStarted())
		{
			fenric('event::session.before.destroy')->run([$this]);

			if (session_destroy())
			{
				fenric('event::session.after.destroy')->run([$this]);

				return true;
			}
		}

		return false;
	}

	/**
	 * Closes the session
	 */
	public function close() : void
	{
		if ($this->isStarted())
		{
			$_SESSION = $this->toArray();

			fenric('event::session.before.close')->run([$this]);

			session_write_close();

			fenric('event::session.after.close')->run([$this]);
		}
	}

	/**
	 * Checks whether the session is ready
	 */
	public function isReady() : bool
	{
		return session_status() === PHP_SESSION_NONE;
	}

	/**
	 * Checks whether the session is started
	 */
	public function isStarted() : bool
	{
		return session_status() === PHP_SESSION_ACTIVE;
	}

	/**
	 * Gets ID of the session
	 */
	public function getId() :? string
	{
		return session_id() ?: null;
	}
}
