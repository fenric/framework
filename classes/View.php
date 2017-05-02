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
	public function getFile() : string
	{
		return fenric()->path('views', $this->getName() . '.phtml');
	}

	/**
	 * Проверка существования представления
	 */
	public function exists() : bool
	{
		return file_exists($this->getFile());
	}

	/**
	 * Рендеринг представления
	 */
	public function render() : string
	{
		$content = '';

		if ($this->exists())
		{
			ob_start();

			extract($this->variables);

			include $this->getFile();

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
	 * Наследование макета представления
	 */
	protected function layout(string $name, array $variables = []) : void
	{
		$this->layout = $this->make($name, $variables);
	}

	/**
	 * Получение отрендеренного представления
	 */
	protected function partial(string $name, array $variables = []) : string
	{
		return $this->make($name, $variables)->render();
	}
}
