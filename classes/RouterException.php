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
 * Import classes
 */
use RuntimeException;

/**
 * RouterException
 */
class RouterException extends RuntimeException
{

	/**
	 * Конструктор класса
	 *
	 * @param   int     $code
	 *
	 * @access  public
	 * @return  void
	 */
	public function __construct($code = 404)
	{
		parent::__construct('The route is not defined.', $code);
	}
}
