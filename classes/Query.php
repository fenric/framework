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
 * Import classes
 */
use Closure, DateTime, PDO, PDOStatement;

/**
 * Query
 */
class Query
{

	/**
	 * Экземпляр PDO
	 *
	 * @var     object
	 * @access  protected
	 */
	protected $pdo;

	/**
	 * Текущий режим
	 *
	 * @var     string
	 * @access  protected
	 */
	protected $mode;

	/**
	 * Текущий оператор
	 *
	 * @var     string
	 * @access  protected
	 */
	protected $operator;

	/**
	 * Хранилище конструктора
	 *
	 * @var     array
	 * @access  protected
	 */
	protected $storage =
	[
		/**
		 * Данные оператора `DISTINCT`
		 *
		 * @param bool
		 */
		'distinct' => false,

		/**
		 * Данные оператора `SELECT`
		 *
		 * @param array
		 */
		'select' => [],

		/**
		 * Данные оператора `FROM`
		 *
		 * @param array
		 */
		'from' => [],

		/**
		 * Данные оператора `JOIN`
		 *
		 * @param array
		 */
		'join' => [],

		/**
		 * Дополнительные операторы для оператора `JOIN`
		 *
		 * @param array
		 */
		'join operators' => [],

		/**
		 * Данные оператора `WHERE`
		 *
		 * @param array
		 */
		'where' => ['conditions' => [], 'parentheses' => ['open' => 0, 'close' => 0]],

		/**
		 * Данные оператора `GROUP BY`
		 *
		 * @param array
		 */
		'group' => [],

		/**
		 * Данные оператора `HAVING`
		 *
		 * @param array
		 */
		'having' => ['conditions' => [], 'parentheses' => ['open' => 0, 'close' => 0]],

		/**
		 * Данные оператора `ORDER BY`
		 *
		 * @param array
		 */
		'order' => [],

		/**
		 * Данные оператора `LIMIT`
		 *
		 * @param int
		 */
		'limit' => null,

		/**
		 * Данные оператора `OFFSET`
		 *
		 * @param int
		 */
		'offset' => null,

		/**
		 * Логическая конструкция
		 *
		 * @param string
		 */
		'logical' => 'AND',

		/**
		 * Значения для операторов `INSERT` или `UPDATE`
		 *
		 * @param array
		 */
		'values' => [],

		/**
		 * Значения в виде параметров
		 *
		 * @param array
		 */
		'params' => [],
	];

	/**
	 * Результат выполнения SQL запроса
	 *
	 * @var     object
	 * @access  protected
	 */
	protected $statement;

	/**
	 * Время жизни результирующего набора в кэше
	 *
	 * @var     int
	 * @access  protected
	 */
	protected $cacheLifetime = 0;

	/**
	 * Конструктор класса
	 *
	 * @param   object   $pdo
	 *
	 * @access  public
	 * @return  void
	 */
	public function __construct(PDO $pdo)
	{
		$this->pdo = $pdo;
	}

	/**
	 * Получение объекта PDO
	 *
	 * @access  public
	 * @return  object
	 */
	public function getPdo()
	{
		return $this->pdo;
	}

	/**
	 * Включение кэширования результирующего набора
	 *
	 * @param   int   $lifetime
	 *
	 * @access  public
	 * @return  object
	 */
	public function cache($lifetime = 31536000)
	{
		$this->cacheLifetime = $lifetime;

		return $this;
	}

	/**
	 * Замыкающееся связывание с экземпляром класса через анонимную функцию
	 *
	 * @param   Closure   $callback
	 *
	 * @access  public
	 * @return  object
	 */
	public function bind(Closure $callback)
	{
		$callback($this);

		return $this;
	}

	/**
	 * Отложенное выполнение SQL запроса
	 *
	 * @access  public
	 * @return  void
	 */
	public function shutdown()
	{
		register_shutdown_function(function()
		{
			$this->prepareSql()->executeSql();
		});
	}

	/**
	 * Выполнение SQL запроса и получение количества затронутых строк
	 *
	 * @access  public
	 * @return  int
	 */
	public function run()
	{
		$this->prepareSql()->executeSql();

		return $this->statement->rowCount();
	}

	/**
	 * Выполнение SQL запроса и получение результирующего набора в виде ассоциативного массива
	 *
	 * @access  public
	 * @return  array
	 */
	public function toArray()
	{
		return $this->readCache('array', function()
		{
			$this->prepareSql()->executeSql();

			return $this->statement->fetchAll(PDO::FETCH_ASSOC);
		});
	}

	/**
	 * Выполнение SQL запроса и получение результирующего набора в виде массива с данными в виде объектов
	 *
	 * @param   string   $class
	 *
	 * @access  public
	 * @return  array
	 */
	public function toObject($class = 'stdClass')
	{
		return $this->readCache($class, function() use($class)
		{
			$this->prepareSql()->executeSql();

			return $this->statement->fetchAll(PDO::FETCH_CLASS, $class);
		});
	}

	/**
	 * Выполнение SQL запроса и получение результирующего набора в виде JSON данных
	 *
	 * @param   int   $options
	 *
	 * @access  public
	 * @return  string
	 */
	public function toJson($options = 0)
	{
		return json_encode($this->toArray(), $options);
	}

