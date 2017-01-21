<?php
/**
 * It is free open-source software released under the MIT License.
 *
 * @author Anatoly Fenric <a.fenric@gmail.com>
 * @copyright Copyright (c) 2013-2016 by Fenric Laboratory
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
	 * Статус ответа
	 *
	 * @var	    int
	 * @access  protected
	 */
	protected $status = 200;

	/**
	 * Заголовки ответа
	 *
	 * @var	    array
	 * @access  protected
	 */
	protected $headers = [];

	/**
	 * Тело ответа
	 *
	 * @var	    string
	 * @access  protected
	 */
	protected $content = null;

	/**
	 * Установка статуса ответа
	 *
	 * @param   int   $status
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
	 * Установка заголовка ответа
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
	 * Получение заголовков ответа
	 *
	 * @access  public
	 * @return  object
	 */
	public function getHeaders()
	{
		return $this->headers;
	}

	/**
	 * Установка тела ответа
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
	 * Установка тела ответа в виде JSON данных
	 *
	 * @param   mixed   $data
	 * @param   int     $options
	 * @param   int     $depth
	 *
	 * @access  public
	 * @return  object
	 */
	public function setJsonContent($data, $options = 0, $depth = 512)
	{
		$this->setHeader('Content-Type: application/json; charset=UTF-8');

		$this->setContent(json_encode($data, $options, $depth));

		return $this;
	}

	/**
	 * Получение тела ответа
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
		http_response_code($this->getStatus());

		foreach ($this->getHeaders() as $header)
		{
			header($header, true);
		}

		echo $this->getContent();
	}
}
