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
	 * @return mixed
	 */
	public function beforeFilter(EventInterface $event) {
		$callback = Configure::read('TestApp.beforeFilter');
		if ($callback) {
			return $callback($event);
		}

		return parent::beforeFilter($event);
	}

}
