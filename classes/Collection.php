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
 * Collection
 */
class Collection
{

	/**
	 * Элементы коллекции
	 *
	 * @var     array
	 * @access  protected
	 */
	protected $items = [];

	/**
	 * Конструктор класса
	 *
	 * @param   array   $items
	 *
	 * @access  public
	 * @return  void
	 */
	public function __construct(array $items = [])
	{
		$this->items = $items;
	}

	/**
	 * Добавление элемента коллекции
	 *
	 * @param   mixed   $item
	 *
	 * @access  public
	 * @return  object
	 */
	public function add($item)
	{
		$this->items[] = $item;

		return $this;
	}

	/**
	 * Установка элемента коллекции
	 *
	 * @param   mixed   $key
	 * @param   mixed   $value
	 *
	 * @access  public
	 * @return  object
	 */
	public function set($key, $value)
	{
		$this->items[$key] = $value;

		return $this;
	}

	/**
	 * Получение элемента коллекции
	 *
	 * @param   mixed   $key
	 * @param   mixed   $default
	 *
	 * @access  public
	 * @return  mixed
	 */
	public function get($key, $default = null)
	{
		if (array_key_exists($key, $this->items))
		{
			return $this->items[$key];
		}

		return $default;
	}

	/**
	 * Извлечение элемента коллекции
	 *
	 * @param   mixed   $key
	 *
	 * @access  public
	 * @return  mixed
	 */
	public function remove($key)
	{
		if (array_key_exists($key, $this->items))
		{
			$removed = $this->items[$key];

			unset($this->items[$key]);

			return $removed;
		}
	}

	/**
	 * Проверка существования элемента коллекции
	 *
	 * @param   mixed   $key
	 *
	 * @access  public
	 * @return  bool
	 */
	public function has($key)
	{
		if (array_key_exists($key, $this->items))
		{
			return true;
		}

		return false;
	}

	/**
	 * Очистка коллекции
	 *
	 * @access  public
	 * @return  object
	 */
	public function clear()
	{
		$this->items = [];

		return $this;
	}

	/**
	 * Обновление коллекции
	 *
	 * @param   array   $items
	 *
	 * @access  public
	 * @return  object
	 */
	public function update(array $items)
	{
		$this->items += $items;

		return $this;
	}

	/**
	 * Получение всех элементов коллекции
	 *
	 * @access  public
	 * @return  array
	 */
	public function all()
	{
		return $this->items;
	}

	/**
	 * Получение количества элементов коллекции
	 *
	 * @access  public
	 * @return  int
	 */
	public function count()
	{
		return count($this->items);
	}
}
