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
 * Collection
 */
class Collection
{

	/**
	 * Элементы коллекции
	 */
	protected $items;

	/**
	 * Конструктор класса
	 */
	public function __construct(array $items = [])
	{
		$this->items = $items;
	}

	/**
	 * Установка элемента коллекции
	 */
	public function set($key, $value) : self
	{
		$this->items[$key] = $value;

		return $this;
	}

	/**
	 * Получение элемента коллекции
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
	 * Существование элемента коллекции
	 */
	public function exists($key) : bool
	{
		if (array_key_exists($key, $this->items))
		{
			return true;
		}

		return false;
	}

	/**
	 * Очистка коллекции
	 */
	public function clear() : self
	{
		$this->items = [];

		return $this;
	}

	/**
	 * Обновление коллекции
	 */
	public function update(array $items) : self
	{
		$this->items += $items;

		return $this;
	}

	/**
	 * Получение всех элементов коллекции
	 */
	public function all() : array
	{
		return $this->items;
	}

	/**
	 * Получение количества элементов коллекции
	 */
	public function count() : int
	{
		return count($this->items);
	}
}
