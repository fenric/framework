<?php
/**
 * (c) Fenric Lab, 2010-2016
 *
 * @product      Fenric Framework
 * @author       Anatoly Nekhay E.
 * @email        support@fenric.ru
 * @site         http://fenric.ru/
 */

namespace Fenric;

/**
 * Import classes
 */
use DOMDocument;

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
	 * @var	    object
	 * @access  protected
	 */
	protected $headers;

	/**
	 * HTTP куки
	 *
	 * @var	    object
	 * @access  protected
	 */
	protected $cookies;

	/**
	 * Содержимое ответа
	 *
	 * @var	    string
	 * @access  protected
	 */
	protected $content;

	/**
	 * Конструктор класса
	 *
	 * @access  public
	 * @return  void
	 */
	public function __construct()
	{
		$this->headers = new Collection();
		$this->cookies = new Collection();
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
	 * @param   string   $name
	 * @param   string   $value
	 *
	 * @access  public
	 * @return  object
	 */
	public function setHeader($name, $value)
	{
		$this->headers->set($name, $value);

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

		$this->cookies->set($name, ['value' => $value, 'expires' => ($expires <> 0 ? $expires + time() : $expires), 'options' => $options]);

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
		$this->content = (string) $content;

		return $this;
	}

	/**
	 * Установка содержимого ответа в виде текстовых данных
	 *
	 * @param   string   $content
	 *
	 * @access  public
	 * @return  object
	 */
	public function setPlainContent($content)
	{
		$this->setHeader('Content-Type', 'text/plain; charset=UTF-8');

		$this->setContent($content);

		return $this;
	}

	/**
	 * Установка содержимого ответа в виде HTML данных
	 *
	 * @param   mixed    $content
	 *
	 * @access  public
	 * @return  object
	 */
	public function setHtmlContent($content)
	{
		$this->setHeader('Content-Type', 'text/html; charset=UTF-8');

		if (is_string($content))
		{
			$this->setContent($content);
		}
		else if ($content instanceof View)
		{
			$this->setContent($content->render());
		}
		else if ($content instanceof DOMDocument)
		{
			$this->setContent($content->saveHTML());
		}

		return $this;
	}

	/**
	 * Установка содержимого ответа в виде XML данных
	 *
	 * @param   mixed    $content
	 *
	 * @access  public
	 * @return  object
	 */
	public function setXmlContent($content)
	{
		$this->setHeader('Content-Type', 'application/xml; charset=UTF-8');

		if (is_string($content))
		{
			$this->setContent($content);
		}
		else if ($content instanceof View)
		{
			$this->setContent($content->render());
		}
		else if ($content instanceof DOMDocument)
		{
			$this->setContent($content->saveXML());
		}

		return $this;
	}

	/**
	 * Установка содержимого ответа в виде JSON данных
	 *
	 * @param   mixed    $content
	 * @param   int      $flags
	 * @param   int      $depth
	 *
	 * @access  public
	 * @return  object
	 */
	public function setJsonContent($content, $flags = 0, $depth = 512)
	{
		$this->setHeader('Content-Type', 'application/json; charset=UTF-8');

		$this->setContent(json_encode($content, $flags, $depth));

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
		$this->headers->clear();
		$this->cookies->clear();
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
		$this->trigger('beforeSend');

		$this->sendHeaders();
		$this->sendCookies();
		$this->sendContent();

		$this->trigger('afterSend');
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

		if ($this->headers->count() > 0)
		{
			$this->trigger('beforeSendHeaders');

			foreach ($this->headers->all() as $name => $value)
			{
				header(sprintf('%s: %s', $name, $value), true);
			}

			$this->trigger('afterSendHeaders');
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
		if ($this->cookies->count() > 0)
		{
			$this->trigger('beforeSendCookies');

			foreach ($this->cookies->all() as $name => $cookie)
			{
				setcookie($name, $cookie['value'], $cookie['expires'], $cookie['options']['path'], $cookie['options']['domain'], $cookie['options']['secure'], $cookie['options']['httpOnly']);
			}

			$this->trigger('afterSendCookies');
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
			$this->trigger('beforeSendContent');

			echo $this->content;

			$this->trigger('afterSendContent');
		}
	}
}
