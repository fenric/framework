<?php
/**
 * It is free open-source software released under the MIT License.
 *
 * @author       Anatoly Nekhay <a.fenric@gmail.com>
 * @copyright    Copyright (c) 2013-2016 by Fenric Laboratory
 * @license      http://fenric.ru/license/
 * @link         http://fenric.ru/
 */

namespace Fenric;

/**
 * Import classes
 */
use Closure;

/**
 * Object
 */
abstract class Object
{

	/**
	 * События класса
	 *
	 * @var     array
	 * @access  protected
	 */
	protected $events = [];

	/**
	 * Регистрация слушателя события класса
	 *
	 * @param   string   $name
	 * @param   call     $listener
	 *
	 * @access  public
	 * @return  void
	 */
	final public function on($name, callable $listener)
	{
		$this->events[$name]['listeners'][] = $listener;
	}

	/**
	 * Разрегистрация слушателей события класса
	 *
	 * @param   string   $name
	 *
	 * @access  public
	 * @return  void
	 */
	final public function off($name)
	{
		$this->events[$name]['listeners'] = null;
	}

	/**
	 * Вызов слушателей события класса
	 *
	 * @param   string   $name
	 * @param   mixed    $arguments
	 *
	 * @access  public
	 * @return  bool
	 */
	final public function trigger($name, $arguments = null)
	{
		$arguments = (array) $arguments;

		if (isset($this->events[$name]['listeners']))
		{
			foreach ($this->events[$name]['listeners'] as $listener)
			{
				if (false === call_user_func_array($listener, $arguments))
				{
					return false;
				}
			}
		}

		return true;
	}

	/**
	 * Замыкающееся связывание с экземпляром класса через анонимную функцию
	 *
	 * @param   call    $closure
	 * @param   array   $arguments
	 *
	 * @access  public
	 * @return  object
	 */
	final public function bind(Closure $closure, array $arguments = [])
	{
		$fn = $closure->bindTo($this, get_class($this));

		call_user_func_array($fn, $arguments);

		return $this;
	}
}
