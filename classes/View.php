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
 * View
 */
class View
{

	/**
	 * Файл представления
	 *
	 * @var     string
	 * @access  protected
	 */
	protected $filename;

	/**
	 * Переменные представления
	 *
	 * @var     array
	 * @access  protected
	 */
	protected $variables;

	/**
	 * Секции представления
	 *
	 * @var     array
	 * @access  protected
	 */
	protected $sections;

	/**
	 * Макет представления
	 *
	 * @var     object
	 * @access  protected
	 */
	protected $layout;

	/**
	 * Конструктор класса
	 *
	 * @param   string   $filename
	 * @param   array    $variables
	 *
	 * @access  public
	 * @return  void
	 */
	public function __construct($filename, array $variables)
	{
		$this->filename = $filename;

		$this->variables = $variables;
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

		extract($this->variables);

		include $this->filename;

		$content = ob_get_clean();

		if ($this->layout instanceof self)
		{
			$this->layout->sections = $this->sections;

			$this->layout->sections['content'] = $content;

			$content = $this->layout->render();
		}

		return ltrim($content);
	}

	/**
	 * Получение содержимого секции
	 *
	 * @param   string   $sectionId
	 *
	 * @access  protected
	 * @return  string
	 */
	protected function section($sectionId)
	{
		if (isset($this->sections[$sectionId]))
		{
			return $this->sections[$sectionId];
		}
	}

	/**
	 * Начало записи содержимого секции
	 *
	 * @param   string   $sectionId
	 *
	 * @access  protected
	 * @return  void
	 */
	protected function start($sectionId)
	{
		ob_start();

		$this->sections[$sectionId] = null;
	}

	/**
	 * Конец записи содержимого секции
	 *
	 * @access  protected
	 * @return  void
	 */
	protected function stop()
	{
		$sectionId = end($this->sections);

		$this->sections[$sectionId] = ob_get_clean();
	}

	/**
	 * Наследования макета
	 *
	 * @param   object   $layout
	 *
	 * @access  protected
	 * @return  void
	 */
	protected function layout(self $layout)
	{
		$this->layout = $layout;
	}

	/**
	 * Экранирование строки
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
