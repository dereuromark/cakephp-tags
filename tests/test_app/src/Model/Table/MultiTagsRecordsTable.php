<?php

namespace App\Model\Table;

use Cake\ORM\Table;

/**
 * @property \Tags\Model\Table\TaggedTable|\Cake\ORM\Association\HasMany $TaggedOne
 * @property \Tags\Model\Table\TagsTable|\Cake\ORM\Association\BelongsToMany $TagsOne
 * @property \Tags\Model\Table\TaggedTable|\Cake\ORM\Association\HasMany $TaggedTwo
 * @property \Tags\Model\Table\TagsTable|\Cake\ORM\Association\BelongsToMany $TagsTwo
 *
 * @mixin \Tags\Model\Behavior\TagBehavior
 */
class MultiTagsRecordsTable extends Table {

	/**
	 * @param array $config
	 * @return void
	 */
	public function initialize(array $config) {
		$this->addBehavior('TagsOne', [
			'className' => 'Tags.Tag',
			'fkModelAlias' => 'MultiTagsRecordsOne',
			'field' => 'one_list',
			'tagsAlias' => 'TagsOne',
			'taggedAlias' => 'TaggedOne',
			'taggedCounter' => false,
			'tagsAssoc' => [
				'propertyName' => 'one',
			],
			'implementedFinders' => [
			],
			'implementedMethods' => [
			],
		]);
		$this->addBehavior('TagsTwo', [
			'className' => 'Tags.Tag',
			'fkModelAlias' => 'MultiTagsRecordsTwo',
			'field' => 'two_list',
			'tagsAlias' => 'TagsTwo',
			'taggedAlias' => 'TaggedTwo',
			'taggedCounter' => false,
			'tagsAssoc' => [
				'propertyName' => 'two',
			],
			'implementedFinders' => [
			],
			'implementedMethods' => [
			],
		]);
	}

}
