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
 * Controller
 */
abstract class Controller
{

	/**
	 * Экземпляр маршрутизатора инициализировавшего контроллер
	 */
	protected $router;

	/**
	 * Экземпляр HTTP объекта для обработки запросов клиента
	 */
	protected $request;

	/**
	 * Экземпляр HTTP объекта для генерации ответа клиенту
	 */
	protected $response;

	/**
	 * Конструктор класса
	 */
	final public function __construct(Router $router, Request $request, Response $response)
	{
		$this->router = $router;
		$this->request = $request;
		$this->response = $response;
	}

	/**
	 * Получение экземпляра маршрутизатора инициализировавшего контроллер
	 */
	final public function getRouter() : Router
	{
		return $this->router;
	}

	/**
	 * Получение экземпляра HTTP объекта для обработки запросов клиента
	 */
	final public function getRequest() : Request
	{
		return $this->request;
	}

	/**
	 * Получение экземпляра HTTP объекта для генерации ответа клиенту
	 */
	final public function getResponse() : Response
	{
		return $this->response;
	}

	/**
	 * Предварительная инициализация контроллера
	 */
	public function preInit() : bool
	{
		return true;
	}

	/**
	 * Инициализация контроллера
	 */
	public function init() : void
	{}

	/**
	 * Рендеринг контроллера
	 */
	abstract public function render() : void;
}
