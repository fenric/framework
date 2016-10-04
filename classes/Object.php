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
 * Object
 */
abstract class Object
{

	/**
	 * Регистрация слушателя события класса
	 *
	 * @param   string   $eventname
	 * @param   call     $listener
	 *
	 * @access  public
	 * @return  mixed
	 */
	final public function registerEventListener($eventname, callable $listener)
	{
		return fenric()->registerEventListener(get_class($this), $eventname, $listener);
	}

	/**
	 * Разрегистрация слушателей события класса
	 *
	 * @param   string   $eventname
	 *
	 * @access  public
	 * @return  mixed
	 */
	final public function unregisterEventListeners($eventname)
	{
		return fenric()->unregisterEventListeners(get_class($this), $eventname);
	}

	/**
	 * Вызов слушателей события класса
	 *
	 * @param   string   $eventname
	 * @param   mixed    $params
	 *
	 * @access  public
	 * @return  mixed
	 */
	final public function dispatchEvent($eventname, $params = null)
	{
		return fenric()->dispatchEvent(get_class($this), $eventname, $params);
	}

	/**
	 * Замыкающееся связывание с экземпляром класса через анонимную функцию
	 *
	 * @param   call    $closure
	 * @param   array   $params
	 *
	 * @access  public
	 * @return  object
	 */
	final public function bindOf(\Closure $closure, array $params = [])
	{
		$fn = $closure->bindTo($this, get_class($this));

		call_user_func_array($fn, $params);

		return $this;
	}
}
