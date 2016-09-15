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
 * View
 */
class View extends Object
{

	/**
	 * Имя представления
	 *
	 * @var     string
	 * @access  protected
	 */
	protected $name;

	/**
	 * Данные представления
	 *
	 * @var     array
	 * @access  protected
	 */
	protected $data;

	/**
	 * Секции представления
	 *
	 * @var     array
	 * @access  protected
	 */
	protected $sections = [];

	/**
	 * Макет представления
	 *
	 * @var     object
	 * @access  protected
	 */
	protected $layout;

	/**
	 * Инициализация представления
	 *
	 * @param   string   $name
	 * @param   array    $data
	 *
	 * @access  public
	 * @return  void
	 */
	public function __construct($name, array $data = [])
	{
		$this->name = $name;
		$this->data = $data;
	}

	/**
	 * Рендеринг представления
	 *
	 * @access  public
	 * @return  string
	 */
	public function render()
	{
		ob_start();

		extract($this->data, EXTR_REFS);

		include fenric()->path('views', $this->name . '.phtml');

		$content = ob_get_clean();

		if ($this->layout instanceof View)
		{
			$this->layout->sections = $this->sections;

			$this->layout->sections['content'] = $content;

			$content = $this->layout->render();
		}

		return ltrim($content);
	}

	/**
	 * Получение содержимого секции представления
	 *
	 * @param   string   $key
	 *
	 * @access  protected
	 * @return  string
	 */
	protected function section($key)
	{
		if (isset($this->sections[$key]))
		{
			return $this->sections[$key];
		}
	}

	/**
	 * Начало записи содержимого секции представления
	 *
	 * @param   string   $section
	 *
	 * @access  protected
	 * @return  void
	 */
	protected function start($section)
	{
		if (ob_start())
		{
			$this->sections[] = $section;
		}
	}

	/**
	 * Конец записи содержимого секции представления
	 *
	 * @access  protected
	 * @return  void
	 */
	protected function end()
	{
		$section = array_pop($this->sections);

		$this->sections[$section] = ob_get_clean();
	}

	/**
	 * Наследования макета представления
	 *
	 * @param   string   $name
	 * @param   array    $data
	 *
	 * @access  protected
	 * @return  void
	 */
	protected function layout($name, array $data = [])
	{
		$this->layout = new static($name, $data);
	}

	/**
	 * Получение отрендеренного представления
	 *
	 * @param   string   $name
	 * @param   array    $data
	 *
	 * @access  protected
	 * @return  string
	 */
	protected function fetch($name, array $data = [])
	{
		return ( new static($name, $data + $this->data) )->render();
	}

	/**
	 * Экранирование данных
	 *
	 * @param   string   $value
	 *
	 * @access  protected
	 * @return  string
	 */
	protected function e($value)
	{
		return htmlentities($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8', false);
	}
}