	/**
	 * Выполнение SQL запроса и чтение всего результирующего набора
	 *
	 * @access  public
	 * @return  mixed
	 */
	public function readAll()
	{
		if ($this->isSelect())
		{
			return $this->readCache('all', function()
			{
				if ($this->prepareSql()->executeSql())
				{
					return $this->statement->fetchAll(PDO::FETCH_OBJ);
				}
			});
		}
	}

	/**
	 * Выполнение SQL запроса и чтение только первой колонки из результирующего набора
	 *
	 * @access  public
	 * @return  mixed
	 */
	public function readCol()
	{
		if ($this->isSelect())
		{
			return $this->readCache('col', function()
			{
				if ($this->prepareSql()->executeSql())
				{
					return $this->statement->fetchAll(PDO::FETCH_OBJ | PDO::FETCH_COLUMN);
				}
			});
		}
	}

	/**
	 * Выполнение SQL запроса и чтение только первого ряда из результирующего набора
	 *
	 * @access  public
	 * @return  mixed
	 */
	public function readRow()
	{
		$this->limit(1); // автоматическое лимитирование результирующего набора

		if ($this->isSelect())
		{
			return $this->readCache('row', function()
			{
				if ($this->prepareSql()->executeSql())
				{
					return $this->statement->fetch(PDO::FETCH_OBJ);
				}
			});
		}
	}

	/**
	 * Выполнение SQL запроса и чтение только первой колонки первого ряда из результирующего набора
	 *
	 * @access  public
	 * @return  mixed
	 */
	public function readOne()
	{
		$this->limit(1); // автоматическое лимитирование результирующего набора

		if ($this->isSelect())
		{
			return $this->readCache('one', function()
			{
				if ($this->prepareSql()->executeSql())
				{
					return $this->statement->fetch(PDO::FETCH_COLUMN);
				}
			});
		}
	}

	/**
	 * Выполнение SQL запроса и чтение только первых двух колонок из результирующего набора
	 *
	 * @access  public
	 * @return  mixed
	 */
	public function readPair()
	{
		if ($this->isSelect())
		{
			return $this->readCache('pair', function()
			{
				if ($this->prepareSql()->executeSql())
				{
					return $this->statement->fetchAll(PDO::FETCH_KEY_PAIR);
				}
			});
		}
	}

	/**
	 * Это `SELECT` запрос
	 *
	 * @access  public
	 * @return  bool
	 */
	public function isSelect()
	{
		return strcmp($this->mode, 'select') === 0;
	}

	/**
	 * Это `INSERT` запрос
	 *
	 * @access  public
	 * @return  bool
	 */
	public function isInsert()
	{
		return strcmp($this->mode, 'insert') === 0;
	}

	/**
	 * Это `UPDATE` запрос
	 *
	 * @access  public
	 * @return  bool
	 */
	public function isUpdate()
	{
		return strcmp($this->mode, 'update') === 0;
	}

	/**
	 * Это `UPSERT` запрос
	 *
	 * @access  public
	 * @return  bool
	 */
	public function isUpsert()
	{
		return strcmp($this->mode, 'upsert') === 0;
	}

	/**
	 * Это `DELETE` запрос
	 *
	 * @access  public
	 * @return  bool
	 */
	public function isDelete()
	{
		return strcmp($this->mode, 'delete') === 0;
	}

	/**
	 * Оператор `DISTINCT`
	 *
	 * @param   bool   $state
	 *
	 * @access  public
	 * @return  object
	 */
	public function distinct($state = true)
	{
		$this->storage['distinct'] = !! $state;

		return $this;
	}

	/**
	 * Оператор `SELECT`
	 *
	 * @param   array   $columns [, $...]
	 *
	 * @access  public
	 * @return  object
	 */
	public function select($columns = ['*'])
	{
		$this->mode = $this->operator = 'select';

		$columns = $this->prepareColumn(is_array($columns) ? $columns : func_get_args());

		$this->storage['select'] = array_merge($this->storage['select'], $columns);

		return $this;
	}

	/**
	 * Функция COUNT() в операторе `SELECT`
	 *
	 * @param   mixed   $column
	 *
	 * @access  public
	 * @return  object
	 */
	public function count($column = '*')
	{
		return $this->select(function() use($column)
		{
			return sprintf('COUNT(%s)', $this->prepareColumn($column));
		});
	}

	/**
	 * Функция AVG() в операторе `SELECT`
	 *
	 * @param   mixed   $column
	 *
	 * @access  public
	 * @return  object
	 */
	public function avg($column)
	{
		return $this->select(function() use($column)
		{
			return sprintf('AVG(%s)', $this->prepareColumn($column));
		});
	}

	/**
	 * Функция MIN() в операторе `SELECT`
	 *
	 * @param   mixed   $column
	 *
	 * @access  public
	 * @return  object
	 */
	public function min($column)
	{
		return $this->select(function() use($column)
		{
			return sprintf('MIN(%s)', $this->prepareColumn($column));
		});
	}

