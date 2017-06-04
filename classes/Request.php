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
		$this->query = new Collection($_GET);

		$this->post = new Collection($_POST);

		$this->files = new Collection($_FILES);

		$this->cookies = new Collection($_COOKIE);

		$this->environment = new Collection($_SERVER + $_ENV);

		$this->parameters = new Collection();

		parent::__construct($_REQUEST);
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
		return 0 === strcmp($this->getMethod(), 'OPTIONS');
	}

	/**
	 * Это HEAD запрос
	 */
	public function isHead() : bool
	{
		return 0 === strcmp($this->getMethod(), 'HEAD');
	}

	/**
	 * Это GET запрос
	 */
	public function isGet() : bool
	{
		return 0 === strcmp($this->getMethod(), 'GET');
	}

	/**
	 * Это POST запрос
	 */
	public function isPost() : bool
	{
		return 0 === strcmp($this->getMethod(), 'POST');
	}

	/**
	 * Это PATCH запрос
	 */
	public function isPatch() : bool
	{
		return 0 === strcmp($this->getMethod(), 'PATCH');
	}

	/**
	 * Это DELETE запрос
	 */
	public function isDelete() : bool
	{
		return 0 === strcmp($this->getMethod(), 'DELETE');
	}

	/**
	 * Это PUT запрос
	 */
	public function isPut() : bool
	{
		return 0 === strcmp($this->getMethod(), 'PUT');
	}

	/**
	 * Это безопасный запрос
	 */
	public function isSecure() : bool
	{
		return 0 === strcmp($this->environment->get('HTTPS'), 'on');
	}

	/**
	 * Это асинхронный запрос
	 */
	public function isAjax() : bool
	{
		return 0 === strcmp($this->environment->get('HTTP_X_REQUESTED_WITH'), 'XMLHttpRequest');
	}
}
