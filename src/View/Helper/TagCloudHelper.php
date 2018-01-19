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

use Cake\Utility\Hash;
use Cake\View\Helper;
use Cake\View\StringTemplateTrait;

/**
 * Tag cloud helper
 *
 * @property \Cake\View\Helper\HtmlHelper $Html
 */
class TagCloudHelper extends Helper {

	use StringTemplateTrait;

	/**
	 * @var array
	 */
	public $helpers = [
		'Html'
	];

	/**
	 * @var array
	 */
	protected $_defaultConfig = [
		'tagModel' => 'tag',
		'shuffle' => true,
		'extract' => '{n}.weight',
		'maxSize' => 160,
		'minSize' => 80,
		'url' => [
		],
		'named' => 'by',
		'templates' => [
			'wrapper' => '<ul{{attrs}}>{{content}}</ul>',
			'item' => '<li style="font-size: {{size}}%">{{content}}</li>'
		],
	];

	/**
	 * Method to output a tag-cloud formatted based on the weight of the tags
	 *
	 * Valid option keys are:
	 *  - shuffle: true to shuffle the tag list, false to display them in the same order than passed [default: true]
	 *  - extract: Hash::extract() compatible format string. Path to extract weight values from the $tags array
	 *      [default: {n}.weight]
	 *  - templates: Set your wrapper and item (usually ul/li elements). {{size}} will be replaced with tag size calculated
	 *      from the weight
	 *  - maxSize: size of the heaviest tag [default: 160]
	 *  - minSize: size of the lightest tag [default: 80]
	 *  - url: an array containing the default url
	 *  - named: the named parameter used to send the tag [default: by].
	 *
	 * @param array $tags Tag array to display.
	 * @param array $options Display options.
	 * @param array $attrs For ul element
	 * @return string
	 */
	public function display(array $tags, array $options = [], array $attrs = []) {
		if (empty($tags)) {
			return '';
		}
		$options += $this->_config;

		$tags = $this->calculateWeights($tags);

		$weights = Hash::extract($tags, $options['extract']);
		$maxWeight = max($weights);
		$minWeight = min($weights);

		// find the range of values
		$spread = $maxWeight - $minWeight;
		if ($spread == 0) {
			$spread = 1;
		}

		if ($options['shuffle'] == true) {
			shuffle($tags);
		}

		$cloud = [];
		foreach ($tags as $tag) {
			$tagWeight = $tag['weight'];

			$size = $options['minSize'] + (
				($tagWeight - $minWeight) * (
					($options['maxSize'] - $options['minSize']) / $spread
				)
			);
			$size = $tag['size'] = ceil($size);

			$content = $this->Html->link(
				$tag[$options['tagModel']]['label'],
				$this->_tagUrl($tag, $options),
				['id' => 'tag-' . $tag[$options['tagModel']]['id']]
			);
			$data = compact('size', 'content');
			$cloud[] = $this->templater()->format('item', $data);
		}

		$content = implode(PHP_EOL, $cloud);
		$attrs = $this->templater()->formatAttributes($attrs);
		$data = compact('attrs', 'content');

		return $this->templater()->format('wrapper', $data);
	}

	/**
	 * @param array $entities
	 * @param array $config
	 *
	 * @return array
	 */
	public function calculateWeights(array $entities, array $config = []) {
		$config += [
			'minSize' => 10,
			'maxSize' => 20,
		];

		$weights = Hash::extract($entities, '{n}.counter');
		$maxWeight = max($weights);
		$minWeight = min($weights);

		$spread = $maxWeight - $minWeight;
		if ($spread == 0) {
			$spread = 1;
		}

		foreach ($entities as $key => $result) {
			$size = $config['minSize'] + (
					($result['counter'] - $minWeight) * (
						($config['maxSize'] - $config['minSize']) / ($spread)
					)
				);
			$entities[$key]['weight'] = ceil($size);
		}

		return $entities;
	}

	/**
	 * Generates the URL for a tag
	 *
	 * @param array $tag Tag to generate URL for.
	 * @param array $options Tag options.
	 * @return array|string Tag URL.
	 */
	protected function _tagUrl($tag, $options) {
		$options['url'][$options['named']] = $tag[$options['tagModel']]['slug'];

		return $options['url'];
	}

}
