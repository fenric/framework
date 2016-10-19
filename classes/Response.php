<?php
/**
 * It is free open-source software released under the MIT License.
 *
 * @author       Anatoly Nekhay <a.fenric@gmail.com>
 * @copyright    Copyright (c) 2013-2016 by Fenric Laboratory
 * @license      https://github.com/fenric/framework/blob/master/LICENSE.md
 * @link         https://github.com/fenric/framework
 */

namespace Fenric;

/**
 * Response
 */
class Response
{

	/**
	 * Статус ответа
	 *
	 * @var	    int
	 * @access  protected
	 */
	protected $status;

	/**
	 * HTTP заголовки
	 *
	 * @var	    array
	 * @access  protected
	 */
	protected $headers;

	/**
	 * Содержимое ответа
	 *
	 * @var	    string
	 * @access  protected
	 */
	protected $content;

	/**
	 * Установка статуса ответа
	 *
	 * @param   int      $status
	 * @param   array    $headers
	 * @param   string   $content
	 *
	 * @access  public
	 * @return  object
	 */
	public function __construct($status = 200, array $headers = [], $content = '')
	{
		$this->setStatus($status);

		foreach ($headers as $header)
		{
			$this->setHeader($header);
		}

		$this->setContent($content);
	}

	/**
	 * Установка статуса ответа
	 *
	 * @param   int      $status
	 *
	 * @access  public
	 * @return  object
	 */
	public function setStatus($status)
	{
		$this->status = $status;

		return $this;
	}

	/**
	 * Получение статуса ответа
	 *
	 * @access  public
	 * @return  int
	 */
	public function getStatus()
	{
		return $this->status;
	}

	/**
	 * Установка HTTP заголовка
	 *
	 * @param   string   $header
	 *
	 * @access  public
	 * @return  object
	 */
	public function setHeader($header)
	{
		$this->headers[] = $header;

		return $this;
	}

	/**
	 * Получение HTTP заголовков
	 *
	 * @access  public
	 * @return  object
	 */
	public function getHeaders()
	{
		return $this->headers;
	}

	/**
	 * Установка содержимого ответа
	 *
	 * @param   string   $content
	 *
	 * @access  public
	 * @return  object
	 */
	public function setContent($content)
	{
		$this->content = $content;

		return $this;
	}

	/**
	 * Получение содержимого ответа
	 *
	 * @access  public
	 * @return  string
	 */
	public function getContent()
	{
		return $this->content;
	}

	/**
	 * Отправка ответа
	 *
	 * @access  public
	 * @return  void
	 */
	public function send()
	{
		fenric('event::http.response.before.send.status')
			->notifySubscribers([$this]);

		http_response_code($this->getStatus());

		fenric('event::http.response.before.send.headers')
			->notifySubscribers([$this]);

		foreach ($this->getHeaders() as $header) {
			header($header, true);
		}

		fenric('event::http.response.before.send.content')
			->notifySubscribers([$this]);

		echo $this->getContent();

		fenric('event::http.response.before.close.connection')
			->notifySubscribers([$this]);

		if (function_exists('fastcgi_finish_request')) {
			fastcgi_finish_request();
		}

		fenric('event::http.response.finish')
			->notifySubscribers([$this]);
	}
}
