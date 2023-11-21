<?php

namespace Tags\Model\Entity;

use Cake\ORM\Entity;

/**
 * @property int $id
 * @property int $tag_id
 * @property int $fk_id
 * @property string $fk_model
 * @property \Cake\I18n\DateTime $created
 * @property \Cake\I18n\DateTime $modified
 * @property array<\Tags\Model\Entity\Tag> $tags
 * @property \Tags\Model\Entity\Tag $tag
 */
class Tagged extends Entity {

	/**
	 * List of properties that can be mass assigned.
	 *
	 * @var array<string, bool>
	 */
	protected array $_accessible = [
		'id' => false,
		'*' => true,
	];

}
