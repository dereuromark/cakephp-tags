<?php

namespace Tags\Model\Table;

use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * @method \Tags\Model\Entity\Tag get($primaryKey, $options = [])
 * @method \Tags\Model\Entity\Tag newEntity($data = null, array $options = [])
 * @method \Tags\Model\Entity\Tag[] newEntities(array $data, array $options = [])
 * @method \Tags\Model\Entity\Tag|bool save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \Tags\Model\Entity\Tag patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \Tags\Model\Entity\Tag[] patchEntities($entities, array $data, array $options = [])
 * @method \Tags\Model\Entity\Tag findOrCreate($search, callable $callback = null, $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class TagsTable extends Table {

	/**
	 * Initialize table config.
	 *
	 * @param array $config Config options
	 * @return void
	 * @throws \RuntimeException
	 */
	public function initialize(array $config): void {
		$this->setTable('tags_tags');
		$this->setDisplayField('label'); // Change to name?
		$this->addBehavior('Timestamp');
	}

	/**
	 * Default validation rules.
	 *
	 * @param \Cake\Validation\Validator $validator Validator instance.
	 * @return \Cake\Validation\Validator
	 */
	public function validationDefault(Validator $validator): Validator {
		$validator
			->scalar('id')
			->allowEmptyString('id', 'create');

		$validator
			->notBlank('slug')
			->add('slug', 'isUnique', [
				'rule' => ['validateUnique', ['scope' => 'namespace']],
				'message' => __('Already exists'),
				'provider' => 'table',
			]);

		$validator
			->notBlank('label');

		return $validator;
	}

}
