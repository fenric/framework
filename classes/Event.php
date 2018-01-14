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
use Closure;

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
	public function subscribe(Closure $subscriber) : void
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
				if ($subscriber(...$params) === false)
				{
					return false;
				}
			}
		}

		return true;
	}
}
