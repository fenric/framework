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
