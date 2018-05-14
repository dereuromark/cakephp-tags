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

use Cake\Core\Configure;
use Cake\View\Helper;
use Cake\View\View;

/**
 * @property \Cake\View\Helper\FormHelper $Form
 */
class TagHelper extends Helper {

	/**
	 * Other helpers to load
	 *
	 * @var array
	 */
	public $helpers = [
		'Form',
	];

	/**
	 * @var array
	 */
	protected $_defaultConfig = [
		'field' => 'tag_list',
		'strategy' => 'string',
	];

	/**
	 * @param \Cake\View\View $View The View this helper is being attached to.
	 * @param array $config Configuration settings for the helper.
	 */
	public function __construct(View $View, array $config = []) {
		$this->_defaultConfig = (array)Configure::read('Tags') + $this->_defaultConfig;

		parent::__construct($View, $config);
	}

	/**
	 * Convenience method for tag list form input.
	 *
	 * @param array $options
	 *
	 * @return string
	 */
	public function control(array $options = []) {
		if ($this->getConfig('strategy') === 'array') {
			$tags = (array)$this->Form->getSourceValue($this->getConfig('field'));

			$options += [
				'options' => array_combine($tags, $tags),
				'val' => $tags,
				'type' => 'select',
				'multiple' => true,
			];
		}

		$options += [
			'label' => __d('tags', 'Tags'),
		];

		return $this->Form->control($this->getConfig('field'), $options);
	}

}
