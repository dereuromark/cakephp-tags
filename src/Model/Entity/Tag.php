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
	 * @var array
	 */
	protected $_accessible = [
		'id' => false,
		'slug' => false,
		'counter' => false,
		'*' => true,
	];

}
