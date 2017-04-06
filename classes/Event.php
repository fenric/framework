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
 * Event
 */
class Event
{

	/**
	 * Имя события
	 */
	protected $name;

	/**
	 * Подписчики события
	 */
	protected $subscribers = [];

	/**
	 * Конструктор класса
	 */
	public function __construct(string $name)
	{
		$this->name = $name;
	}

	/**
	 * Получение имени события
	 */
	public function getName() : string
	{
		return $this->name;
	}

	/**
	 * Подписка на событие
	 */
	public function subscribe(callable $subscriber) : void
	{
		$this->subscribers[] = $subscriber;
	}

	/**
	 * Запуск события
	 */
	public function run(array $params = []) : bool
	{
		if (count($this->subscribers) > 0)
		{
			foreach ($this->subscribers as $subscriber)
			{
				if (false === call_user_func_array($subscriber, $params))
				{
					return false;
				}
			}
		}

		return true;
	}
}
