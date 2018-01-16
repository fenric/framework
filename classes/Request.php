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
use RuntimeException;

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
	 * Session of the request
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
	 * Gets the request folder
	 */
	public function root() : string
	{
		$script = $this->environment->get('SCRIPT_NAME');

		$dirname = pathinfo($script, PATHINFO_DIRNAME);

		return rtrim($dirname, DIRECTORY_SEPARATOR);
	}

	/**
	 * Gets the request method
	 *
	 * @throws  RuntimeException
	 */
	public function method() : string
	{
		if (! ($method = $this->environment->get('REQUEST_METHOD')))
		{
			throw new RuntimeException('Unable to determine the request method.');
		}

		return strtoupper($method);
	}

	/**
	 * Gets the request host
	 *
	 * @throws  RuntimeException
	 */
	public function host() : string
	{
		if (! ($host = $this->environment->get('HTTP_HOST')))
		{
			throw new RuntimeException('Unable to determine the request host.');
		}

		return $host;
	}

	/**
	 * Gets the request URI
	 *
	 * @throws  RuntimeException
	 */
	public function uri() : string
	{
		if (! ($uri = $this->environment->get('REQUEST_URI')))
		{
			throw new RuntimeException('Unable to determine the request URI.');
		}

		return $uri;
	}

	/**
	 * Gets the request scheme
	 */
	public function scheme(bool $separable = false) : string
	{
		$scheme = $this->isSecure() ? 'https' : 'http';

		return $scheme . ($separable ? '://' : '');
	}

	/**
	 * Gets the request domain
	 */
	public function domain() :? string
	{
		$host = '//' . $this->host();

		return parse_url($host, PHP_URL_HOST);
	}

	/**
	 * Gets the request port
	 */
	public function port() :? int
	{
		$host = '//' . $this->host();

		return parse_url($host, PHP_URL_PORT);
	}

	/**
	 * Gets the request path
	 */
	public function path() :? string
	{
		$uri = urldecode($this->uri());

		return parse_url($uri, PHP_URL_PATH);
	}

	/**
	 * Gets the request query
	 */
	public function query() :? string
	{
		$uri = urldecode($this->uri());

		return parse_url($uri, PHP_URL_QUERY);
	}

	/**
	 * Gets the request URL
	 */
	public function url(array $params = []) : string
	{
		$url = $this->origin() . $this->path();

		$query = $this->query->clone()->upgrade($params);

		if ($query->count() > 0)
		{
			$url .= '?' . $query->toQueryString();
		}

		return $url;
	}

	/**
	 * Gets the request origin
	 */
	public function origin() : string
	{
		return $this->scheme(true) . $this->host();
	}

	/**
	 * Gets the request body
	 */
	public function body() : string
	{
		return file_get_contents('php://input');
	}

	/**
	 * Gets the request body as JSON
	 */
	public function json(...$options)
	{
		return json_decode($this->body(), ...$options);
	}

	/**
	 * Checks whether the request is root
	 */
	public function isRoot() : bool
	{
		return 0 === strlen($this->root());
	}

	/**
	 * Checks whether the request method is OPTIONS
	 */
	public function isOptions() : bool
	{
		return 0 === strcmp($this->method(), 'OPTIONS');
	}

	/**
	 * Checks whether the request method is HEAD
	 */
	public function isHead() : bool
	{
		return 0 === strcmp($this->method(), 'HEAD');
	}

	/**
	 * Checks whether the request method is GET
	 */
	public function isGet() : bool
	{
		return 0 === strcmp($this->method(), 'GET');
	}

	/**
	 * Checks whether the request method is POST
	 */
	public function isPost() : bool
	{
		return 0 === strcmp($this->method(), 'POST');
	}

	/**
	 * Checks whether the request method is PATCH
	 */
	public function isPatch() : bool
	{
		return 0 === strcmp($this->method(), 'PATCH');
	}

	/**
	 * Checks whether the request method is DELETE
	 */
	public function isDelete() : bool
	{
		return 0 === strcmp($this->method(), 'DELETE');
	}

	/**
	 * Checks whether the request method is PUT
	 */
	public function isPut() : bool
	{
		return 0 === strcmp($this->method(), 'PUT');
	}

	/**
	 * Checks whether the request is secure
	 */
	public function isSecure() : bool
	{
		$signature = $this->environment->get('HTTPS');

		return ! empty($signature) && 0 !== strcasecmp($signature, 'off');
	}

	/**
	 * Checks whether the request is ajax
	 */
	public function isAjax() : bool
	{
		$signature = $this->environment->get('HTTP_X_REQUESTED_WITH');

		return 0 === strcasecmp($signature, 'XMLHttpRequest');
	}

	/**
	 * Confirms the request host by pattern
	 */
	public function isHost(string $pattern, & $matched = []) : bool
	{
		$sanitized = addcslashes($pattern, '\.+?[^]${}=!|:-#');

		$expression = str_replace(['(', ')'], ['(?:', ')?'], $sanitized);

		$expression = str_replace(['*', '%'], ['([^.]*)', '(.*)'], $expression);

		return !! preg_match("#^{$expression}$#u", $this->host(), $matched);
	}

	/**
	 * Confirms the request path by pattern
	 */
	public function isPath(string $pattern, & $matched = []) : bool
	{
		$sanitized = addcslashes($pattern, '\.+?[^]${}=!|:-#');

		$expression = str_replace(['(', ')'], ['(?:', ')?'], $sanitized);

		$expression = str_replace(['*', '%'], ['([^/]*)', '(.*)'], $expression);

		return !! preg_match("#^{$expression}$#u", $this->path(), $matched);
	}
}
