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
 * Import classes
 */
use RuntimeException;

/**
 * Mailer
 */
class Mailer
{

	/**
	 * Символ конца строки
	 *
	 * @var     string
	 * @access  protected
	 */
	protected $eol = "\r\n";

	/**
	 * Кодировка письма по умолчанию
	 *
	 * @var     string
	 * @access  protected
	 */
	protected $charset = 'UTF-8';

	/**
	 * Адрес получателя
	 *
	 * @var     string
	 * @access  protected
	 */
	protected $to;

	/**
	 * Адрес отправителя
	 *
	 * @var     string
	 * @access  protected
	 */
	protected $from;

	/**
	 * Имя отправителя
	 *
	 * @var     string
	 * @access  protected
	 */
	protected $sender;

	/**
	 * Тема письма
	 *
	 * @var     string
	 * @access  protected
	 */
	protected $subject;

	/**
	 * Тело письма
	 *
	 * @var     string
	 * @access  protected
	 */
	protected $body;

	/**
	 * Адреса получателей копий
	 *
	 * @var     array
	 * @access  protected
	 */
	protected $cc = [];

	/**
	 * Адреса получателей скрытых копий
	 *
	 * @var     array
	 * @access  protected
	 */
	protected $bcc = [];

	/**
	 * Файлы в письме
	 *
	 * @var     array
	 * @access  protected
	 */
	protected $attachments = [];

	/**
	 * Пользовательские заголовки
	 *
	 * @var     array
	 * @access  protected
	 */
	protected $headers = [];

	/**
	 * Разделитель частей письма
	 *
	 * @var     string
	 * @access  protected
	 */
	protected $boundary;

	/**
	 * Конструктор класса
	 *
	 * @access  public
	 * @return  void
	 */
	public function __construct()
	{
		$this->boundary = hash('md5', uniqid(__CLASS__, true));
	}

	/**
	 * Установка адреса получателя
	 *
	 * @param   string   $email
	 *
	 * @access  public
	 * @return  object
	 */
	public function to($email)
	{
		if (filter_var($email, FILTER_VALIDATE_EMAIL))
		{
			$this->to = $email;
		}

		return $this;
	}

	/**
	 * Установка адреса отправителя
	 *
	 * @param   string   $email
	 *
	 * @access  public
	 * @return  object
	 */
	public function from($email)
	{
		if (filter_var($email, FILTER_VALIDATE_EMAIL))
		{
			$this->from = $email;
		}

		return $this;
	}

	/**
	 * Установка имени отправителя
	 *
	 * @param   string   $sender
	 *
	 * @access  public
	 * @return  object
	 */
	public function sender($sender)
	{
		$this->sender = $this->cleanLine($sender);

		return $this;
	}

	/**
	 * Установка темы письма
	 *
	 * @param   string   $subject
	 *
	 * @access  public
	 * @return  object
	 */
	public function subject($subject)
	{
		$this->subject = $this->cleanLine($subject);

		return $this;
	}

	/**
	 * Установка тела письма
	 *
	 * @param   string   $body
	 *
	 * @access  public
	 * @return  object
	 */
	public function body($body)
	{
		$this->body = $body;

		return $this;
	}

	/**
	 * Установка получателя копии
	 *
	 * @param   string   $email
	 *
	 * @access  public
	 * @return  object
	 */
	public function cc($email)
	{
		if (filter_var($email, FILTER_VALIDATE_EMAIL))
		{
			$this->cc[] = $email;
		}

		return $this;
	}

	/**
	 * Установка получателя скрытой копии
	 *
	 * @param   string   $email
	 *
	 * @access  public
	 * @return  object
	 */
	public function bcc($email)
	{
		if (filter_var($email, FILTER_VALIDATE_EMAIL))
		{
			$this->bcc[] = $email;
		}

		return $this;
	}

	/**
	 * Прикрепление файла к письму
	 *
	 * @param   string   $file
	 * @param   string   $cid
	 *
	 * @access  public
	 * @return  object
	 */
	public function attachment($file, & $cid = null)
	{
		if (is_readable($file))
		{
			$cid = hash_file('md5', $file);

			$this->attachments[$cid] = $file;
		}

		return $this;
	}

