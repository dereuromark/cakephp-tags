<?php
declare(strict_types=1);

namespace Tags\Controller\Admin;

use App\Controller\AppController;
use Cake\Controller\Controller;
use Cake\Core\Configure;

/**
 * TagsAppController
 *
 * Base controller for Tags admin.
 *
 * By default, extends AppController to inherit app authentication, components, and configuration.
 * Set `Tags.standalone` to `true` for an isolated admin that doesn't depend on the host app.
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

}
