<?php
declare(strict_types=1);

namespace Tags\Controller\Admin;

use App\Controller\AppController;
use Cake\Controller\Controller;
use Cake\Core\Configure;
use Cake\Event\EventInterface;
use Cake\Http\Exception\ForbiddenException;
use Cake\Log\Log;
use Closure;
use Throwable;

/**
 * TagsAppController
 *
 * Base controller for Tags admin.
 *
 * By default, extends AppController to inherit app authentication, components, and configuration.
 * Set `Tags.standalone` to `true` for an isolated admin that doesn't depend on the host app.
 *
 * Authorization: an optional defense-in-depth gate is available via
 * `Tags.accessCheck`. When set to a `Closure`, it must return literal `true`
 * for the current request to proceed; anything else (returns false, returns
 * truthy non-bool, throws) yields a 403. Unset = no-op (the host
 * AppController's auth is the only gate). See docs/README.md for examples.
 */
class TagsAppController extends AppController {

	use LoadHelperTrait;

	/**
	 * @return void
	 */
	public function initialize(): void {
		if (Configure::read('Tags.standalone')) {
			// Standalone mode: skip app's AppController, initialize independently
			Controller::initialize();
			$this->loadComponent('Flash');
		} else {
			// Default: inherit app's full controller setup
			parent::initialize();
		}

		$this->loadHelpers();

		// Layout configuration:
		// - null (default): Uses 'Tags.tags' isolated Bootstrap 5 layout
		// - false: Disables plugin layout, uses app's default layout
		// - string: Uses specified layout (e.g., 'Tags.tags' or custom)
		$layout = Configure::read('Tags.adminLayout');
		if ($layout !== false) {
			$this->viewBuilder()->setLayout($layout ?: 'Tags.tags');
		}
	}

	/**
	 * Optional defense-in-depth access gate. Unset = no-op.
	 *
	 * @param \Cake\Event\EventInterface<\Cake\Controller\Controller> $event
	 * @throws \Cake\Http\Exception\ForbiddenException When the configured Closure rejects the request.
	 * @return void
	 */
	public function beforeFilter(EventInterface $event): void {
		parent::beforeFilter($event);

		$check = Configure::read('Tags.accessCheck');
		if ($check === null) {
			return;
		}
		if (!($check instanceof Closure)) {
			throw new ForbiddenException('Tags.accessCheck must be a Closure');
		}

		// Coexist with cakephp/authorization: this gate IS the authorization
		// decision for the Tags admin, so silence the policy check.
		if ($this->components()->has('Authorization') && method_exists($this->components()->get('Authorization'), 'skipAuthorization')) {
			$this->components()->get('Authorization')->skipAuthorization();
		}

		try {
			$allowed = $check($this->request) === true;
		} catch (ForbiddenException $e) {
			throw $e;
		} catch (Throwable $e) {
			Log::warning(sprintf('Tags.accessCheck threw %s: %s', $e::class, $e->getMessage()));

			throw new ForbiddenException('Tags admin access denied');
		}

		if (!$allowed) {
			throw new ForbiddenException('Tags admin access denied');
		}
	}

}