	/**
	 * Функция MAX() в операторе `SELECT`
	 *
	 * @param   mixed   $column
	 *
	 * @access  public
	 * @return  object
	 */
	public function max($column)
	{
		return $this->select(function() use($column)
		{
			return sprintf('MAX(%s)', $this->prepareColumn($column));
		});
	}

	/**
	 * Функция SUM() в операторе `SELECT`
	 *
	 * @param   mixed   $column
	 *
	 * @access  public
	 * @return  object
	 */
	public function sum($column)
	{
		return $this->select(function() use($column)
		{
			return sprintf('SUM(%s)', $this->prepareColumn($column));
		});
	}

	/**
	 * Оператор `INSERT`
	 *
	 * @param   string   $table
	 * @param   array    $values
	 *
	 * @access  public
	 * @return  object
	 */
	public function insert($table, array $values = [])
	{
		$this->mode = $this->operator = 'insert';

		$this->storage['values']['insert'] = $this->preparePairs($values);

		$this->from($table);

		return $this;
	}

	/**
	 * Оператор `UPDATE`
	 *
	 * @param   string   $table
	 * @param   array    $values
	 *
	 * @access  public
	 * @return  object
	 */
	public function update($table, array $values = [])
	{
		$this->mode = $this->operator = 'update';

		$this->storage['values']['update'] = $this->preparePairs($values);

		$this->from($table);

		return $this;
	}

	/**
	 * Оператор `UPSERT`
	 *
	 * @param   string   $table
	 * @param   array    $inserts
	 * @param   array    $updates
	 *
	 * @access  public
	 * @return  object
	 */
	public function upsert($table, array $inserts = [], array $updates = [])
	{
		$this->mode = $this->operator = 'upsert';

		$this->storage['values']['insert'] = $this->preparePairs($inserts);
		$this->storage['values']['update'] = $this->preparePairs($updates ?: $inserts);

		$this->from($table);

		return $this;
	}

	/**
	 * Оператор `DELETE`
	 *
	 * @access  public
	 * @return  object
	 */
	public function delete()
	{
		$this->mode = $this->operator = 'delete';

		return $this;
	}

	/**
	 * Оператор `FROM`
	 *
	 * @param   array   $tables [, $...]
	 *
	 * @access  public
	 * @return  object
	 */
	public function from($tables)
	{
		$this->operator = 'from';

		$tables = $this->prepareTable(is_array($tables) ? $tables : func_get_args());

		$this->storage['from'] = array_merge($this->storage['from'], $tables);

		return $this;
	}

	/**
	 * Оператор `JOIN`
	 *
	 * @param   string   $table
	 *
	 * @access  public
	 * @return  object
	 */
	public function join($table)
	{
		$this->operator = 'join';

		$this->storage['join'][] = ['table' => $this->prepareTable($table), 'conditions' => [], 'parentheses' => ['open' => 0, 'close' => 0], 'operators' => $this->storage['join operators']];

		$this->storage['join operators'] = [];

		return $this;
	}

	/**
	 * Оператор `WHERE`
	 *
	 * @param   string   $column
	 * @param   string   $compare
	 * @param   string   $value
	 *
	 * @access  public
	 * @return  object
	 */
	public function where($column, $compare, $value)
	{
		$this->operator = 'where';

		if (strpos($this->storage['logical'], '(') !== false)
		{
			$this->storage['where']['parentheses']['open'] += substr_count($this->storage['logical'], '(');
		}

		if (strpos($this->storage['logical'], ')') !== false)
		{
			$this->storage['where']['parentheses']['close'] += substr_count($this->storage['logical'], ')');
		}

		$this->storage['where']['conditions'][] = [
			$this->prepareColumn($column),
			$this->prepareComparison($compare),
			$this->prepareValue($value),
			$this->storage['logical'],
		];

		$this->storage['logical'] = 'AND';

		return $this;
	}

	/**
	 * Оператор `GROUP BY`
	 *
	 * @param   array   $columns [, $...]
	 *
	 * @access  public
	 * @return  object
	 */
	public function group($columns)
	{
		$this->operator = 'group';

		$columns = $this->prepareColumn(is_array($columns) ? $columns : func_get_args());

		$this->storage['group'] = array_merge($this->storage['group'], $columns);

		return $this;
	}

	/**
	 * Оператор `HAVING`
	 *
	 * @param   string   $column
	 * @param   string   $compare
	 * @param   string   $value
	 *
	 * @access  public
	 * @return  object
	 */
	public function having($column, $compare, $value)
	{
		$this->operator = 'having';

		if (strpos($this->storage['logical'], '(') !== false)
		{
			$this->storage['having']['parentheses']['open'] += substr_count($this->storage['logical'], '(');
		}

		if (strpos($this->storage['logical'], ')') !== false)
		{
			$this->storage['having']['parentheses']['close'] += substr_count($this->storage['logical'], ')');
		}

		$this->storage['having']['conditions'][] = [
			$this->prepareColumn($column),
			$this->prepareComparison($compare),
			$this->prepareValue($value),
			$this->storage['logical'],
		];

		$this->storage['logical'] = 'AND';

		return $this;
	}

