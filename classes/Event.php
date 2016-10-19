<?php
/**
 * It is free open-source software released under the MIT License.
 *
 * @author       Anatoly Nekhay <a.fenric@gmail.com>
 * @copyright    Copyright (c) 2013-2016 by Fenric Laboratory
 * @license      https://github.com/fenric/framework/blob/master/LICENSE.md
 * @link         https://github.com/fenric/framework
 */

namespace Fenric;

/**
 * Event
 */
class Event
{

	/**
	 * Подписчики события
	 *
	 * @var     array
	 * @access  protected
	 */
	protected $subscribers = [];

	/**
	 * Регистрация подписчика события
	 *
	 * @param   call     $subscriber
	 *
	 * @access  public
	 * @return  void
	 */
	public function subscribe(callable $subscriber)
	{
		$this->subscribers[] = $subscriber;
	}

	/**
	 * Уведомление подписчиков о наступившем событии
	 *
	 * @param   array    $params
	 *
	 * @access  public
	 * @return  bool
	 */
	public function notifySubscribers(array $params = [])
	{
		foreach ($this->subscribers as $subscriber)
		{
			if (false === call_user_func_array($subscriber, $params))
			{
				return false;
			}
		}

		return true;
	}
}
