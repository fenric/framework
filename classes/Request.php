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

	/**
	 * Gets the request folder
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
	 * Gets the request username
	 */
	public function getUsername() :? string
	{
		return $this->environment->get('PHP_AUTH_USER');
	}

	/**
	 * Gets the request password
	 */
	public function getPassword() :? string
	{
		return $this->environment->get('PHP_AUTH_PW');
	}

	/**
	 * Gets the request host
	 */
	public function getHost() :? string
	{
		if (! ($host = $this->environment->get('HTTP_HOST')))
		{
			if (! ($host = $this->environment->get('SERVER_NAME')))
			{
				if (! ($host = $this->environment->get('SERVER_ADDR')))
				{
					return null;
				}
			}
		}

		return $host;
	}

	/**
	 * Gets the request URI
	 */
	public function getURI() :? string
	{
		if (! ($uri = $this->environment->get('REQUEST_URI')))
		{
			if (! ($uri = $this->environment->get('SCRIPT_NAME')))
			{
				return null;
			}
		}

		return $uri;
	}

	/**
	 * Gets the request domain
	 */
	public function getDomain() :? string
	{
		$host = '//' . $this->getHost();

		return parse_url($host, PHP_URL_HOST);
	}

	/**
	 * Gets the request port
	 */
	public function getPort() :? int
	{
		$host = '//' . $this->getHost();

		return parse_url($host, PHP_URL_PORT);
	}

	/**
	 * Gets the request path
	 */
	public function getPath() :? string
	{
		$uri = urldecode($this->getURI());

		return parse_url($uri, PHP_URL_PATH);
	}

	/**
	 * Gets the request query
	 */
	public function getQuery() :? string
	{
		$uri = urldecode($this->getURI());

		return parse_url($uri, PHP_URL_QUERY);
	}

	/**
	 * Gets the request URL
	 */
	public function getURL(array $params = []) : string
	{
		$url = '';

		if ($this->getScheme())
		{
			if ($this->getDomain())
			{
				$url .= $this->getScheme() . '://';

				if ($this->getUsername())
				{
					$url .= $this->getUsername();

					if ($this->getPassword())
					{
						$url .= ':' . $this->getPassword();
					}

					$url .= '@';
				}

				$url .= $this->getDomain();

				if ($this->getPort())
				{
					$url .= ':' . $this->getPort();
				}
			}
		}

		if ($this->getPath())
		{
			$url .= $this->getPath();

			$query = clone $this->query;

			$query->upgrade($params);

			if ($query->count() > 0)
			{
				$url .= '?' . $query->toQueryString();
			}
		}

		return $url;
	}

	/**
	 * Confirms the request domain by pattern
	 */
	public function confirmDomain(string $pattern) : bool
	{
		$sanitized = addcslashes($pattern, '\.+?[^]${}=!|:-#');

		$expression = str_replace(['(', '*', '%', ')'], ['(?:', '[^.]*', '.*', ')?'], $sanitized);

		return !! preg_match("#^{$expression}$#u", $this->getDomain());
	}

	/**
	 * Confirms the request path by pattern
	 */
	public function confirmPath(string $pattern) : bool
	{
		$sanitized = addcslashes($pattern, '\.+?[^]${}=!|:-#');

		$expression = str_replace(['(', '*', '%', ')'], ['(?:', '[^/]*', '.*', ')?'], $sanitized);

		return !! preg_match("#^{$expression}$#u", $this->getPath());
	}

	/**
	 * Gets the request body
	 */
	public function getBody() : string
	{
		return file_get_contents('php://input');
	}

	/**
	 * Gets the request body as JSON
	 */
	public function getJSON(...$options)
	{
		return json_decode($this->getBody(), ...$options);
	}
}
