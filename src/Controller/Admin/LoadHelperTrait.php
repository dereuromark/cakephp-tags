<?php
declare(strict_types=1);

namespace Tags\Controller\Admin;

use Cake\Core\Plugin;

trait LoadHelperTrait {

	/**
	 * @return void
	 */
	protected function loadHelpers(): void {
		$helpers = [];

		// Time helper: prefer Tools, fallback to core
		if (Plugin::isLoaded('Tools')) {
			$helpers[] = 'Tools.Time';
			$helpers[] = 'Tools.Text';
			$helpers[] = 'Tools.Format';
		} else {
			$helpers[] = 'Time';
			$helpers[] = 'Text';
		}

		$helpers[] = 'Tags.Tag';

		$this->viewBuilder()->addHelpers($helpers);
	}

}
