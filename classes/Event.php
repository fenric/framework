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
	 * Name of the event
	 */
	protected $name;

	/**
	 * Subscribers of the event
	 */
	protected $subscribers = [];

	/**
	 * Constructor of the class
	 */
	public function __construct(string $name)
	{
		$this->name = $name;

		if (fenric('config::events')->exists($name))
		{
			foreach (fenric('config::events')->get($name) as $subscriber)
			{
				$this->subscribe(new $subscriber);
			}
		}
	}

	/**
	 * Gets name of the event
	 */
	public function getName() : string
	{
		return $this->name;
	}

	/**
	 * Subscribes on the event
	 */
	public function subscribe(callable $subscriber) : void
	{
		$this->subscribers[] = $subscriber;
	}

	/**
	 * Runs the event
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
