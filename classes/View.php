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
			extract($this->variables);

			ob_start();

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
	protected function section(string $sectionId) : ?string
	{
		return $this->sections[$sectionId] ?? null;
	}

	/**
	 * Запись содержимого участка представления
	 */
	protected function start(string $sectionId) : void
	{
		ob_start();

		$this->sections[$sectionId] = null;
	}

	/**
	 * Сохранение содержимого участка представления
	 */
	protected function stop() : void
	{
		end($this->sections);

		$sectionId = key($this->sections);

		$this->sections[$sectionId] = ob_get_clean();
	}

	/**
	 * Наследование макета представления
	 */
	protected function layout(string $name, array $variables = []) : void
	{
		$this->layout = new self($name, $variables);
	}

	/**
	 * Получение отрендеренного представления
	 */
	protected function partial(string $name, array $variables = []) : string
	{
		return (new self($name, $variables))->render();
	}
}
