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
 * @method \Tags\Model\Entity\Tag get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \Tags\Model\Entity\Tag newEntity(array $data, array $options = [])
 * @method array<\Tags\Model\Entity\Tag> newEntities(array $data, array $options = [])
 * @method \Tags\Model\Entity\Tag|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \Tags\Model\Entity\Tag patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method array<\Tags\Model\Entity\Tag> patchEntities(iterable $entities, array $data, array $options = [])
 * @method \Tags\Model\Entity\Tag findOrCreate($search, ?callable $callback = null, array $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 * @method \Tags\Model\Entity\Tag newEmptyEntity()
 * @method \Tags\Model\Entity\Tag saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \Cake\Datasource\ResultSetInterface<\Tags\Model\Entity\Tag>|false saveMany(iterable $entities, array $options = [])
 * @method \Cake\Datasource\ResultSetInterface<\Tags\Model\Entity\Tag> saveManyOrFail(iterable $entities, array $options = [])
 * @method \Cake\Datasource\ResultSetInterface<\Tags\Model\Entity\Tag>|false deleteMany(iterable $entities, array $options = [])
 * @method \Cake\Datasource\ResultSetInterface<\Tags\Model\Entity\Tag> deleteManyOrFail(iterable $entities, array $options = [])
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

		$validator
			->scalar('color')
			->maxLength('color', 7)
			->allowEmptyString('color')
			->add('color', 'hexColor', [
				'rule' => function ($value, $context) {
					if (empty($value)) {
						return true;
					}

					return (bool)preg_match('/^#[0-9A-Fa-f]{6}$/', $value);
				},
				'message' => __('Color must be a valid hex color (e.g., #FF5733)'),
			]);

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
