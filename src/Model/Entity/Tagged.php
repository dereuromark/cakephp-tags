<?php
namespace Tags\Model\Entity;

use Cake\ORM\Entity;

/**
 * @property int $id
 * @property int $tag_id
 * @property int $fk_id
 * @property string $fk_model
 * @property \Cake\I18n\FrozenTime $created
 * @property \Cake\I18n\FrozenTime $modified
 * @property \Tags\Model\Entity\Tag $tags
 */
class Tagged extends Entity {

	/**
	 * List of properties that can be mass assigned.
	 *
	 * @var array
	 */
	protected $_accessible = [
		'id' => false,
		'*' => true,
	];

}
