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
 * Request
 */
class Request extends Object
{

	/**
	 * {description}
	 *
	 * @var     object
	 * @access  public
	 */
	public $query;

	/**
	 * {description}
	 *
	 * @var     object
	 * @access  public
	 */
	public $post;

	/**
	 * {description}
	 *
	 * @var     object
	 * @access  public
	 */
	public $files;

	/**
	 * {description}
	 *
	 * @var     object
	 * @access  public
	 */
	public $cookies;

	/**
	 * {description}
	 *
	 * @var     object
	 * @access  public
	 */
	public $environment;

	/**
	 * {description}
	 *
	 * @var     object
	 * @access  public
	 */
	public $parameters;

	/**
	 * {description}
	 *
	 * @var     string
	 * @access  public
	 */
	public $rawBody;

	/**
	 * Конструктор класса
	 *
	 * @param   array   $query
	 * @param   array   $post
	 * @param   array   $files
	 * @param   array   $cookies
	 * @param   array   $environment
	 * @param   array   $parameters
	 * @param   string  $rawBody
	 *
	 * @access  public
	 * @return  void
	 */
	public function __construct(array $query, array $post, array $files, array $cookies, array $environment, array $parameters, $rawBody)
	{
		$this->query = new Collection($query);

		$this->post = new Collection($post);

		$this->files = new Collection($files);

		$this->cookies = new Collection($cookies);

		$this->environment = new Collection($environment);

		$this->parameters = new Collection($parameters);

		$this->rawBody = $rawBody;
	}

	/**
	 * Получение родительской директории
	 *
	 * @access  public
	 * @return  string
	 */
	public function getRoot()
	{
		$script = $this->environment->get('SCRIPT_NAME');

		$dirname = pathinfo($script, PATHINFO_DIRNAME);

		return rtrim($dirname, DIRECTORY_SEPARATOR);
	}

	/**
	 * Получение запрошенного хоста
	 *
	 * @access  public
	 * @return  string
	 */
	public function getHost()
	{
		return parse_url('scheme://' . $this->environment->get('HTTP_HOST'), PHP_URL_HOST);
	}

	/**
	 * Получение запрошенного порта
	 *
	 * @access  public
	 * @return  int
	 */
	public function getPort()
	{
		return parse_url('scheme://' . $this->environment->get('HTTP_HOST'), PHP_URL_PORT);
	}

	/**
	 * Получение запрошенного URI
	 *
	 * @access  public
	 * @return  string
	 */
	public function getURI()
	{
		return urldecode($this->environment->get('REQUEST_URI'));
	}

	/**
	 * Получение запрошенного пути
	 *
	 * @access  public
	 * @return  string
	 */
	public function getPath()
	{
		return parse_url($this->getURI(), PHP_URL_PATH);
	}

	/**
	 * Получение запрошенных параметров
	 *
	 * @access  public
	 * @return  string
	 */
	public function getQuery()
	{
		return parse_url($this->getURI(), PHP_URL_QUERY);
	}

	/**
	 * Получение HTTP метода
	 *
	 * @access  public
	 * @return  string
	 */
	public function getMethod()
	{
		return strtoupper($this->environment->get('REQUEST_METHOD'));
	}

	/**
	 * Это OPTIONS запрос
	 *
	 * @access  public
	 * @return  bool
	 */
	public function isOptions()
	{
		return strcmp($this->getMethod(), 'OPTIONS') === 0;
	}

	/**
	 * Это HEAD запрос
	 *
	 * @access  public
	 * @return  bool
	 */
	public function isHead()
	{
		return strcmp($this->getMethod(), 'HEAD') === 0;
	}

	/**
	 * Это GET запрос
	 *
	 * @access  public
	 * @return  bool
	 */
	public function isGet()
	{
		return strcmp($this->getMethod(), 'GET') === 0;
	}

	/**
	 * Это POST запрос
	 *
	 * @access  public
	 * @return  bool
	 */
	public function isPost()
	{
		return strcmp($this->getMethod(), 'POST') === 0;
	}

	/**
	 * Это PUT запрос
	 *
	 * @access  public
	 * @return  bool
	 */
	public function isPut()
	{
		return strcmp($this->getMethod(), 'PUT') === 0;
	}

	/**
	 * Это PATCH запрос
	 *
	 * @access  public
	 * @return  bool
	 */
	public function isPatch()
	{
		return strcmp($this->getMethod(), 'PATCH') === 0;
	}

	/**
	 * Это DELETE запрос
	 *
	 * @access  public
	 * @return  bool
	 */
	public function isDelete()
	{
		return strcmp($this->getMethod(), 'DELETE') === 0;
	}
}
