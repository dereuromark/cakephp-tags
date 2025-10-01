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
 * @property \Cake\View\Helper\HtmlHelper $Html
 */
class TagHelper extends Helper {

	/**
	 * Other helpers to load
	 *
	 * @var array
	 */
	protected array $helpers = [
		'Form',
		'Html',
	];

	/**
	 * @var array<string, mixed>
	 */
	protected array $_defaultConfig = [
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

	/**
	 * Calculate contrast text color (black or white) for a given background color.
	 *
	 * @param string $bgColor Hex color code (e.g., '#FF5733')
	 * @return string Hex color code for text ('#000000' or '#ffffff')
	 */
	public function getContrastColor(string $bgColor): string {
		$hex = ltrim($bgColor, '#');
		$r = hexdec(substr($hex, 0, 2));
		$g = hexdec(substr($hex, 2, 2));
		$b = hexdec(substr($hex, 4, 2));

		// Calculate brightness using W3C formula
		$brightness = (($r * 299) + ($g * 587) + ($b * 114)) / 1000;

		return $brightness > 155 ? '#000000' : '#ffffff';
	}

	/**
	 * Render a single tag with color styling.
	 *
	 * @param object|array $tag Tag entity or array with 'label' and optional 'color'
	 * @param array<string, mixed> $options Options for rendering
	 * @return string HTML for colored tag
	 */
	public function tag($tag, array $options = []): string {
		$defaults = [
			'labelKey' => 'label',
			'colorKey' => 'color',
			'defaultColor' => '#cccccc',
			'class' => 'tag',
			'style' => [],
			'url' => null,
			'escape' => true,
		];
		$options += $defaults;

		$label = is_array($tag) ? $tag[$options['labelKey']] : $tag->{$options['labelKey']};
		$color = is_array($tag)
			? ($tag[$options['colorKey']] ?? $options['defaultColor'])
			: ($tag->{$options['colorKey']} ?? $options['defaultColor']);

		$textColor = $this->getContrastColor($color);

		$style = array_merge([
			'background-color' => $color,
			'color' => $textColor,
			'padding' => '4px 12px',
			'border-radius' => '4px',
			'display' => 'inline-block',
			'margin-right' => '4px',
			'margin-bottom' => '4px',
		], $options['style']);

		$styleString = implode('; ', array_map(
			fn ($k, $v) => "$k: $v",
			array_keys($style),
			$style,
		));

		$content = $options['escape'] ? h($label) : $label;

		if ($options['url']) {
			$content = $this->Html->link($content, $options['url'], [
				'escape' => false,
				'class' => $options['class'],
				'style' => $styleString,
			]);
		} else {
			$classAttr = $options['class'] ? ' class="' . h($options['class']) . '"' : '';
			$content = '<span' . $classAttr . ' style="' . $styleString . '">' . $content . '</span>';
		}

		return $content;
	}

	/**
	 * Render multiple tags with color styling.
	 *
	 * @param array $tags Array of tag entities/arrays
	 * @param array<string, mixed> $options Options for rendering
	 * @return string HTML for all tags
	 */
	public function tags(array $tags, array $options = []): string {
		if (empty($tags)) {
			return '';
		}

		$defaults = [
			'wrapper' => 'div',
			'wrapperClass' => 'tags-container',
		];
		$options += $defaults;

		$output = [];
		foreach ($tags as $tag) {
			$output[] = $this->tag($tag, $options);
		}

		$content = implode('', $output);

		if ($options['wrapper']) {
			$classAttr = $options['wrapperClass'] ? ' class="' . h($options['wrapperClass']) . '"' : '';
			$content = '<' . $options['wrapper'] . $classAttr . '>' . $content . '</' . $options['wrapper'] . '>';
		}

		return $content;
	}

	/**
	 * Render a color input field for tag color.
	 *
	 * @param string $fieldName Field name
	 * @param array<string, mixed> $options Options for the input
	 * @return string HTML for color input
	 */
	public function colorControl(string $fieldName = 'color', array $options = []): string {
		$defaults = [
			'type' => 'color',
			'label' => __d('tags', 'Tag Color'),
			'help' => __d('tags', 'Choose a color for this tag'),
			'default' => '#cccccc',
		];
		$options += $defaults;

		return $this->Form->control($fieldName, $options);
	}

	/**
	 * Render tags as badges (Bootstrap-compatible).
	 *
	 * @param array $tags Array of tag entities/arrays
	 * @param array<string, mixed> $options Options for rendering
	 * @return string HTML for badges
	 */
	public function badges(array $tags, array $options = []): string {
		$defaults = [
			'class' => 'badge',
			'style' => [
				'padding' => '0.35em 0.65em',
				'border-radius' => '0.25rem',
				'font-size' => '0.875em',
				'font-weight' => '500',
			],
		];
		$options = array_merge($defaults, $options);

		return $this->tags($tags, $options);
	}

	/**
	 * Render tags as pills (rounded badges).
	 *
	 * @param array $tags Array of tag entities/arrays
	 * @param array<string, mixed> $options Options for rendering
	 * @return string HTML for pills
	 */
	public function pills(array $tags, array $options = []): string {
		$defaults = [
			'class' => 'badge rounded-pill',
			'style' => [
				'padding' => '0.35em 0.65em',
				'border-radius' => '10rem',
			],
		];
		$options = array_merge($defaults, $options);

		return $this->tags($tags, $options);
	}

	/**
	 * Lighten or darken a color.
	 *
	 * @param string $color Hex color code
	 * @param int $percent Positive to lighten, negative to darken (-100 to 100)
	 * @return string Modified hex color code
	 */
	public function adjustBrightness(string $color, int $percent): string {
		$hex = ltrim($color, '#');
		$r = hexdec(substr($hex, 0, 2));
		$g = hexdec(substr($hex, 2, 2));
		$b = hexdec(substr($hex, 4, 2));

		$r = max(0, min(255, $r + ($r * $percent / 100)));
		$g = max(0, min(255, $g + ($g * $percent / 100)));
		$b = max(0, min(255, $b + ($b * $percent / 100)));

		return sprintf('#%02X%02X%02X', (int)$r, (int)$g, (int)$b);
	}

	/**
	 * Render a tag list with colors and optional links.
	 *
	 * @param array $tags Array of tag entities
	 * @param array<string, mixed> $options Options
	 * @return string HTML
	 */
	public function list(array $tags, array $options = []): string {
		$defaults = [
			'url' => null,
			'separator' => ' ',
			'beforeTag' => '',
			'afterTag' => '',
		];
		$options += $defaults;

		$output = [];
		foreach ($tags as $tag) {
			$url = null;
			if ($options['url']) {
				$slug = is_array($tag) ? $tag['slug'] : $tag->slug;
				$url = is_array($options['url'])
					? array_merge($options['url'], [$slug])
					: $options['url'] . '/' . $slug;
			}

			$output[] = $options['beforeTag']
				. $this->tag($tag, ['url' => $url] + $options)
				. $options['afterTag'];
		}

		return implode($options['separator'], $output);
	}

}