	/**
	 * Оператор `ORDER BY`
	 *
	 * @param   array   $columns [, $...]
	 *
	 * @access  public
	 * @return  object
	 */
	public function order($columns)
	{
		$this->operator = 'order';

		$columns = $this->prepareColumn(is_array($columns) ? $columns : func_get_args());

		$this->storage['order'] = array_merge($this->storage['order'], $columns);

		return $this;
	}

	/**
	 * Функция RAND() для оператора `ORDER BY`
	 *
	 * @access  public
	 * @return  object
	 */
	public function rand()
	{
		return $this->order(function()
		{
			return 'RAND()';
		});
	}

	/**
	 * Оператор `LIMIT`
	 *
	 * @param   int   $limit
	 *
	 * @access  public
	 * @return  object
	 */
	public function limit($limit)
	{
		if (is_numeric($limit) && floor($limit) > 0)
		{
			$this->storage['limit'] = (int) $limit;
		}

		return $this;
	}

	/**
	 * Оператор `OFFSET`
	 *
	 * @param   int   $offset
	 *
	 * @access  public
	 * @return  object
	 */
	public function offset($offset)
	{
		if (is_numeric($offset) && floor($offset) >= 0)
		{
			$this->storage['offset'] = (int) $offset;
		}

		return $this;
	}

	/**
	 * Оператор `ON`
	 *
	 * @param   string   $column1
	 * @param   string   $compare
	 * @param   string   $column2
	 *
	 * @access  public
	 * @return  object
	 */
	public function on($column1, $compare, $column2)
	{
		if (strcmp($this->operator, 'join') === 0)
		{
			end($this->storage['join']);

			$key = key($this->storage['join']);

			reset($this->storage['join']);

			if (strpos($this->storage['logical'], '(') !== false)
			{
				$this->storage['join'][$key]['parentheses']['open'] += substr_count($this->storage['logical'], '(');
			}

			if (strpos($this->storage['logical'], ')') !== false)
			{
				$this->storage['join'][$key]['parentheses']['close'] += substr_count($this->storage['logical'], ')');
			}

			$this->storage['join'][$key]['conditions'][] = [
				$this->prepareColumn($column1),
				$this->prepareComparison($compare),
				$this->prepareColumn($column2),
				$this->storage['logical'],
			];

			$this->storage['logical'] = 'AND';
		}

		return $this;
	}

	/**
	 * Оператор `AS`
	 *
	 * @param   string   $alias
	 *
	 * @access  public
	 * @return  object
	 */
	public function alias($alias)
	{
		if (in_array($this->operator, ['select', 'from', 'join']))
		{
			end($this->storage[$this->operator]);

			$key = key($this->storage[$this->operator]);

			reset($this->storage[$this->operator]);

			$supplement = sprintf(' AS %s', $this->prepareColumn($alias));

			switch ($this->operator)
			{
				case 'select' :
				case 'from' :
					$this->storage[$this->operator][$key] .= $supplement;
					break;

				case 'join' :
					$this->storage[$this->operator][$key]['table'] .= $supplement;
					break;
			}
		}

		return $this;
	}

	/**
	 * Оператор `ASC`
	 *
	 * @access  public
	 * @return  object
	 */
	public function asc()
	{
		if (in_array($this->operator, ['order', 'group']))
		{
			end($this->storage[$this->operator]);

			$this->storage[$this->operator][key($this->storage[$this->operator])] .= ' ASC';

			reset($this->storage[$this->operator]);
		}

		return $this;
	}

	/**
	 * Оператор `DESC`
	 *
	 * @access  public
	 * @return  object
	 */
	public function desc()
	{
		if (in_array($this->operator, ['order', 'group']))
		{
			end($this->storage[$this->operator]);

			$this->storage[$this->operator][key($this->storage[$this->operator])] .= ' DESC';

			reset($this->storage[$this->operator]);
		}

		return $this;
	}

	/**
	 * Дополнительный оператор `NATURAL` для оператора `JOIN`
	 *
	 * @access  public
	 * @return  object
	 */
	public function natural()
	{
		$this->storage['join operators'][] = 'NATURAL';

		return $this;
	}

	/**
	 * Дополнительный оператор `LEFT` для оператора `JOIN`
	 *
	 * @access  public
	 * @return  object
	 */
	public function left()
	{
		$this->storage['join operators'][] = 'LEFT';

		return $this;
	}

	/**
	 * Дополнительный оператор `RIGHT` для оператора `JOIN`
	 *
	 * @access  public
	 * @return  object
	 */
	public function right()
	{
		$this->storage['join operators'][] = 'RIGHT';

		return $this;
	}

	/**
	 * Дополнительный оператор `INNER` для оператора `JOIN`
	 *
	 * @access  public
	 * @return  object
	 */
	public function inner()
	{
		$this->storage['join operators'][] = 'INNER';

		return $this;
	}

	/**
	 * Дополнительный оператор `CROSS` для оператора `JOIN`
	 *
	 * @access  public
	 * @return  object
	 */
	public function cross()
	{
		$this->storage['join operators'][] = 'CROSS';

		return $this;
	}

