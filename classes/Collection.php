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
use Traversable;
use ArrayIterator;
use IteratorAggregate;

/**
 * Collection
 */
class Collection implements IteratorAggregate
{

	/**
	 * Items of the collection
	 */
	protected $items = [];

	/**
	 * Constructor of the class
	 */
	public function __construct(iterable $items = [])
	{
		foreach ($items as $key => $value)
		{
			$this->set($key, $value);
		}
	}

	/**
	 * Gets an external iterator
	 */
	public function getIterator()
	{
		return new ArrayIterator($this->items);
	}

	/**
	 * Adds an item to the collection
	 */
	public function add($value) : self
	{
		$this->items[] = $value;

		return $this;
	}

	/**
	 * Sets an item to the collection
	 */
	public function set($key, $value) : self
	{
		$this->items[$key] = $value;

		return $this;
	}

	/**
	 * Gets an item from the collection
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
	 * Removes an item from the collection
	 */
	public function remove($key, $default = null)
	{
		if (array_key_exists($key, $this->items))
		{
			$value = $this->items[$key];

			unset($this->items[$key]);

			return $value;
		}

		return $default;
	}

	/**
	 * Checks if an item exists in the collection
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
	 * Checks if an item contains in the collection
	 */
	public function contains($value) : bool
	{
		if (in_array($value, $this->items))
		{
			return true;
		}

		return false;
	}

	/**
	 * Gets size of the collection
	 */
	public function count() : int
	{
		return count($this->items);
	}

	/**
	 * Gets items of the collection
	 */
	public function all() : array
	{
		return $this->items;
	}

	/**
	 * Clears the collection
	 */
	public function clear() : self
	{
		$this->items = [];

		return $this;
	}

	/**
	 * Sorts the collection
	 */
	public function sort(Closure $callback) : self
	{
		uasort($this->items, $callback);

		return $this;
	}

	/**
	 * Transforms the collection
	 *
	 * The callback function must return an array.
	 */
	public function transform(Closure $callback) : self
	{
		$items = [];

		$iteration = 0;

		foreach ($this->items as $key => $value)
		{
			$items += $callback($key, $value, $iteration++);
		}

		$this->items = $items;

		return $this;
	}

	/**
	 * Updates the collection
	 *
	 * Do not replace existing collection items.
	 */
	public function update(array $items) : self
	{
		$this->items = array_merge($items, $this->items);

		return $this;
	}

	/**
	 * Upgrades the collection
	 *
	 * Replaces existing collection items.
	 */
	public function upgrade(array $items) : self
	{
		$this->items = array_merge($this->items, $items);

		return $this;
	}

	/**
	 * Filters the collection
	 */
	public function filter(...$options) : self
	{
		$this->items = array_filter($this->items, ...$options);

		return $this;
	}

	/**
	 * Unifies the collection
	 */
	public function unique(...$options) : self
	{
		$this->items = array_unique($this->items, ...$options);

		return $this;
	}

	/**
	 * Converts the collection to Array
	 */
	public function toArray() : array
	{
		return array_map(function($item)
		{
			if ($item instanceof Collection)
			{
				return $item->toArray();
			}

			if ($item instanceof Traversable)
			{
				$item = new Collection($item);

				return $item->toArray();
			}

			return $item;

		}, $this->items);
	}

	/**
	 * Converts the collection to JSON
	 */
	public function toJson(...$options) : string
	{
		return json_encode($this->toArray(), ...$options);
	}

	/**
	 * Converts the collection to QueryString
	 */
	public function toQueryString(...$options) : string
	{
		return http_build_query($this->toArray(), ...$options);
	}

	/**
	 * Clones the collection
	 */
	public function clone() : Collection
	{
		return clone $this;
	}

	/**
	 * Copies the collection
	 *
	 * Consumes more memory than cloning.
	 */
	public function copy() : Collection
	{
		return new static($this);
	}
}
