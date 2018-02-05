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
	 * Gets the request object
	 */
	final public function getRequest() : Request
	{
		return $this->request;
	}

	/**
	 * Gets the response object
	 */
	final public function getResponse() : Response
	{
		return $this->response;
	}

	/**
	 * Pre-initializes of the controller
	 */
	public function preInit() : bool
	{
		return true;
	}

	/**
	 * Initializes of the controller
	 */
	public function init() : void
	{}

	/**
	 * Renders of the controller
	 */
	abstract public function render() : void;
}
