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
 * Request
 */
class Request extends Collection
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
	 * Конструктор класса
	 *
	 * @access  public
	 * @return  void
	 */
	public function __construct()
	{
		parent::__construct($_REQUEST);

		$this->query = new Collection($_GET);

		$this->post = new Collection($_POST);

		$this->files = new Collection($_FILES);

		$this->cookies = new Collection($_COOKIE);

		$this->environment = new Collection($_SERVER);

		$this->parameters = new Collection();
	}

	/**
	 * Получение тела запроса
	 *
	 * @access  public
	 * @return  string
	 */
	public function getBody()
	{
		return file_get_contents('php://input');
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

	/**
	 * Это AJAX запрос
	 *
	 * @access  public
	 * @return  bool
	 */
	public function isAjax()
	{
		return strcasecmp($this->environment->get('HTTP_X_REQUESTED_WITH'), 'XMLHttpRequest') === 0;
	}
}