	/**
	 * Дополнительный оператор `OUTER` для оператора `JOIN`
	 *
	 * @access  public
	 * @return  object
	 */
	public function outer()
	{
		$this->storage['join operators'][] = 'OUTER';

		return $this;
	}

	/**
	 * Логическая конструкция с использованием оператора `AND`
	 *
	 * @access  public
	 * @return  object
	 */
	public function and_()
	{
		$this->storage['logical'] = 'AND';

		return $this;
	}

	public function and_open($open = 1)
	{
		$this->storage['logical'] = 'AND ' . str_repeat('(', $open);

		return $this;
	}

	public function close_and_open($close = 1, $open = 1)
	{
		$this->storage['logical'] = str_repeat(')', $close) . ' AND ' . str_repeat('(', $open);

		return $this;
	}

	public function close_and_($close = 1)
	{
		$this->storage['logical'] = str_repeat(')', $close) . ' AND';

		return $this;
	}

	/**
	 * Логическая конструкция с использованием оператора `OR`
	 *
	 * @access  public
	 * @return  object
	 */
	public function or_()
	{
		$this->storage['logical'] = 'OR';

		return $this;
	}

	public function or_open($open = 1)
	{
		$this->storage['logical'] = 'OR ' . str_repeat('(', $open);

		return $this;
	}

	public function close_or_open($close = 1, $open = 1)
	{
		$this->storage['logical'] = str_repeat(')', $close) . ' OR ' . str_repeat('(', $open);

		return $this;
	}

	public function close_or_($close = 1)
	{
		$this->storage['logical'] = str_repeat(')', $close) . ' OR';

		return $this;
	}

	/**
	 * Сборка оператора `SELECT`
	 *
	 * @access  public
	 * @return  string
	 */
	public function buildSelect()
	{
		if (! empty($this->storage['select']))
		{
			return sprintf('SELECT' . ($this->storage['distinct'] ? ' DISTINCT' : '') . ' %s', implode(', ', $this->storage['select']));
		}
	}

	/**
	 * Сборка оператора `INSERT`
	 *
	 * @access  public
	 * @return  string
	 */
	public function buildInsert()
	{
		if (! empty($this->storage['from']))
		{
			if (! empty($this->storage['values']['insert']))
			{
				$t = $this->storage['from'];

				$keys = array_keys($this->storage['values']['insert']);

				$inserts = array_values($this->storage['values']['insert']);

				return sprintf('INSERT INTO %s (%s) VALUES (%s)', implode($t), implode(', ', $keys), implode(', ', $inserts));
			}
		}
	}

	/**
	 * Сборка оператора `UPDATE`
	 *
	 * @access  public
	 * @return  string
	 */
	public function buildUpdate()
	{
		if (! empty($this->storage['from']))
		{
			if (! empty($this->storage['values']['update']))
			{
				$t = $this->storage['from'];

				foreach ($this->storage['values']['update'] as $key => $value)
				{
					$assignments[] = sprintf('%s = %s', $key, $value);
				}

				return sprintf('UPDATE %s SET %s', implode($t), implode(', ', $assignments));
			}
		}
	}

	/**
	 * Сборка оператора `UPSERT`
	 *
	 * @access  public
	 * @return  string
	 */
	public function buildUpsert()
	{
		if (! empty($this->storage['from']))
		{
			if (! empty($this->storage['values']['insert']))
			{
				if (! empty($this->storage['values']['update']))
				{
					$t = $this->storage['from'];

					$keys = array_keys($this->storage['values']['insert']);

					$inserts = array_values($this->storage['values']['insert']);

					foreach ($this->storage['values']['update'] as $key => $value)
					{
						$assignments[] = sprintf('%s = %s', $key, $value);
					}

					return sprintf('INSERT INTO %s (%s) VALUES (%s) ON DUPLICATE KEY UPDATE %s', implode($t), implode(', ', $keys), implode(', ', $inserts), implode(', ', $assignments));
				}
			}
		}
	}

	/**
	 * Сборка оператора `DELETE`
	 *
	 * @access  public
	 * @return  string
	 */
	public function buildDelete()
	{
		return 'DELETE';
	}

	/**
	 * Сборка оператора `FROM`
	 *
	 * @access  public
	 * @return  string
	 */
	public function buildFrom()
	{
		if (! empty($this->storage['from']))
		{
			return sprintf('FROM %s', implode(', ', $this->storage['from']));
		}
	}

	/**
	 * Сборка оператора `JOIN`
	 *
	 * @access  public
	 * @return  string
	 */
	public function buildJoin()
	{
		if (! empty($this->storage['join']))
		{
			$builts = [];

			foreach ($this->storage['join'] as $join)
			{
				if (! empty($join['operators']))
				{
					if (! empty($join['conditions']))
					{
						$builts[] = sprintf('%s JOIN %s ON %s', implode(' ', $join['operators']), $join['table'], $this->buildConditions($join['conditions'], $join['parentheses']));
					}
				}
			}

			return implode(PHP_EOL, $builts);
		}
	}

	/**
	 * Сборка оператора `WHERE`
	 *
	 * @access  public
	 * @return  string
	 */
	public function buildWhere()
	{
		if (! empty($this->storage['where']['conditions']))
		{
			return sprintf('WHERE %s', $this->buildConditions($this->storage['where']['conditions'], $this->storage['where']['parentheses']));
		}
	}

