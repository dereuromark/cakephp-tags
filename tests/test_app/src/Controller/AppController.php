<?php
declare(strict_types=1);

namespace App\Controller;

use Cake\Controller\Controller;
use Cake\Core\Configure;
use Cake\Event\EventInterface;

/**
 * Application Controller
 *
 * Stub for testing the Tags plugin admin backend.
 */
class AppController extends Controller {

	/**
	 * @return void
	 */
	public function initialize(): void {
		parent::initialize();

		$this->loadComponent('Flash');
	}

	/**
	 * @param \Cake\Event\EventInterface $event
	 * @return void
	 */
	public function beforeFilter(EventInterface $event): void {
		$callback = Configure::read('TestApp.beforeFilter');
		if ($callback) {
			$result = $callback($event);
			if ($result instanceof \Psr\Http\Message\ResponseInterface) {
				$event->setResult($result);
				$event->stopPropagation();
			}

			return;
		}

		parent::beforeFilter($event);
	}

}
