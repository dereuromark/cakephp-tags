<?php
namespace Tags\Model\Table;

use ArrayObject;
use Cake\Collection\CollectionInterface;
use Cake\Event\Event;
use Cake\ORM\Query;
use Cake\ORM\Table;
use Cake\Utility\Hash;

/**
 * @property \Tags\Model\Table\TagsTable|\Cake\ORM\Association\BelongsTo $Tags
 *
 * @method \Tags\Model\Entity\Tagged get($primaryKey, $options = [])
 * @method \Tags\Model\Entity\Tagged newEntity($data = null, array $options = [])
 * @method \Tags\Model\Entity\Tagged[] newEntities(array $data, array $options = [])
 * @method \Tags\Model\Entity\Tagged|bool save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \Tags\Model\Entity\Tagged patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \Tags\Model\Entity\Tagged[] patchEntities($entities, array $data, array $options = [])
 * @method \Tags\Model\Entity\Tagged findOrCreate($search, callable $callback = null, $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class TaggedTable extends Table {

	/**
	 * Initialize table config.
	 *
	 * @param array $config Config options
	 * @return void
	 */
	public function initialize(array $config) {
		$this->setTable('tags_tagged');
		$this->belongsTo('Tags', [
			'className' => 'Tags.Tags',
			'foreignKey' => 'tag_id',
			'propertyName' => 'tag',
		]);
		$this->addBehavior('Timestamp');
	}

	/**
	 * Returns a tag cloud
	 *
	 * The result contains a "weight" field which has a normalized size of the tag
	 * counter set. The min and max size can be set by passing 'minSize" and
	 * 'maxSize' to the query. This value can be used in the view to control the
	 * size of the tag font.
	 *
	 * @param \Cake\ORM\Query $query Query array.
	 * @return \Cake\ORM\Query
	 */
	public function findCloud(Query $query) {
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
			//'page' => '',
			//'limit' => '',
			//'order' => '',
			//'joins' => array(),
			//'offset' => '',
			'contain' => 'Tags',
			//'conditions' => array(),
			'fields' => $fields,
			'group' => $groupBy
		];

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
	 * @param \Cake\Event\Event $event
	 * @param \Cake\ORM\Query $query
	 * @param \ArrayObject $options
	 * @param bool $primary
	 * @return \Cake\ORM\Query
	 */
	public function beforeFind(Event $event, Query $query, ArrayObject $options, $primary) {
		$order = $query->clause('order');
		if ($order !== null) {
			return $query;
		}

		if (!isset($this->order)) {
			$contain = $query->getContain();
			if (!isset($contain[$this->Tags->getAlias()])) {
				return $query;
			}

			$order = [$this->Tags->getAlias() . '.label' => 'ASC'];
		} else {
			$order = $this->order;
		}

		if (!empty($order)) {
			$query->order($order);
		}

		return $query;
	}

}