	/**
	 * Сборка оператора `GROUP BY`
	 *
	 * @access  public
	 * @return  string
	 */
	public function buildGroup()
	{
		if (! empty($this->storage['group']))
		{
			return sprintf('GROUP BY %s', implode(', ', $this->storage['group']));
		}
	}

	/**
	 * Сборка оператора `HAVING`
	 *
	 * @access  public
	 * @return  string
	 */
	public function buildHaving()
	{
		if (! empty($this->storage['having']['conditions']))
		{
			return sprintf('HAVING %s', $this->buildConditions($this->storage['having']['conditions'], $this->storage['having']['parentheses']));
		}
	}

	/**
	 * Сборка оператора `ORDER BY`
	 *
	 * @access  public
	 * @return  string
	 */
	public function buildOrder()
	{
		if (! empty($this->storage['order']))
		{
			return sprintf('ORDER BY %s', implode(', ', $this->storage['order']));
		}
	}

	/**
	 * Сборка оператора `LIMIT`
	 *
	 * @access  public
	 * @return  string
	 */
	public function buildLimit()
	{
		if (! is_null($this->storage['limit']))
		{
			return sprintf('LIMIT %d', $this->storage['limit']);
		}
	}

	/**
	 * Сборка оператора `OFFSET`
	 *
	 * @access  public
	 * @return  string
	 */
	public function buildOffset()
	{
		if (! is_null($this->storage['offset']))
		{
			return sprintf('OFFSET %d', $this->storage['offset']);
		}
	}

	/**
	 * Сброс данных оператора `SELECT`
	 *
	 * @access  public
	 * @return  object
	 */
	public function resetSelect()
	{
		$this->storage['select'] = [];

		return $this;
	}

	/**
	 * Сброс данных оператора `INSERT`
	 *
	 * @access  public
	 * @return  object
	 */
	public function resetInsert()
	{
		$this->storage['values']['insert'] = [];

		return $this;
	}

	/**
	 * Сброс данных оператора `UPDATE`
	 *
	 * @access  public
	 * @return  object
	 */
	public function resetUpdate()
	{
		$this->storage['values']['update'] = [];

		return $this;
	}

	/**
	 * Сброс данных оператора `UPSERT`
	 *
	 * @access  public
	 * @return  object
	 */
	public function resetUpsert()
	{
		$this->storage['values']['insert'] = [];
		$this->storage['values']['update'] = [];

		return $this;
	}

	/**
	 * Сброс данных оператора `FROM`
	 *
	 * @access  public
	 * @return  object
	 */
	public function resetFrom()
	{
		$this->storage['from'] = [];

		return $this;
	}

	/**
	 * Сброс данных оператора `JOIN`
	 *
	 * @access  public
	 * @return  object
	 */
	public function resetJoin()
	{
		$this->storage['join'] = [];

		return $this;
	}

	/**
	 * Сброс данных оператора `WHERE`
	 *
	 * @access  public
	 * @return  object
	 */
	public function resetWhere()
	{
		$this->storage['where']['conditions'] = [];
		$this->storage['where']['parentheses']['open'] = 0;
		$this->storage['where']['parentheses']['close'] = 0;

		return $this;
	}

	/**
	 * Сброс данных оператора `GROUP BY`
	 *
	 * @access  public
	 * @return  object
	 */
	public function resetGroup()
	{
		$this->storage['group'] = [];

		return $this;
	}

	/**
	 * Сброс данных оператора `HAVING`
	 *
	 * @access  public
	 * @return  object
	 */
	public function resetHaving()
	{
		$this->storage['having']['conditions'] = [];
		$this->storage['having']['parentheses']['open'] = 0;
		$this->storage['having']['parentheses']['close'] = 0;

		return $this;
	}

	/**
	 * Сброс данных оператора `ORDER BY`
	 *
	 * @access  public
	 * @return  object
	 */
	public function resetOrder()
	{
		$this->storage['order'] = [];

		return $this;
	}

	/**
	 * Сброс данных оператора `LIMIT`
	 *
	 * @access  public
	 * @return  object
	 */
	public function resetLimit()
	{
		$this->storage['limit'] = null;

		return $this;
	}

	/**
	 * Сброс данных оператора `OFFSET`
	 *
	 * @access  public
	 * @return  object
	 */
	public function resetOffset()
	{
		$this->storage['offset'] = null;

		return $this;
	}

	/**
	 * Получение данных оператора `LIMIT`
	 *
	 * @access  public
	 * @return  int
	 */
	public function getLimit()
	{
		return $this->storage['limit'];
	}

	/**
	 * Получение данных оператора `OFFSET`
	 *
	 * @access  public
	 * @return  int
	 */
	public function getOffset()
	{
		return $this->storage['offset'];
	}

	/**
	 * Получение значений в виде параметров
	 *
	 * @access  public
	 * @return  array
	 */
	public function getParams()
	{
		return $this->storage['params'];
	}

