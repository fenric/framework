<?php
/**
 * (c) Fenric Lab, 2010-2016
 *
 * @author       Anatoly Nekhay
 * @product      Fenric Framework
 * @site         http://fenric.ru/
 */

namespace Fenric;

/**
 * Controller
 */
abstract class Controller extends Object
{

	/**
	 * Код возврата
	 *
	 * @var     int
	 * @access  public
	 */
	public $code = 404;

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
	 * Параметры обнаруженного маршрута
	 *
	 * @var     object
	 * @access  protected
	 */
	protected $parameters;

	/**
	 * Конструктор класса
	 *
	 * @param   object  $router
	 * @param   object  $parameters
	 *
	 * @access  public
	 * @return  void
	 */
	final public function __construct(Router $router, Collection $parameters)
	{
		// Сохранение экземпляра маршрутизатора инициализировавшего контроллер
		$this->router = $router;

		// Получение и сохранение экземпляра HTTP объекта для обработки запросов клиента
		$this->request = $router->getRequest();

		// Получение и сохранение экземпляра HTTP объекта для генерации ответа клиенту
		$this->response = $router->getResponse();

		// Сохранение коллекции с параметрами обнаруженного маршрута
		$this->parameters = $parameters;
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
