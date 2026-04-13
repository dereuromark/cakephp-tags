<?php
declare(strict_types=1);

namespace TestApp;

use Cake\Http\BaseApplication;
use Cake\Http\MiddlewareQueue;
use Cake\Routing\Middleware\RoutingMiddleware;
use Tags\TagsPlugin;
use Tools\ToolsPlugin;

class Application extends BaseApplication {

	/**
	 * @param string $configDir Config directory.
	 */
	public function __construct(string $configDir) {
		parent::__construct($configDir);

		$this->addPlugin(new TagsPlugin());
		$this->addPlugin(new ToolsPlugin());
	}

	/**
	 * @return void
	 */
	public function bootstrap(): void {
	}

	/**
	 * @param \Cake\Http\MiddlewareQueue $middlewareQueue Middleware queue.
	 * @return \Cake\Http\MiddlewareQueue
	 */
	public function middleware(MiddlewareQueue $middlewareQueue): MiddlewareQueue {
		return $middlewareQueue->add(new RoutingMiddleware($this));
	}

}