	/**
	 * Установка пользовательского заголовка
	 *
	 * @param   string   $header
	 *
	 * @access  public
	 * @return  object
	 */
	public function header($header)
	{
		$this->headers[] = $header;

		return $this;
	}

	/**
	 * Сборка заголовков письма
	 *
	 * @access  public
	 * @return  string
	 */
	public function buildHeaders()
	{
		$lines[] = 'MIME-Version: 1.0';

		$lines[] = sprintf('Date: %s', gmdate(DATE_RFC2822));

		$lines[] = sprintf('From: %s <%s>', $this->encodeLine($this->sender ?: $this->from), $this->from);

		if (! empty($this->cc))
		{
			foreach ($this->cc as $cc)
			{
				$lines[] = sprintf('Cc: %s', $cc);
			}
		}

		if (! empty($this->bcc))
		{
			foreach ($this->bcc as $bcc)
			{
				$lines[] = sprintf('Bcc: %s', $bcc);
			}
		}

		if (! empty($this->headers))
		{
			foreach ($this->headers as $header)
			{
				$lines[] = $header;
			}
		}

		$lines[] = sprintf('Content-Type: multipart/mixed; boundary="%s"', $this->boundary);
		$lines[] = '';

		return implode($this->eol, $lines);
	}

	/**
	 * Сборка тела письма
	 *
	 * @access  public
	 * @return  string
	 */
	public function buildBody()
	{
		if (! empty($this->body))
		{
			$lines[] = '';
			$lines[] = sprintf('--%s', $this->boundary);
			$lines[] = sprintf('Content-Type: text/html; charset="%s"', $this->charset);
			$lines[] = 'Content-Transfer-Encoding: base64';
			$lines[] = '';
			$lines[] = $this->encodeData($this->body);
		}

		if (! empty($this->attachments))
		{
			foreach ($this->attachments as $cid => $attachment)
			{
				$lines[] = '';
				$lines[] = sprintf('--%s', $this->boundary);
				$lines[] = sprintf('Content-ID: <%s>', $cid);
				$lines[] = sprintf('Content-Type: application/octet-stream; name="%s"', $this->encodeLine(basename($attachment)));
				$lines[] = 'Content-Disposition: attachment';
				$lines[] = 'Content-Transfer-Encoding: base64';
				$lines[] = '';
				$lines[] = $this->encodeData(file_get_contents($attachment));
			}
		}

		if (isset($lines))
		{
			$lines[] = sprintf('--%s--', $this->boundary);
			$lines[] = '';

			return implode($this->eol, $lines);
		}
	}

	/**
	 * Отправка письма
	 *
	 * @access  public
	 * @return  bool
	 *
	 * @throws  RuntimeException
	 */
	public function send()
	{
		if (! empty($this->to))
		{
			if (! empty($this->from))
			{
				if (! empty($this->subject))
				{
					if (@ mail($this->to, $this->encodeLine($this->subject), $this->buildBody(), $this->buildHeaders(), sprintf('-f %s', $this->from)))
					{
						return true;
					}
					else throw new RuntimeException(fenric()->t('mailer', 'refused'));
				}
				else throw new RuntimeException(fenric()->t('mailer', 'undefined.subject'));
			}
			else throw new RuntimeException(fenric()->t('mailer', 'undefined.from'));
		}
		else throw new RuntimeException(fenric()->t('mailer', 'undefined.to'));
	}

	/**
	 * Очистка строки
	 *
	 * @param   string   $line
	 *
	 * @access  protected
	 * @return  string
	 */
	protected function cleanLine($line)
	{
		return trim(str_replace(["\r", "\n", "\0"], '', $line));
	}

	/**
	 * Кодирование строки
	 *
	 * @param   string   $line
	 *
	 * @access  protected
	 * @return  string
	 */
	protected function encodeLine($line)
	{
		return sprintf('=?%s?B?%s?=', $this->charset, base64_encode($line));
	}

	/**
	 * Кодирование данных
	 *
	 * @param   string   $data
	 *
	 * @access  protected
	 * @return  string
	 */
	protected function encodeData($data)
	{
		$data = base64_encode($data);
		$data = chunk_split($data, 76, $this->eol);
		$data = rtrim($data);

		return $data;
	}
}
