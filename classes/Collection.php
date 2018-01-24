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
	 * Добавление элемента коллекции
	 */
	public function add($value) : self
	{
		$this->items[] = $value;

		return $this;
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
			$value = $this->items[$key];

			unset($this->items[$key]);

			return $value;
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
	 * Дополнение коллекции
	 */
	public function update(array $items) : self
	{
		$this->items = array_merge($items, $this->items);

		return $this;
	}

	/**
	 * Обновление коллекции
	 */
	public function upgrade(array $items) : self
	{
		$this->items = array_merge($this->items, $items);

		return $this;
	}

	/**
	 * Фильтрация коллекции
	 */
	public function filter(...$options) : self
	{
		$this->items = array_filter($this->items, ...$options);

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
	 * Получение ключей элементов коллекции
	 */
	public function keys() : array
	{
		return array_keys($this->items);
	}

	/**
	 * Получение значений элементов коллекции
	 */
	public function values() : array
	{
		return array_values($this->items);
	}

	/**
	 * Получение количества элементов коллекции
	 */
	public function count() : int
	{
		return count($this->items);
	}

	/**
	 * Получение коллекции в виде массива
	 */
	public function toArray() : array
	{
		return $this->items;
	}

	/**
	 * Получение коллекции в виде JSON данных
	 */
	public function toJson(...$options)
	{
		return json_encode($this->items, ...$options);
	}

	/**
	 * Получение коллекции в виде QueryString данных
	 */
	public function toQueryString(...$options)
	{
		return http_build_query($this->items, ...$options);
	}

	/**
	 * Клонирование коллекции
	 */
	public function clone() : Collection
	{
		return clone $this;
	}
}
