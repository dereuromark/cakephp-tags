<?php

namespace Tags\Model\Entity;

use Cake\ORM\Entity;

/**
 * @property int $id
 * @property string $namespace
 * @property string $slug
 * @property string $label
 * @property int $counter
 * @property \Cake\I18n\FrozenTime $created
 * @property \Cake\I18n\FrozenTime $modified
 */
class Tag extends Entity {

	/**
	 * List of properties that can be mass assigned.
	 *
	 * @var array<string, bool>
	 */
	protected $_accessible = [
		'id' => false,
		'counter' => false,
		'*' => true,
	];

}
