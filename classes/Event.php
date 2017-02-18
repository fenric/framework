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
	 *
	 * @var     string
	 * @access  protected
	 */
	protected $name;

	/**
	 * Подписчики события
	 *
	 * @var     array
	 * @access  protected
	 */
	protected $subscribers = [];

	/**
	 * Инкрементальный идентификатор подписчиков события
	 *
	 * @var     int
	 * @access  protected
	 */
	protected $increment = 0;

	/**
	 * Конструктор класса
	 *
	 * @param   string   $name
	 *
	 * @access  public
	 * @return  void
	 */
	public function __construct($name)
	{
		$this->name = $name;
	}

	/**
	 * Получение имени события
	 *
	 * @access  public
	 * @return  string
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * Подписка на событие
	 *
	 * @param   callable   $subscriber
	 * @param   int        $priority
	 *
	 * @access  public
	 * @return  int
	 */
	public function subscribe(callable $subscriber, $priority = 0)
	{
		$this->subscribers[$this->increment] = [$subscriber, $priority];

		return $this->increment++;
	}

	/**
	 * Отписка от события
	 *
	 * @param   int   $subscriberId
	 *
	 * @access  public
	 * @return  bool
	 */
	public function unsubscribe($subscriberId)
	{
		if (isset($this->subscribers[$subscriberId]))
		{
			unset($this->subscribers[$subscriberId]);

			return true;
		}

		return false;
	}

	/**
	 * Запуск события
	 *
	 * @param   array   $params
	 *
	 * @access  public
	 * @return  bool
	 */
	public function run(array $params = [])
	{
		if (count($this->subscribers) > 0)
		{
			usort($this->subscribers, function($a, $b)
			{
				if ($a[1] > $b[1]) return 1;

				else if ($a[1] < $b[1]) return -1;

				else return 0;
			});

			foreach ($this->subscribers as $subscription)
			{
				list($subscriber, $priority) = $subscription;

				if (false === call_user_func_array($subscriber, $params))
				{
					return false;
				}
			}
		}

		return true;
	}
}
