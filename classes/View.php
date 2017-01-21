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
 * View
 */
class View
{

	/**
	 * Имя представления
	 *
	 * @var     string
	 * @access  protected
	 */
	protected $name;

	/**
	 * Переменные представления
	 *
	 * @var     array
	 * @access  protected
	 */
	protected $variables;

	/**
	 * Участки представления
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
	 * @param   string   $name
	 * @param   array    $variables
	 *
	 * @access  public
	 * @return  void
	 */
	public function __construct($name, array $variables = null)
	{
		$this->name = $name;

		$this->variables = $variables;
	}

	/**
	 * Получение имени представления
	 *
	 * @access  public
	 * @return  string
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * Получение файла представления
	 *
	 * @access  public
	 * @return  string
	 */
	public function getFile()
	{
		return fenric()->path('views', $this->getName() . '.phtml');
	}

	/**
	 * Проверка существования представления
	 *
	 * @access  public
	 * @return  bool
	 */
	public function exists()
	{
		return file_exists($this->getFile());
	}

	/**
	 * Рендеринг представления
	 *
	 * @access  public
	 * @return  mixed
	 */
	public function render()
	{
		if ($this->exists())
		{
			if (isset($this->variables))
			{
				extract($this->variables);
			}

			ob_start();

			include $this->getFile();

			$content = ob_get_clean();

			if ($this->layout instanceof self)
			{
				$this->layout->sections = $this->sections;

				$this->layout->sections['content'] = $content;

				$content = $this->layout->render();
			}

			return $content;
		}
	}

	/**
	 * Отложенный рендеринг представления при преобразования объекта в строку
	 *
	 * @access  public
	 * @return  string
	 */
	public function __toString()
	{
		return (string) $this->render();
	}

	/**
	 * Получение содержимого участка представления
	 *
	 * @param   string   $sectionId
	 *
	 * @access  protected
	 * @return  mixed
	 */
	protected function section($sectionId)
	{
		if (isset($this->sections[$sectionId]))
		{
			return $this->sections[$sectionId];
		}
	}

	/**
	 * Начало записи содержимого участка представления
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
	 * Остановка записи и сохранение содержимого участка представления
	 *
	 * @access  protected
	 * @return  void
	 */
	protected function stop()
	{
		end($this->sections);

		$sectionId = key($this->sections);

		$this->sections[$sectionId] = ob_get_clean();
	}

	/**
	 * Наследование макета представления
	 *
	 * @param   string   $name
	 * @param   array    $variables
	 *
	 * @access  protected
	 * @return  void
	 */
	protected function layout($name, array $variables = null)
	{
		$this->layout = $this->make($name, $variables);
	}

	/**
	 * Получение отрендеренного представления как фрагмент текущего
	 *
	 * @param   string   $name
	 * @param   array    $variables
	 *
	 * @access  protected
	 * @return  string
	 */
	protected function partial($name, array $variables = null)
	{
		return $this->make($name, $variables)->render();
	}

	/**
	 * Создание нового представления
	 *
	 * @param   string   $name
	 * @param   array    $variables
	 *
	 * @access  protected
	 * @return  object
	 */
	protected function make($name, array $variables = null)
	{
		return new self($name, $variables);
	}

	/**
	 * Экранирование строки
	 *
	 * @param   string   $escapable
	 *
	 * @access  protected
	 * @return  string
	 */
	protected function escape($escapable)
	{
		return htmlspecialchars($escapable, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
	}

	/**
	 * Короткий способ экранирования строки
	 *
	 * @param   string   $escapable
	 *
	 * @access  protected
	 * @return  string
	 */
	protected function e($escapable)
	{
		return $this->escape($escapable);
	}
}
