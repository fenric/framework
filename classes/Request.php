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
 * Request
 */
class Request extends Collection
{

	/**
	 * Collection with parameters of the request uri
	 */
	public $query;

	/**
	 * Collection with parameters of the request body
	 */
	public $post;

	/**
	 * Collection with files of the request
	 */
	public $files;

	/**
	 * Collection with cookies of the request
	 */
	public $cookies;

	/**
	 * Collection with environment of the request
	 */
	public $environment;

	/**
	 * Collection with parameters of the request route
	 */
	public $parameters;

	/**
	 * Session of the request route
	 */
	public $session;

	/**
	 * Constructor of the class
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

		$this->session = new Session();
	}

	/**
	 * Gets the request body
	 */
	public function getBody() : string
	{
		return file_get_contents('php://input');
	}

	/**
	 * Gets the request root folder
	 */
	public function getRoot() : string
	{
		$script = $this->environment->get('SCRIPT_NAME');

		$dirname = pathinfo($script, PATHINFO_DIRNAME);

		return rtrim($dirname, DIRECTORY_SEPARATOR);
	}

	/**
	 * Gets the request scheme
	 */
	public function getScheme() : string
	{
		return $this->isSecure() ? 'https' : 'http';
	}

	/**
	 * Gets the request host
	 */
	public function getHost() :? string
	{
		return parse_url('scheme://' . $this->environment->get('HTTP_HOST'), PHP_URL_HOST);
	}

	/**
	 * Gets the request port
	 */
	public function getPort() :? int
	{
		return parse_url('scheme://' . $this->environment->get('HTTP_HOST'), PHP_URL_PORT);
	}

	/**
	 * Gets the request path
	 */
	public function getPath() :? string
	{
		return parse_url(urldecode($this->environment->get('REQUEST_URI')), PHP_URL_PATH);
	}

	/**
	 * Gets the request query
	 */
	public function getQuery() :? string
	{
		return parse_url(urldecode($this->environment->get('REQUEST_URI')), PHP_URL_QUERY);
	}

	/**
	 * Gets the request method
	 */
	public function getMethod() : string
	{
		return strtoupper($this->environment->get('REQUEST_METHOD'));
	}

	/**
	 * Checks whether the request method is OPTIONS
	 */
	public function isOptions() : bool
	{
		return 0 === strcmp($this->getMethod(), 'OPTIONS');
	}

	/**
	 * Checks whether the request method is HEAD
	 */
	public function isHead() : bool
	{
		return 0 === strcmp($this->getMethod(), 'HEAD');
	}

	/**
	 * Checks whether the request method is GET
	 */
	public function isGet() : bool
	{
		return 0 === strcmp($this->getMethod(), 'GET');
	}

	/**
	 * Checks whether the request method is POST
	 */
	public function isPost() : bool
	{
		return 0 === strcmp($this->getMethod(), 'POST');
	}

	/**
	 * Checks whether the request method is PATCH
	 */
	public function isPatch() : bool
	{
		return 0 === strcmp($this->getMethod(), 'PATCH');
	}

	/**
	 * Checks whether the request method is DELETE
	 */
	public function isDelete() : bool
	{
		return 0 === strcmp($this->getMethod(), 'DELETE');
	}

	/**
	 * Checks whether the request method is PUT
	 */
	public function isPut() : bool
	{
		return 0 === strcmp($this->getMethod(), 'PUT');
	}

	/**
	 * Checks whether the request is sent via HTTPS
	 */
	public function isSecure() : bool
	{
		return 0 === strcasecmp($this->environment->get('HTTPS'), 'on');
	}

	/**
	 * Checks whether the request is sent via AJAX
	 */
	public function isAjax() : bool
	{
		return 0 === strcasecmp($this->environment->get('HTTP_X_REQUESTED_WITH'), 'XMLHttpRequest');
	}
}
