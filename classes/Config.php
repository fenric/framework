<?php
/**
 * It is free open-source software released under the MIT License.
 *
 * @author       Anatoly Nekhay <a.fenric@gmail.com>
 * @copyright    Copyright (c) 2013-2016 by Fenric Laboratory
 * @license      http://fenric.ru/license/
 * @link         http://fenric.ru/
 */

namespace Fenric;

/**
 * Config
 */
class Config extends Collection
{

	/**
	 * Имя конфигурационной группы
	 *
	 * @var     string
	 * @access  protected
	 */
	protected $group;

	/**
	 * Конструктор класса
	 *
	 * @param   string   $group
	 *
	 * @access  public
	 * @return  void
	 */
	public function __construct($group)
	{
		$this->group = $group;

		$this->loadConfigurationGroup();
	}

	/**
	 * Поиск конфигурационной группы
	 *
	 * @access  protected
	 * @return  mixed
	 */
	protected function findConfigurationGroup()
	{
		$filename = fenric()->path('configs', $this->group);

		$extensions = ['php', 'ini', 'json', 'yml'];

		foreach ($extensions as $extension)
		{
			$pathname = $filename .'.'. $extension;

			if (is_file($pathname))
			{
				if (is_readable($pathname))
				{
					$foundConfigurationGroup = [];

					$foundConfigurationGroup['pathname'] = $pathname;

					$foundConfigurationGroup['extension'] = $extension;

					return $foundConfigurationGroup;
				}
			}
		}
	}

	/**
	 * Загрузка конфигурационной группы
	 *
	 * @access  protected
	 * @return  void
	 */
	protected function loadConfigurationGroup()
	{
		if ($group = $this->findConfigurationGroup())
		{
			if (strcmp($group['extension'], 'php') === 0)
			{
				$this->update(require $group['pathname']);
			}

			else if (strcmp($group['extension'], 'ini') === 0)
			{
				$source = file_get_contents($group['pathname']);

				if ($parameters = parse_ini_string($source, true))
				{
					$this->update($parameters);
				}
			}

			else if (strcmp($group['extension'], 'json') === 0)
			{
				$source = file_get_contents($group['pathname']);

				if ($parameters = json_decode($source, true))
				{
					$this->update($parameters);
				}
			}

			else if (strcmp($group['extension'], 'yml') === 0)
			{
				$source = file_get_contents($group['pathname']);

				if ($parameters = yaml_parse($source))
				{
					$this->update($parameters);
				}
			}
		}
	}
}
