<?php

namespace Tags\Model\Entity;

use Cake\ORM\Entity;

/**
 * @property int $id
 * @property string|null $namespace
 * @property string $slug
 * @property string $label
 * @property string|null $color
 * @property int $counter
 * @property \Cake\I18n\DateTime $created
 * @property \Cake\I18n\DateTime $modified
 */
class Tag extends Entity {

	/**
	 * List of properties that can be mass assigned.
	 *
	 * @var array<string, bool>
	 */
	protected array $_accessible = [
		'id' => false,
		'counter' => false,
		'*' => true,
	];

}
