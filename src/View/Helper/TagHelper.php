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
	protected $helpers = [
		'Form',
	];

	/**
	 * @var array<string, mixed>
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
	 * @param array<string, mixed> $options
	 *
	 * @return string
	 */
	public function control(array $options = []) {
		$options += $this->getConfig();
		$field = $options['field'];

		if ($options['strategy'] === 'array') {
			$tags = (array)$this->Form->getSourceValue($field);

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

		unset($options['field']);
		unset($options['strategy']);

		return $this->Form->control($field, $options);
	}

}
