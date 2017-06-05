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
	public function getFile() : ?string
	{
		if (file_exists(fenric()->path('views', $this->getName() . '.local.phtml')))
		{
			return fenric()->path('views', $this->getName() . '.local.phtml');
		}

		if (fenric()->is('test'))
		{
			if (file_exists(fenric()->path('views', $this->getName() . '.test.phtml')))
			{
				return fenric()->path('views', $this->getName() . '.test.phtml');
			}
		}

		if (fenric()->is('production'))
		{
			if (file_exists(fenric()->path('views', $this->getName() . '.production.phtml')))
			{
				return fenric()->path('views', $this->getName() . '.production.phtml');
			}
		}

		if (fenric()->is('development'))
		{
			if (file_exists(fenric()->path('views', $this->getName() . '.development.phtml')))
			{
				return fenric()->path('views', $this->getName() . '.development.phtml');
			}
		}

		if (file_exists(fenric()->path('views', $this->getName() . '.phtml')))
		{
			return fenric()->path('views', $this->getName() . '.phtml');
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
	protected function section(string $id) : ?string
	{
		return $this->sections[$id] ?? null;
	}

	/**
	 * Запись содержимого участка представления
	 */
	protected function start(string $id) : void
	{
		ob_start();

		$this->sections[$id] = null;
	}

	/**
	 * Сохранение содержимого участка представления
	 */
	protected function stop() : void
	{
		end($this->sections);

		$id = key($this->sections);

		$this->sections[$id] = ob_get_clean();
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
		return $this->make($name, $variables)->render();
	}

	/**
	 * Установка макета представления
	 */
	protected function layout(string $name, array $variables = []) : void
	{
		$this->layout = $this->make($name, $variables);
	}
}
