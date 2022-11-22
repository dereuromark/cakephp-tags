<?php

namespace Tags\Model\Table;

use ArrayObject;
use Cake\Core\Configure;
use Cake\Event\EventInterface;
use Cake\ORM\Table;
use Cake\Utility\Text;
use Cake\Validation\Validator;
use RuntimeException;

/**
 * @method \Tags\Model\Entity\Tag get($primaryKey, $options = [])
 * @method \Tags\Model\Entity\Tag newEntity($data = null, array $options = [])
 * @method array<\Tags\Model\Entity\Tag> newEntities(array $data, array $options = [])
 * @method \Tags\Model\Entity\Tag|bool save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \Tags\Model\Entity\Tag patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method array<\Tags\Model\Entity\Tag> patchEntities($entities, array $data, array $options = [])
 * @method \Tags\Model\Entity\Tag findOrCreate($search, callable $callback = null, $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class TagsTable extends Table {

	/**
	 * Initialize table config.
	 *
	 * @param array $config Config options
	 * @throws \RuntimeException
	 * @return void
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

	/**
	 * @param \Cake\Event\EventInterface $event
	 * @param \ArrayObject $data
	 * @param \ArrayObject $options
	 *
	 * @return void
	 */
	public function beforeMarshal(EventInterface $event, ArrayObject $data, ArrayObject $options): void {
		if (isset($data['label']) && isset($data['slug']) && $data['slug'] === 0) {
			$data['slug'] = $this->slug($data['label']);
		}
	}

	/**
	 * @param string $label
	 *
	 * @return string
	 */
	protected function slug(string $label): string {
		$slug = Configure::read('Tags.slug');
		if ($slug) {
			if (!is_callable($slug)) {
				throw new RuntimeException('You must use a valid callable for custom slugging.');
			}

			return $slug($label);
		}

		return mb_strtolower(Text::slug($label));
	}

}
