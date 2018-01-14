<?php
/**
 * It's free open-source software released under the MIT License.
 *
 * @author Anatoly Fenric <anatoly.fenric@gmail.com>
 * @copyright Copyright (c) 2013-2018 by Fenric Laboratory
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
	 */
	protected $name;

	/**
	 * Переменные представления
	 */
	protected $variables;

	/**
	 * Участки представления
	 */
	protected $sections;

	/**
	 * Макет представления
	 */
	protected $layout;

	/**
	 * Конструктор класса
	 */
	public function __construct(string $name, array $variables = [])
	{
		$this->name = $name;

		$this->variables = $variables;
	}

	/**
	 * Получение имени представления
	 */
	public function getName() : string
	{
		return $this->name;
	}

	/**
	 * Получение файла представления
	 */
	public function getFile() :? string
	{
		if (fenric()->path('views', $this->getName() . '.local.phtml')->isFile())
		{
			return fenric()->path('views', $this->getName() . '.local.phtml')->getRealPath();
		}

		if (fenric()->path('views', $this->getName() . '.phtml')->isFile())
		{
			return fenric()->path('views', $this->getName() . '.phtml')->getRealPath();
		}

		if (fenric()->path('views', $this->getName() . '.example.phtml')->isFile())
		{
			return fenric()->path('views', $this->getName() . '.example.phtml')->getRealPath();
		}

		return null;
	}

	/**
	 * Проверка существования представления
	 */
	public function exists() : bool
	{
		return !! $this->getFile();
	}

	/**
	 * Рендеринг представления
	 */
	public function render(array $variables = []) : string
	{
		$content = '';

		if ($file = $this->getFile())
		{
			$variables += $this->variables;

			ob_start();

			extract($variables, EXTR_SKIP | EXTR_REFS);

			include $file;

			$content = ob_get_clean();

			if ($this->layout instanceof self)
			{
				$this->layout->sections = $this->sections;

				$this->layout->sections['content'] = $content;

				$content = $this->layout->render();
			}
		}

		return $content;
	}

	/**
	 * Получение содержимого участка представления
	 */
	protected function section(string $section) :? string
	{
		return $this->sections[$section] ?? null;
	}

	/**
	 * Запись содержимого участка представления
	 */
	protected function start(string $section) : void
	{
		ob_start();

		$this->sections[$section] = null;
	}

	/**
	 * Сохранение содержимого участка представления
	 */
	protected function stop() : void
	{
		end($this->sections);

		$section = key($this->sections);

		reset($this->sections);

		$this->sections[$section] = ob_get_clean();
	}

	/**
	 * Получение экземпляра нового представления
	 */
	protected function make(string $name, array $variables = []) : self
	{
		return new self($name, $variables);
	}

	/**
	 * Получение нового отрендеренного представления
	 */
	protected function partial(string $name, array $variables = []) : string
	{
		return $this->make($name)->render($variables);
	}

	/**
	 * Установка макета представления
	 */
	protected function layout(string $name, array $variables = []) : void
	{
		$this->layout = $this->make($name, $variables);
	}
}
