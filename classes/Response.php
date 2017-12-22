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
 * Response
 */
class Response
{

	/**
	 * Status of the response
	 */
	protected $status = 200;

	/**
	 * Headers of the response
	 */
	protected $headers = [];

	/**
	 * Cookies of the response
	 */
	protected $cookies = [];

	/**
	 * Content of the response
	 */
	protected $content = '';

	/**
	 * Sets the response status
	 */
	public function setStatus(int $status) : self
	{
		$this->status = $status;

		return $this;
	}

	/**
	 * Gets the response status
	 */
	public function getStatus() : int
	{
		return $this->status;
	}

	/**
	 * Sets the response header
	 */
	public function setHeader(string $header) : self
	{
		$this->headers[] = $header;

		return $this;
	}

	/**
	 * Gets the response headers
	 */
	public function getHeaders() : array
	{
		return $this->headers;
	}

	/**
	 * Sets the response cookie
	 */
	public function setCookie(string $name, string $value, int $lifetime = 0, array $options = []) : self
	{
		if ($lifetime <> 0) {
			$lifetime += time();
		}

		$this->cookies[] = [$name, $value, $lifetime, $options];

		return $this;
	}

	/**
	 * Gets the response cookies
	 */
	public function getCookies() : array
	{
		return $this->cookies;
	}

	/**
	 * Sets the response content
	 */
	public function setContent(string $content) : self
	{
		$this->content = $content;

		return $this;
	}

	/**
	 * Sets the response content as JSON
	 */
	public function setJSON($data, int $options = 0, string $charset = 'UTF-8') : self
	{
		$this->setHeader(sprintf('Content-Type: application/json; charset=%s', $charset));

		$this->setContent(json_encode($data, $options));

		return $this;
	}

	/**
	 * Sets the response content as view
	 */
	public function setView(string $name, array $variables = [], string $charset = 'UTF-8') : self
	{
		$this->setHeader(sprintf('Content-Type: text/html; charset=%s', $charset));

		$this->setContent(fenric()->callSharedService('view', [$name])->render($variables));

		return $this;
	}

	/**
	 * Gets the response content
	 */
	public function getContent() : string
	{
		return $this->content;
	}

	/**
	 * Sends the response to the client
	 */
	public function send() : void
	{
		if (fenric('event::http.response.before.send')->run([$this]))
		{
			if (fenric('event::http.response.before.send.status')->run([$this, & $this->status]))
			{
				http_response_code($this->getStatus());

				fenric('event::http.response.after.send.status')->run([$this, $this->status]);
			}

			if (count($this->getHeaders()) > 0)
			{
				if (fenric('event::http.response.before.send.headers')->run([$this, & $this->headers]))
				{
					foreach ($this->getHeaders() as $header)
					{
						if (fenric('event::http.response.before.send.header')->run([$this, & $header]))
						{
							header($header, true, $this->getStatus());

							fenric('event::http.response.after.send.header')->run([$this, $header]);
						}
					}

					fenric('event::http.response.after.send.headers')->run([$this, $this->headers]);
				}
			}

			if (count($this->getCookies()) > 0)
			{
				if (fenric('event::http.response.before.send.cookies')->run([$this, & $this->cookies]))
				{
					foreach ($this->getCookies() as $cookie)
					{
						if (fenric('event::http.response.before.send.cookie')->run([$this, & $cookie]))
						{
							list($name, $value, $lifetime, $options) = $cookie;

							extract($options + fenric('config::cookies')->all(), EXTR_OVERWRITE);

							setcookie($name, $value, $lifetime, $path, $domain, $httpsOnly, $httpOnly);

							fenric('event::http.response.after.send.cookie')->run([$this, $cookie]);
						}
					}

					fenric('event::http.response.after.send.cookies')->run([$this, $this->cookies]);
				}
			}

			if (fenric('event::http.response.before.send.content')->run([$this, & $this->content]))
			{
				file_put_contents('php://output', $this->getContent());

				fenric('event::http.response.after.send.content')->run([$this, $this->content]);
			}

			fenric('event::http.response.after.send')->run([$this]);
		}

		/**
		 * @link http://php.net/fastcgi_finish_request
		 */
		if (function_exists('fastcgi_finish_request'))
		{
			fastcgi_finish_request();
		}
	}

	/**
	 * Removes sent header
	 */
	public function removeSentHeader(string $name) : void
	{
		foreach (headers_list() as $header)
		{
			$parts = explode(':', $header, 2);

			list($key, $value) = $parts;

			if (strcasecmp($key, $name) === 0)
			{
				header_remove($key);

				break;
			}
		}
	}

	/**
	 * Removes sent headers
	 */
	public function removeSentHeaders() : void
	{
		foreach (headers_list() as $header)
		{
			$parts = explode(':', $header, 2);

			list($key, $value) = $parts;

			header_remove($key);
		}
	}

	/**
	 * Removes sent output
	 */
	public function removeSentOutput() : void
	{
		while (ob_get_level() > 0)
		{
			ob_end_clean();
		}
	}
}