	/**
	 * Получение SQL запроса в виде строки
	 *
	 * @access  public
	 * @return  string
	 */
	public function getSql()
	{
		if ($this->isSelect())
		{
			return implode(PHP_EOL, array_filter([
				$this->buildSelect(),
				$this->buildFrom(),
				$this->buildJoin(),
				$this->buildWhere(),
				$this->buildGroup(),
				$this->buildHaving(),
				$this->buildOrder(),
				$this->buildLimit(),
				$this->buildOffset(),
			]));
		}

		if ($this->isInsert())
		{
			return $this->buildInsert();
		}

		if ($this->isUpdate())
		{
			return implode(PHP_EOL, array_filter([
				$this->buildUpdate(),
				$this->buildWhere(),
				$this->buildLimit(),
				$this->buildOffset(),
			]));
		}

		if ($this->isUpsert())
		{
			return $this->buildUpsert();
		}

		if ($this->isDelete())
		{
			return implode(PHP_EOL, array_filter([
				$this->buildDelete(),
				$this->buildFrom(),
				$this->buildWhere(),
				$this->buildLimit(),
				$this->buildOffset(),
			]));
		}
	}

	/**
	 * Максимальная близость к SQL синтаксису
	 *
	 * @param   string   $property
	 *
	 * @access  public
	 * @return  object
	 */
	public function __get($property)
	{
		if (strcasecmp($property, 'or') === 0)
		{
			return $this->or_();
		}
		else if (strcasecmp($property, 'and') === 0)
		{
			return $this->and_();
		}

		else if (strcasecmp($property, 'asc') === 0)
		{
			return $this->asc();
		}
		else if (strcasecmp($property, 'desc') === 0)
		{
			return $this->desc();
		}

		else if (strcasecmp($property, 'natural') === 0)
		{
			return $this->natural();
		}
		else if (strcasecmp($property, 'left') === 0)
		{
			return $this->left();
		}
		else if (strcasecmp($property, 'right') === 0)
		{
			return $this->right();
		}
		else if (strcasecmp($property, 'inner') === 0)
		{
			return $this->inner();
		}
		else if (strcasecmp($property, 'cross') === 0)
		{
			return $this->cross();
		}
		else if (strcasecmp($property, 'outer') === 0)
		{
			return $this->outer();
		}

		return $this;
	}

	/**
	 * Простое экранирование имени таблицы
	 *
	 * @param   string   $table
	 *
	 * @access  protected
	 * @return  string
	 */
	protected function quoteTable($table)
	{
		return '`' . str_replace('`', '``', $table) . '`';
	}

	/**
	 * Простое экранирование имени колонки
	 *
	 * @param   string   $column
	 *
	 * @access  protected
	 * @return  string
	 */
	protected function quoteColumn($column)
	{
		return '`' . str_replace('`', '``', $column) . '`';
	}

	/**
	 * Подготовка имени таблицы перед вставкой в SQL запрос
	 *
	 * @param   mixed   $value
	 *
	 * @access  protected
	 * @return  string
	 */
	protected function prepareTable($value)
	{
		if ($value instanceof self)
		{
			return sprintf('(%s)', $value->getSql());
		}

		else if ($value instanceof Closure)
		{
			return $value($this);
		}

		else if (is_array($value))
		{
			return $this->prepareArray($value, [$this, 'prepareTable']);
		}

		else if (is_string($value))
		{
			$segments = explode('.', $value);

			foreach ($segments as $i => $segment)
			{
				$segments[$i] = $this->quoteTable($segment);
			}

			return implode('.', $segments);
		}
	}

	/**
	 * Подготовка имени колонки перед вставкой в SQL запрос
	 *
	 * @param   mixed   $value
	 *
	 * @access  protected
	 * @return  string
	 */
	protected function prepareColumn($value)
	{
		if ($value instanceof self)
		{
			return sprintf('(%s)', $value->getSql());
		}

		else if ($value instanceof Closure)
		{
			return $value($this);
		}

		else if (is_array($value))
		{
			return $this->prepareArray($value, [$this, 'prepareColumn']);
		}

		else if (is_null($value))
		{
			return 'NULL';
		}

		else if (is_bool($value))
		{
			return $value ? '1' : '0';
		}

		else if (is_numeric($value))
		{
			return (string) $value;
		}

		else if (is_string($value))
		{
			$segments = explode('.', $value);

			foreach ($segments as $i => $segment)
			{
				if (strlen($segment) === 0)
				{
					$segments[$i] = '*';

					continue;
				}

				if (strcmp($segment, '*') === 0)
				{
					$segments[$i] = '*';

					continue;
				}

				$segments[$i] = $this->quoteColumn($segment);
			}

			return implode('.', $segments);
		}
	}

