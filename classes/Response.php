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
 * Response
 */
class Response
{

	/**
	 * List of HTTP status codes
	 */
	public const STATUS_100 = [100, 'Continue'];
	public const STATUS_101 = [101, 'Switching Protocols'];
	public const STATUS_102 = [102, 'Processing'];
	public const STATUS_103 = [103, 'Early Hints'];
	public const STATUS_200 = [200, 'OK'];
	public const STATUS_201 = [201, 'Created'];
	public const STATUS_202 = [202, 'Accepted'];
	public const STATUS_203 = [203, 'Non-Authoritative Information'];
	public const STATUS_204 = [204, 'No Content'];
	public const STATUS_205 = [205, 'Reset Content'];
	public const STATUS_206 = [206, 'Partial Content'];
	public const STATUS_207 = [207, 'Multi-Status'];
	public const STATUS_208 = [208, 'Already Reported'];
	public const STATUS_226 = [226, 'IM Used'];
	public const STATUS_300 = [300, 'Multiple Choices'];
	public const STATUS_301 = [301, 'Moved Permanently'];
	public const STATUS_302 = [302, 'Found'];
	public const STATUS_303 = [303, 'See Other'];
	public const STATUS_304 = [304, 'Not Modified'];
	public const STATUS_305 = [305, 'Use Proxy'];
	public const STATUS_306 = [306, 'Switch Proxy'];
	public const STATUS_307 = [307, 'Temporary Redirect'];
	public const STATUS_308 = [308, 'Permanent Redirect'];
	public const STATUS_400 = [400, 'Bad Request'];
	public const STATUS_401 = [401, 'Unauthorized'];
	public const STATUS_402 = [402, 'Payment Required'];
	public const STATUS_403 = [403, 'Forbidden'];
	public const STATUS_404 = [404, 'Not Found'];
	public const STATUS_405 = [405, 'Method Not Allowed'];
	public const STATUS_406 = [406, 'Not Acceptable'];
	public const STATUS_407 = [407, 'Proxy Authentication Required'];
	public const STATUS_408 = [408, 'Request Timeout'];
	public const STATUS_409 = [409, 'Conflict'];
	public const STATUS_410 = [410, 'Gone'];
	public const STATUS_411 = [411, 'Length Required'];
	public const STATUS_412 = [412, 'Precondition Failed'];
	public const STATUS_413 = [413, 'Payload Too Large'];
	public const STATUS_414 = [414, 'URI Too Long'];
	public const STATUS_415 = [415, 'Unsupported Media Type'];
	public const STATUS_416 = [416, 'Range Not Satisfiable'];
	public const STATUS_417 = [417, 'Expectation Failed'];
	public const STATUS_418 = [418, 'I\'m a teapot'];
	public const STATUS_421 = [421, 'Misdirected Request'];
	public const STATUS_422 = [422, 'Unprocessable Entity'];
	public const STATUS_423 = [423, 'Locked'];
	public const STATUS_424 = [424, 'Failed Dependency'];
	public const STATUS_426 = [426, 'Upgrade Required'];
	public const STATUS_428 = [428, 'Precondition Required'];
	public const STATUS_429 = [429, 'Too Many Requests'];
	public const STATUS_431 = [431, 'Request Header Fields Too Large'];
	public const STATUS_444 = [444, 'No Response'];
	public const STATUS_451 = [451, 'Unavailable For Legal Reasons'];
	public const STATUS_495 = [495, 'SSL Certificate Error'];
	public const STATUS_496 = [496, 'SSL Certificate Required'];
	public const STATUS_497 = [497, 'HTTP Request Sent to HTTPS Port'];
	public const STATUS_499 = [499, 'Client Closed Request'];
	public const STATUS_500 = [500, 'Internal Server Error'];
	public const STATUS_501 = [501, 'Not Implemented'];
	public const STATUS_502 = [502, 'Bad Gateway'];
	public const STATUS_503 = [503, 'Service Unavailable'];
	public const STATUS_504 = [504, 'Gateway Timeout'];
	public const STATUS_505 = [505, 'HTTP Version Not Supported'];
	public const STATUS_506 = [506, 'Variant Also Negotiates'];
	public const STATUS_507 = [507, 'Insufficient Storage'];
	public const STATUS_508 = [508, 'Loop Detected'];
	public const STATUS_510 = [510, 'Not Extended'];
	public const STATUS_511 = [511, 'Network Authentication Required'];

	/**
	 * Status of the response
	 */
	protected $statusCode = 200;
	protected $statusMessage = 'OK';

	/**
	 * Charset of the response
	 */
	protected $charset = 'UTF-8';

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
	protected $content = null;

	/**
	 * Sets the response status
	 */
	public function status(array $status) : self
	{
		list($code, $message) = $status;

		$this->statusCode = $code;
		$this->statusMessage = $message;

		return $this;
	}

