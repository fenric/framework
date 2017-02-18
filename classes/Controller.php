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
 * Controller
 */
abstract class Controller
{

	/**
	 * Экземпляр маршрутизатора инициализировавшего контроллер
	 *
	 * @var     object
	 * @access  protected
	 */
	protected $router;

	/**
	 * Экземпляр HTTP объекта для обработки запросов клиента
	 *
	 * @var     object
	 * @access  protected
	 */
	protected $request;

	/**
	 * Экземпляр HTTP объекта для генерации ответа клиенту
	 *
	 * @var     object
	 * @access  protected
	 */
	protected $response;

	/**
	 * Конструктор класса
	 *
	 * @param   object   $router
	 * @param   object   $request
	 * @param   object   $response
	 *
	 * @access  public
	 * @return  void
	 */
	final public function __construct(Router $router, Request $request, Response $response)
	{
		// Сохранение экземпляра маршрутизатора инициализировавшего контроллер
		$this->router = $router;

		// Сохранение экземпляра HTTP объекта для обработки запросов клиента
		$this->request = $request;

		// Сохранение экземпляра HTTP объекта для генерации ответа клиенту
		$this->response = $response;
	}

	/**
	 * Получение экземпляра маршрутизатора инициализировавшего контроллер
	 *
	 * @access  public
	 * @return  object
	 */
	final public function getRouter()
	{
		return $this->router;
	}

	/**
	 * Получение экземпляра HTTP объекта для обработки запросов клиента
	 *
	 * @access  public
	 * @return  object
	 */
	final public function getRequest()
	{
		return $this->request;
	}

	/**
	 * Получение экземпляра HTTP объекта для генерации ответа клиенту
	 *
	 * @access  public
	 * @return  object
	 */
	final public function getResponse()
	{
		return $this->response;
	}

	/**
	 * Предварительная инициализация контроллера
	 *
	 * @access  public
	 * @return  bool
	 */
	public function preInit()
	{
		return true;
	}

	/**
	 * Инициализация контроллера
	 *
	 * @access  public
	 * @return  void
	 */
	public function init()
	{}

	/**
	 * Запуск контроллера
	 *
	 * @access  public
	 * @return  void
	 */
	public function run()
	{}

	/**
	 * Рендеринг контроллера
	 *
	 * @access  public
	 * @return  void
	 */
	abstract public function render();
}