	/**
	 * Подготовка значения перед вставкой в SQL запрос
	 *
	 * @param   mixed   $value
	 *
	 * @access  protected
	 * @return  string
	 */
	protected function prepareValue($value)
	{
		if ($value instanceof self)
		{
			return sprintf('(%s)', $value->getSql());
		}

		else if ($value instanceof Closure)
		{
			return $value($this);
		}

		else if ($value instanceof DateTime)
		{
			return $this->prepareValue($value->format('Y-m-d H:i:s'));
		}

		else if (is_array($value))
		{
			return $this->prepareArray($value, [$this, 'prepareValue']);
		}

		else if (is_null($value))
		{
			return 'NULL';
		}

		else if (is_bool($value))
		{
			return $value ? '1' : '0';
		}

		else if (is_numeric($value))
		{
			return (string) $value;
		}

		else if (is_string($value))
		{
			$count = count($this->storage['params']);

			$param = sprintf(':BP%d', $count);

			$this->storage['params'][$param] = $value;

			return $param;
		}
	}

	/**
	 * Подготовка пар перед вставкой в SQL запрос
	 *
	 * @param   array   $pairs
	 *
	 * @access  protected
	 * @return  array
	 */
	protected function preparePairs(array $pairs)
	{
		$prepared = [];

		array_walk_recursive($pairs, function($value, $column) use(& $prepared)
		{
			$prepared[$this->prepareColumn($column)] = $this->prepareValue($value);
		});

		return $prepared;
	}

	/**
	 * Подготовка массива перед вставкой в SQL запрос
	 *
	 * @param   array      $values
	 * @param   callable   $preparer
	 *
	 * @access  protected
	 * @return  array
	 */
	protected function prepareArray(array $values, callable $preparer)
	{
		$prepared = [];

		array_walk_recursive($values, function($value) use(& $prepared, $preparer)
		{
			$prepared[] = call_user_func($preparer, $value);
		});

		return $prepared;
	}

	/**
	 * Подготовка оператора сравнения перед вставкой в SQL запрос
	 *
	 * @param   string   $value
	 *
	 * @access  protected
	 * @return  string
	 */
	protected function prepareComparison($value)
	{
		static $comparisons = [
			'=', '!=', '<>', '<', '>', '<=>', '<=', '>=',
			'IS', 'IS NOT', 'BETWEEN', 'NOT BETWEEN', 'IN', 'NOT IN',
			'LIKE', 'NOT LIKE', 'RLIKE', 'NOT RLIKE', 'REGEXP', 'NOT REGEXP',
			'EXISTS', 'NOT EXISTS',
		];

		foreach ($comparisons as $comparison)
		{
			if (strcasecmp($comparison, $value) === 0)
			{
				return $comparison;
			}
		}

		return '=';
	}

	/**
	 * Сборка условий
	 *
	 * @param   array   $conditions
	 * @param   array   $parentheses
	 *
	 * @access  protected
	 * @return  string
	 */
	protected function buildConditions(array $conditions, array $parentheses)
	{
		$i = 0;

		$built = null;

		$amount = count($conditions);

		foreach ($conditions as $condition)
		{
			list($left, $compare, $right, $logical) = $condition;

			if (++$i > 1 && $i <= $amount)
			{
				$built .= sprintf('%s ', $logical);
			}

			$built .= sprintf('%s ', $this->buildComparison($left, $compare, $right));
		}

		if (0 < $parentheses['unclosed'] = $parentheses['open'] - $parentheses['close'])
		{
			$built .= str_repeat(')', $parentheses['unclosed']);
		}

		return $built;
	}

	/**
	 * Сборка строки с оператором сравнения
	 *
	 * @param   string   $left
	 * @param   string   $compare
	 * @param   mixed    $right
	 *
	 * @access  protected
	 * @return  string
	 */
	protected function buildComparison($left, $compare, $right)
	{
		if (in_array($compare, ['IN', 'NOT IN']))
		{
			return sprintf('%s %s (%s)', $left, $compare, implode(', ', $right));
		}

		if (in_array($compare, ['BETWEEN', 'NOT BETWEEN']))
		{
			return sprintf('%s %s %s AND %s', $left, $compare, $right[0], $right[1]);
		}

		return sprintf('%s %s %s', $left, $compare, $right);
	}

	/**
	 * Подготовка SQL запроса
	 *
	 * @access  protected
	 * @return  object
	 */
	protected function prepareSql()
	{
		$this->statement = $this->pdo->prepare($this->getSql());

		return $this;
	}

	/**
	 * Выполнение SQL запроса
	 *
	 * @access  protected
	 * @return  bool
	 */
	protected function executeSql()
	{
		return $this->statement->execute($this->getParams());
	}

	/**
	 * Чтение результирующего набора из кэша
	 *
	 * @param   string    $typeResult
	 * @param   Closure   $resultSetter
	 *
	 * @access  protected
	 * @return  mixed
	 */
	protected function readCache($typeResult, Closure $resultSetter)
	{
		$id = hash('md5', $this->getSql() . implode($this->getParams()));

		$dir = sys_get_temp_dir() . DIRECTORY_SEPARATOR;

		$file = $dir . sprintf('query.%s.%s', $typeResult, $id);

		if ($this->cacheLifetime === 0)
		{
			is_file($file) and unlink($file); // автоматическая подчистка...

			return $resultSetter();
		}

		else if (is_file($file) && filemtime($file) + $this->cacheLifetime > time())
		{
			return unserialize(file_get_contents($file));
		}

		$result = $resultSetter();

		file_put_contents($file, serialize($result), LOCK_EX);

		return $result;
	}
}
