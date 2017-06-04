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
 * Response
 */
class Response
{

	/**
	 * Статус ответа
	 */
	protected $status = 200;

	/**
	 * Заголовки ответа
	 */
	protected $headers = [];

	/**
	 * Содержимое ответа
	 */
	protected $content = '';

	/**
	 * Установка статуса ответа
	 */
	public function setStatus(int $status) : self
	{
		$this->status = $status;

		return $this;
	}

	/**
	 * Получение статуса ответа
	 */
	public function getStatus() : int
	{
		return $this->status;
	}

	/**
	 * Установка заголовка ответа
	 */
	public function setHeader(string $header) : self
	{
		$this->headers[] = $header;

		return $this;
	}

	/**
	 * Получение заголовков ответа
	 */
	public function getHeaders() : array
	{
		return $this->headers;
	}

	/**
	 * Установка содержимого ответа
	 */
	public function setContent(string $content) : self
	{
		$this->content = $content;

		return $this;
	}

	/**
	 * Установка содержимого ответа в виде JSON данных
	 */
	public function setJsonContent($data, int $options = 0, int $depth = 512, string $charset = 'UTF-8') : self
	{
		$this->setHeader(sprintf('Content-Type: application/json; charset=%s', $charset));

		$this->setContent(json_encode($data, $options, $depth));

		return $this;
	}

	/**
	 * Получение содержимого ответа
	 */
	public function getContent() : string
	{
		return $this->content;
	}

	/**
	 * Отправка ответа
	 */
	public function send() : void
	{
		http_response_code($this->getStatus());

		foreach ($this->getHeaders() as $header)
		{
			header($header, true, $this->getStatus());
		}

		echo $this->getContent();

		if (function_exists('fastcgi_finish_request'))
		{
			fastcgi_finish_request();
		}
	}
}
