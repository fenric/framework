<?php
/**
 * (c) Fenric Lab, 2010-2016
 *
 * @product      Fenric Framework
 * @author       Anatoly Nekhay E.
 * @email        support@fenric.ru
 * @site         http://fenric.ru/
 */

namespace Fenric;

/**
 * Import classes
 */
use Countable;

/**
 * Collection
 */
class Collection extends Object implements Countable
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
	 * @param   array    $items
	 *
	 * @access  public
	 * @return  void
	 */
	public function __construct(array $items = [])
	{
		$this->items = $items;
	}

	/**
	 * Установка элемента коллекции
	 *
	 * @param   mixed    $key
	 * @param   mixed    $value
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
	 * @param   mixed    $key
	 * @param   mixed    $default
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
	 * @param   mixed    $key
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
	 * @param   mixed    $key
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
	 * @param   array    $items
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
