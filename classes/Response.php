<?php
/**
 * It is free open-source software released under the MIT License.
 *
 * @author       Anatoly Nekhay <a.fenric@gmail.com>
 * @copyright    Copyright (c) 2013-2016 by Fenric Laboratory
 * @license      http://fenric.ru/license/
 * @link         http://fenric.ru/
 */

namespace Fenric;

/**
 * Response
 */
class Response extends Object
{

	/**
	 * Статус ответа
	 *
	 * @var	    int
	 * @access  protected
	 */
	protected $status = 200;

	/**
	 * HTTP заголовки
	 *
	 * @var	    array
	 * @access  protected
	 */
	protected $headers = [];

	/**
	 * HTTP куки
	 *
	 * @var	    array
	 * @access  protected
	 */
	protected $cookies = [];

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
	 * @param   string   $name
	 * @param   string   $value
	 *
	 * @access  public
	 * @return  object
	 */
	public function setHeader($name, $value)
	{
		$this->headers[$name] = $value;

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
	 * Установка HTTP куки
	 *
	 * @param   string   $name
	 * @param   string   $value
	 * @param   int      $expires
	 * @param   array    $options
	 *
	 * @access  public
	 * @return  object
	 */
	public function setCookie($name, $value, $expires = 0, array $options = [])
	{
		$options += ['path' => '/', 'domain' => '', 'secure' => false, 'httpOnly' => false];

		$this->cookies[$name] = ['value' => $value, 'expires' => ($expires <> 0 ? $expires + time() : $expires), 'options' => $options];

		return $this;
	}

	/**
	 * Получение HTTP кук
	 *
	 * @access  public
	 * @return  object
	 */
	public function getCookies()
	{
		return $this->cookies;
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
	 * Сброс свойств класса
	 *
	 * @access  public
	 * @return  object
	 */
	public function reset()
	{
		$this->status = 200;
		$this->headers = [];
		$this->cookies = [];
		$this->content = null;

		return $this;
	}

	/**
	 * Очистка буфера вывода
	 *
	 * @access  public
	 * @return  object
	 */
	public function clean()
	{
		while (ob_get_level() > 0)
		{
			ob_end_clean();
		}

		return $this;
	}

	/**
	 * Отправка ответа
	 *
	 * @access  public
	 * @return  void
	 */
	public function send()
	{
		$this->dispatchEvent('beforeSend');

		$this->sendHeaders();
		$this->sendCookies();
		$this->sendContent();

		$this->dispatchEvent('afterSend');
	}

	/**
	 * Отправка HTTP заголовков
	 *
	 * @access  protected
	 * @return  void
	 */
	protected function sendHeaders()
	{
		http_response_code($this->status);

		if (count($this->headers) > 0)
		{
			$this->dispatchEvent('beforeSendHeaders');

			foreach ($this->headers as $name => $value)
			{
				header(sprintf('%s: %s', $name, $value), true);
			}

			$this->dispatchEvent('afterSendHeaders');
		}
	}

	/**
	 * Отправка HTTP кук
	 *
	 * @access  protected
	 * @return  void
	 */
	protected function sendCookies()
	{
		if (count($this->cookies) > 0)
		{
			$this->dispatchEvent('beforeSendCookies');

			foreach ($this->cookies as $name => $cookie)
			{
				setcookie($name, $cookie['value'], $cookie['expires'], $cookie['options']['path'], $cookie['options']['domain'], $cookie['options']['secure'], $cookie['options']['httpOnly']);
			}

			$this->dispatchEvent('afterSendCookies');
		}
	}

	/**
	 * Отправка контента
	 *
	 * @access  protected
	 * @return  void
	 */
	protected function sendContent()
	{
		if (isset($this->content))
		{
			$this->dispatchEvent('beforeSendContent');

			echo $this->content;

			$this->dispatchEvent('afterSendContent');
		}
	}
}