	/**
	 * Sets the response charset
	 */
	public function charset(string $charset) : self
	{
		$this->charset = $charset;

		return $this;
	}

	/**
	 * Sets the response header
	 */
	public function header(string $name, ?string $value, bool $replace = true) : self
	{
		$this->headers[] = [$name, $value, $replace];

		return $this;
	}

	/**
	 * Sets the response cookie
	 */
	public function cookie(string $name, ?string $value, int $lifetime = 0, array $options = []) : self
	{
		$this->cookies[] = [$name, $value, $lifetime, $options];

		return $this;
	}

	/**
	 * Sets the response content
	 */
	public function content(?string $content) : self
	{
		$this->content = $content;

		return $this;
	}

	/**
	 * Sets the response content as JSON
	 */
	public function json($data, ...$options) : self
	{
		$this->header('Content-Type', sprintf('application/json; charset=%s', $this->charset));

		$this->content(json_encode($data, ...$options));

		return $this;
	}

	/**
	 * Sets the response content as view
	 */
	public function view(string $name, array $variables = []) : self
	{
		$this->header('Content-Type', sprintf('text/html; charset=%s', $this->charset));

		$this->content(fenric()->callSharedService('view', [$name])->render($variables));

		return $this;
	}

	/**
	 * Gets the response status code
	 */
	public function getStatusCode() : int
	{
		return $this->statusCode;
	}

	/**
	 * Gets the response status message
	 */
	public function getStatusMessage() : string
	{
		return $this->statusMessage;
	}

	/**
	 * Gets the response charset
	 */
	public function getCharset() : string
	{
		return $this->charset;
	}

	/**
	 * Gets the response headers
	 */
	public function getHeaders() : array
	{
		return $this->headers;
	}

	/**
	 * Gets the response cookies
	 */
	public function getCookies() : array
	{
		return $this->cookies;
	}

	/**
	 * Gets the response content
	 */
	public function getContent() :? string
	{
		return $this->content;
	}

	/**
	 * Checks whether the response status code is invalid
	 */
	public function isInvalid() : bool
	{
		return ($this->getStatusCode() < 100 || $this->getStatusCode() >= 600);
	}

	/**
	 * Checks whether the response status code is informational
	 */
	public function isInformational() : bool
	{
		return ($this->getStatusCode() >= 100 && $this->getStatusCode() < 200);
	}

	/**
	 * Checks whether the response status code is successful
	 */
	public function isSuccessful() : bool
	{
		return ($this->getStatusCode() >= 200 && $this->getStatusCode() < 300);
	}

	/**
	 * Checks whether the response status code is redirection
	 */
	public function isRedirection() : bool
	{
		return ($this->getStatusCode() >= 300 && $this->getStatusCode() < 400);
	}

	/**
	 * Checks whether the response status code indicates a client error
	 */
	public function isClientError() : bool
	{
		return ($this->getStatusCode() >= 400 && $this->getStatusCode() < 500);
	}

	/**
	 * Checks whether the response status code indicates a server error
	 */
	public function isServerError() : bool
	{
		return ($this->getStatusCode() >= 500 && $this->getStatusCode() < 600);
	}

	/**
	 * Checks whether the response is OK
	 */
	public function isOk() : bool
	{
		return $this->getStatusCode() === 200;
	}

	/**
	 * Checks whether the response is forbidden error
	 */
	public function isForbidden() : bool
	{
		return $this->getStatusCode() === 403;
	}

	/**
	 * Checks whether the response is page not found error
	 */
	public function isNotFound() : bool
	{
		return $this->getStatusCode() === 404;
	}

	/**
	 * Checks whether the response is empty
	 */
	public function isEmpty() : bool
	{
		return in_array($this->getStatusCode(), [204, 304]);
	}

	/**
	 * Sends the response to the client
	 */
	public function send() : void
	{
		http_response_code(
			$this->getStatusCode()
		);

		if (count($this->getHeaders()) > 0)
		{
			foreach ($this->getHeaders() as $header)
			{
				list($name, $value, $replace) = $header;

				if ($value === null)
				{
					header_remove($name);

					continue;
				}

				header(sprintf('%s: %s', $name, $value), $replace);
			}
		}

		if (count($this->getCookies()) > 0)
		{
			foreach ($this->getCookies() as $cookie)
			{
				list($name, $value, $lifetime, $options) = $cookie;

				if ($value === null)
				{
					$lifetime = time() - 3600;
				}
				else if ($lifetime <> 0)
				{
					$lifetime += time();
				}

				extract($options + fenric('config::cookies')->all(), EXTR_OVERWRITE);

				setcookie($name, $value, $lifetime, $path, $domain, $httpsOnly, $httpOnly);
			}
		}

		if ($this->getContent())
		{
			echo $this->getContent();
		}

		/**
		 * @link http://php.net/fastcgi_finish_request
		 */
		if (function_exists('fastcgi_finish_request'))
		{
			fastcgi_finish_request();
		}
	}
}
