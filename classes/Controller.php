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
 * Controller
 */
abstract class Controller
{

	/**
	 * Request object
	 */
	protected $request;

	/**
	 * Response object
	 */
	protected $response;

	/**
	 * Constructor of the class
	 */
	final public function __construct(Request $request, Response $response)
	{
		$this->request = $request;
		$this->response = $response;
	}

	/**
	 * Gets Request object
	 */
	final public function getRequest() : Request
	{
		return $this->request;
	}

	/**
	 * Gets Response object
	 */
	final public function getResponse() : Response
	{
		return $this->response;
	}

	/**
	 * Pre-inits of the controller
	 */
	public function preInit() : bool
	{
		return true;
	}

	/**
	 * Inits of the controller
	 */
	public function init() : void
	{}

	/**
	 * Renders of the controller
	 */
	abstract public function render() : void;
}
