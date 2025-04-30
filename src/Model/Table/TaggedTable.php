<?php

namespace Tags\Model\Table;

use ArrayObject;
use Cake\Collection\CollectionInterface;
use Cake\Event\EventInterface;
use Cake\ORM\Query\SelectQuery;
use Cake\ORM\Table;
use Cake\Utility\Hash;
use Cake\Validation\Validator;

/**
 * @property \Tags\Model\Table\TagsTable&\Cake\ORM\Association\BelongsTo $Tags
 *
 * @method \Tags\Model\Entity\Tagged get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \Tags\Model\Entity\Tagged newEntity(array $data, array $options = [])
 * @method array<\Tags\Model\Entity\Tagged> newEntities(array $data, array $options = [])
 * @method \Tags\Model\Entity\Tagged|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \Tags\Model\Entity\Tagged patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method array<\Tags\Model\Entity\Tagged> patchEntities(iterable $entities, array $data, array $options = [])
 * @method \Tags\Model\Entity\Tagged findOrCreate($search, ?callable $callback = null, array $options = [])
 * @method \Tags\Model\Entity\Tagged newEmptyEntity()
 * @method \Tags\Model\Entity\Tagged saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \Cake\Datasource\ResultSetInterface<\Tags\Model\Entity\Tagged>|false saveMany(iterable $entities, array $options = [])
 * @method \Cake\Datasource\ResultSetInterface<\Tags\Model\Entity\Tagged> saveManyOrFail(iterable $entities, array $options = [])
 * @method \Cake\Datasource\ResultSetInterface<\Tags\Model\Entity\Tagged>|false deleteMany(iterable $entities, array $options = [])
 * @method \Cake\Datasource\ResultSetInterface<\Tags\Model\Entity\Tagged> deleteManyOrFail(iterable $entities, array $options = [])
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class TaggedTable extends Table {

	/**
	 * Initialize table config.
	 *
	 * @param array $config Config options
	 * @return void
	 */
	public function initialize(array $config): void {
		$this->setTable('tags_tagged');
		$this->belongsTo('Tags', [
			'className' => 'Tags.Tags',
			'foreignKey' => 'tag_id',
			'propertyName' => 'tag',
		]);
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
			->notBlank('fk_model');

		$validator
			->notBlank('fk_id');

		$validator
			->notBlank('tag_id');

		return $validator;
	}

	/**
	 * Returns a tag cloud
	 *
	 * The result contains a "weight" field which has a normalized size of the tag
	 * counter set. The min and max size can be set by passing 'minSize" and
	 * 'maxSize' to the query. This value can be used in the view to control the
	 * size of the tag font.
	 *
	 * @param \Cake\ORM\Query\SelectQuery $query Query array.
	 * @return \Cake\ORM\Query\SelectQuery
	 */
	public function findCloud(SelectQuery $query) {
		$groupBy = ['Tagged.tag_id', 'Tags.id', 'Tags.slug', 'Tags.label'];
		$fields = $groupBy;
		$fields['counter'] = $query->func()->count('*');

		// FIXME or remove
		// Support old code without the counter cache
		// This is related to https://github.com/CakeDC/tags/issues/10 to work around a limitation of postgres
		/*
		$field = $this->getDataSource()->fields($this->Tag);
		$field = array_merge($field, $this->getDataSource()->fields($this, null, 'Tagged.tag_id'));
		$fields = 'DISTINCT ' . implode(',', $field);
		$groupBy = null;
		*/

		$options = [
			'minSize' => 10,
			'maxSize' => 20,
			'contain' => 'Tags',
			'fields' => $fields,
			'group' => $groupBy,
		];
		if ($query->clause('where') === null) {
			$query->where(['Tags.id IS NOT' => null]);
		}
		if ($query->clause('order') === null) {
			$query->orderbyAsc('Tags.label');
		}

		$query->formatResults(function (CollectionInterface $results) {
			$results = static::calculateWeights($results->toArray());

			return $results;
		});

		return $query->find('all', $options);
	}

	/**
	 * @param array $entities
	 * @param array $config
	 *
	 * @return array
	 */
	public static function calculateWeights(array $entities, array $config = []) {
		$config += [
			'minSize' => 10,
			'maxSize' => 20,
		];
		/** @var array $weights */
		$weights = Hash::extract($entities, '{n}.counter');
		if ($weights) {
			$maxWeight = max($weights);
			$minWeight = min($weights);
			$spread = $maxWeight - $minWeight;
			if ($spread === 0) {
				$spread = 1;
			}
			foreach ($entities as $key => $result) {
				$size = $config['minSize'] + (
						($result['counter'] - $minWeight) * (
							($config['maxSize'] - $config['minSize']) / ($spread)
						)
					);
				$entities[$key]['weight'] = ceil($size);
			}
		}

		return $entities;
	}

	/**
	 * Sets the default ordering.
	 *
	 * If you don't want that, don't call parent when overwriting it in extending classes
	 * or just set the order to an empty array. This will only trigger for order clause of null.
	 *
	 * @param \Cake\Event\EventInterface $event
	 * @param \Cake\ORM\Query\SelectQuery $query
	 * @param \ArrayObject $options
	 * @param bool $primary
	 * @return void
	 */
	public function beforeFind(EventInterface $event, SelectQuery $query, ArrayObject $options, $primary): void {
		$order = $query->clause('order');
		if ($order !== null) {
			return;
		}

		if (!isset($this->order)) {
			$contain = $query->getContain();
			if (!isset($contain[$this->Tags->getAlias()])) {
				return;
			}

			$order = [$this->Tags->getAlias() . '.label' => 'ASC'];
		} else {
			$order = $this->order;
		}

		if ($order) {
			$query = $query->orderBy($order);
		}

		$event->setResult($query);
	}

}
