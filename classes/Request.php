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
	 * Коллекции данных
	 */
	public $query, $post, $files, $cookies, $environment, $parameters;

	/**
	 * Конструктор класса
	 */
	public function __construct()
	{
		parent::__construct($_REQUEST);

		$this->query = new Collection($_GET);

		$this->post = new Collection($_POST);

		$this->files = new Collection($_FILES);

		$this->cookies = new Collection($_COOKIE);

		$this->environment = new Collection($_SERVER + $_ENV);

		$this->parameters = new Collection();
	}

	/**
	 * Получение тела запроса
	 */
	public function getBody() : string
	{
		return file_get_contents('php://input');
	}

	/**
	 * Получение родительской директории
	 */
	public function getRoot() : string
	{
		$script = $this->environment->get('SCRIPT_NAME');

		$dirname = pathinfo($script, PATHINFO_DIRNAME);

		return rtrim($dirname, DIRECTORY_SEPARATOR);
	}

	/**
	 * Получение запрошенного URI
	 */
	public function getURI() : string
	{
		return urldecode($this->environment->get('REQUEST_URI'));
	}

	/**
	 * Получение запрошенной схемы
	 */
	public function getScheme() : string
	{
		return $this->isSecure() ? 'https' : 'http';
	}

	/**
	 * Получение запрошенного хоста
	 */
	public function getHost() : ?string
	{
		return parse_url('scheme://' . $this->environment->get('HTTP_HOST'), PHP_URL_HOST);
	}

	/**
	 * Получение запрошенного порта
	 */
	public function getPort() : ?int
	{
		return parse_url('scheme://' . $this->environment->get('HTTP_HOST'), PHP_URL_PORT);
	}

	/**
	 * Получение запрошенного пути
	 */
	public function getPath() : ?string
	{
		return parse_url($this->getURI(), PHP_URL_PATH);
	}

	/**
	 * Получение запрошенных параметров
	 */
	public function getQuery() : ?string
	{
		return parse_url($this->getURI(), PHP_URL_QUERY);
	}

	/**
	 * Получение запрошенного URL
	 */
	public function getURL() : string
	{
		return sprintf('%s://%s%s?%s',
			$this->getScheme(),
			$this->environment->get('HTTP_HOST'),
			$this->getPath(),
			$this->getQuery()
		);
	}

	/**
	 * Получение HTTP метода
	 */
	public function getMethod() : string
	{
		return $this->environment->get('REQUEST_METHOD');
	}

	/**
	 * Это OPTIONS запрос
	 */
	public function isOptions() : bool
	{
		return strcmp($this->getMethod(), 'OPTIONS') === 0;
	}

	/**
	 * Это HEAD запрос
	 */
	public function isHead() : bool
	{
		return strcmp($this->getMethod(), 'HEAD') === 0;
	}

	/**
	 * Это GET запрос
	 */
	public function isGet() : bool
	{
		return strcmp($this->getMethod(), 'GET') === 0;
	}

	/**
	 * Это POST запрос
	 */
	public function isPost() : bool
	{
		return strcmp($this->getMethod(), 'POST') === 0;
	}

	/**
	 * Это PATCH запрос
	 */
	public function isPatch() : bool
	{
		return strcmp($this->getMethod(), 'PATCH') === 0;
	}

	/**
	 * Это DELETE запрос
	 */
	public function isDelete() : bool
	{
		return strcmp($this->getMethod(), 'DELETE') === 0;
	}

	/**
	 * Это PUT запрос
	 */
	public function isPut() : bool
	{
		return strcmp($this->getMethod(), 'PUT') === 0;
	}

	/**
	 * Это безопасный запрос
	 */
	public function isSecure() : bool
	{
		return strcmp($this->environment->get('HTTPS'), 'on') === 0;
	}

	/**
	 * Это асинхронный запрос
	 */
	public function isAjax() : bool
	{
		return strcmp($this->environment->get('HTTP_X_REQUESTED_WITH'), 'XMLHttpRequest') === 0;
	}
}
