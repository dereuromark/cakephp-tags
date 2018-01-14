<?php
/**
 * Copyright 2009-2014, Cake Development Corporation (http://cakedc.com)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright 2009-2014, Cake Development Corporation (http://cakedc.com)
 * @license MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

namespace Tags\View\Helper;

use Cake\View\Helper;

class TagHelper extends Helper {

	/**
	 * Other helpers to load
	 *
	 * @var array
	 */
	public $helpers = [
		'Html',
		'Form',
	];

	/**
	 * @param array $options
	 *
	 * @return string
	 */
	public function control(array $options = []) {
		return $this->Form->control('tag_list');
	}

}
