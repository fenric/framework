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
 * Logger
 */
class Logger
{

    /**
     * Сообщение генерируемое с целью информирования
     */
    const INFO = 'info';

    /**
     * Сообщение генерируемое при возникновении ошибки высокого уровня
     */
    const ERROR = 'error';

    /**
     * Сообщение генерируемое при возникновении ошибки среднего уровня
     */
    const WARNING = 'warning';

    /**
     * Сообщение генерируемое при возникновении ошибки низкого уровня
     */
    const NOTICE = 'notice';

    /**
     * Сообщение генерируемое в процессе отладки
     */
    const DEBUG = 'debug';

    /**
     * Сообщения журнала
     *
     * @var     array
     * @access  protected
     */
    protected $messages = [];

    /**
	 * Добавление в журнал сообщения сгенерированного с целью информирования
	 *
	 * @param   string   $message
	 *
	 * @access  public
	 * @return  void
	 */
    public function info($message)
    {
        $this->add(self::INFO, $message);
    }

    /**
	 * Добавление в журнал сообщения сгенерированного при возникновении ошибки высокого уровня
	 *
	 * @param   string   $message
	 *
	 * @access  public
	 * @return  void
	 */
    public function error($message)
    {
        $this->add(self::ERROR, $message);
    }

    /**
	 * Добавление в журнал сообщения сгенерированного при возникновении ошибки среднего уровня
	 *
	 * @param   string   $message
	 *
	 * @access  public
	 * @return  void
	 */
    public function warning($message)
    {
        $this->add(self::WARNING, $message);
    }

    /**
	 * Добавление в журнал сообщения сгенерированного при возникновении ошибки низкого уровня
	 *
	 * @param   string   $message
	 *
	 * @access  public
	 * @return  void
	 */
    public function notice($message)
    {
        $this->add(self::NOTICE, $message);
    }

    /**
	 * Добавление в журнал сообщения сгенерированного в процессе отладки
	 *
	 * @param   string   $message
	 *
	 * @access  public
	 * @return  void
	 */
    public function debug($message)
    {
        $this->add(self::DEBUG, $message);
    }

    /**
     * Добавление PHP сообщения в журнал
     *
     * @param   int      $type
     * @param   string   $message
     *
     * @access  public
     * @return  void
     *
     * @see     http://php.net/manual/ru/errorfunc.constants.php
     */
    public function php($type, $message)
    {
        switch ($type)
        {
            case E_ERROR :
            case E_PARSE :
            case E_CORE_ERROR :
            case E_COMPILE_ERROR :
            case E_USER_ERROR :
            case E_RECOVERABLE_ERROR :
                $this->error($message);
                break;

            case E_WARNING :
            case E_CORE_WARNING :
            case E_COMPILE_WARNING :
            case E_USER_WARNING :
                $this->warning($message);
                break;

            case E_NOTICE :
            case E_USER_NOTICE :
                $this->notice($message);
                break;

            case E_STRICT :
            case E_DEPRECATED :
            case E_USER_DEPRECATED :
                $this->debug($message);
                break;
        }
    }

    /**
     * Добавление сообщения в журнал
     *
     * @param   string   $level
     * @param   string   $message
     *
     * @access  public
     * @return  void
     */
    public function add($level, $message)
    {
        $this->messages[] = [$level, $message, microtime(true)];
    }

    /**
     * Получение всех сообщений журнала
     *
     * @access  public
     * @return  array
     */
    public function all()
    {
        return $this->messages;
    }

    /**
     * Получение количества сообщений журнала
     *
     * @access  public
     * @return  int
     */
    public function count()
    {
        return count($this->messages);
    }
}
