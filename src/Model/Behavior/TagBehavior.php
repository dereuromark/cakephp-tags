<?php

namespace Tags\Model\Behavior;

use ArrayObject;
use Cake\Core\Configure;
use Cake\Datasource\EntityInterface;
use Cake\Datasource\QueryInterface;
use Cake\Event\EventInterface;
use Cake\ORM\Behavior;
use Cake\ORM\Entity;
use Cake\ORM\Query;
use Cake\ORM\Table;
use Cake\Utility\Text;
use RuntimeException;

class TagBehavior extends Behavior {

	/**
	 * Configuration.
	 *
	 * @var array<string, mixed>
	 */
	protected $_defaultConfig = [
		'field' => 'tag_list',
		'strategy' => 'string',
		'delimiter' => ',',
		'separator' => null,
		'namespace' => null,
		'tagsAlias' => 'Tags',
		'tagsAssoc' => [
			'className' => 'Tags.Tags',
			'joinTable' => 'tags_tagged',
			'foreignKey' => 'fk_id',
			'targetForeignKey' => 'tag_id',
			'propertyName' => 'tags',
		],
		'tagsCounter' => ['counter'],
		'taggedAlias' => 'Tagged',
		'taggedAssoc' => [
			'className' => 'Tags.Tagged',
		],
		'taggedCounter' => [
			'tag_count' => [
				'conditions' => [
				],
			],
		],
		'implementedEvents' => [
			'Model.beforeMarshal' => 'beforeMarshal',
			'Model.beforeFind' => 'beforeFind',
			'Model.beforeSave' => 'beforeSave',
		],
		'implementedMethods' => [
			'normalizeTags' => 'normalizeTags',
		],
		'implementedFinders' => [
			'tagged' => 'findByTag',
			'untagged' => 'findUntagged',
		],
		'finderField' => null, // Set to a specific field, e.g. `tag` for using tag name, defaults to `slug`
		'andSeparator' => '+', // For tagged finder - or e.g. &
		'orSeparator' => ',', // For tagged finder - or e.g. |
		'fkModelField' => 'fk_model',
		'fkModelAlias' => null,
		'slug' => null, // Slugging mechanism, defaults to core internal way
	];

	/**
	 * Merges config with the default and store in the config property
	 *
	 * @param \Cake\ORM\Table $table The table this behavior is attached to.
	 * @param array $config The config for this behavior.
	 */
	public function __construct(Table $table, array $config = []) {
		$this->_defaultConfig = (array)Configure::read('Tags') + $this->_defaultConfig;

		parent::__construct($table, $config);
	}

	/**
	 * Initialize configuration.
	 *
	 * @param array $config Configuration array.
	 * @return void
	 */
	public function initialize(array $config): void {
		$this->bindAssociations();
		$this->attachCounters();
	}

	/**
	 * Return lists of event's this behavior is interested in.
	 *
	 * @return array Events list.
	 */
	public function implementedEvents(): array {
		return $this->getConfig('implementedEvents');
	}

	/**
	 * Before marshal callback
	 *
	 * @param \Cake\Event\EventInterface $event The Model.beforeMarshal event.
	 * @param \ArrayObject $data Data.
	 * @param \ArrayObject $options Options.
	 * @throws \RuntimeException
	 * @return void
	 */
	public function beforeMarshal(EventInterface $event, ArrayObject $data, ArrayObject $options) {
		$field = $this->getConfig('field');
		$property = $this->getConfig('tagsAssoc.propertyName');
		$options['accessibleFields'][$property] = true;
		$options['associated'][$this->getConfig('tagsAlias')]['accessibleFields']['id'] = true;

		if (isset($data[$field])) {
			$data[$property] = $this->normalizeTags($data[$field]);
		} elseif ($field !== $property) {
			if (isset($data[$property]) && !is_array($data[$property])) {
				throw new RuntimeException('Your `' . $property . '` property is malformed (expected array instead of string). You configured to save list of tags in `' . $field . '` field.');
			}
		}

		if (isset($data[$field]) && empty($data[$field])) {
			unset($data[$field]);
		}
	}

	/**
	 * Modifies the entity before it is saved so that translated fields are persisted
	 * in the database too.
	 *
	 * @param \Cake\Event\EventInterface $event The beforeSave event that was fired
	 * @param \Cake\Datasource\EntityInterface $entity The entity that is going to be saved
	 * @param \ArrayObject $options the options passed to the save method
	 *
	 * @return void
	 */
	public function beforeSave(EventInterface $event, EntityInterface $entity, ArrayObject $options) {
		if (empty($entity->tags)) {
			return;
		}

		/**
		 * @var \Tags\Model\Entity\Tag $tag
		 */
		foreach ($entity->tags as $k => $tag) {
			if (!$tag->isNew()) {
				continue;
			}

			$existing = $this->_tagExists($tag->slug);
			if (!$existing) {
				continue;
			}

			$joinData = $tag->_joinData;
			$tag = $existing;
			$tag->_joinData = $joinData;
			$entity->tags[$k] = $tag;
		}
	}

	/**
	 * @param \Cake\Event\EventInterface $event
	 * @param \Cake\ORM\Query $query
	 * @param \ArrayObject $options
	 * @return \Cake\ORM\Query
	 */
	public function beforeFind(EventInterface $event, Query $query, ArrayObject $options) {
		$query->formatResults(function ($results) {
			/** @var \Cake\Collection\CollectionInterface $results */
			return $results->map(function ($row) {
				$field = $this->getConfig('field');
				$property = $this->getConfig('tagsAssoc.propertyName');

				if (!$row instanceof Entity && !isset($row[$property])) {
					return $row;
				}

				$row[$field] = $this->prepareTagsForOutput((array)$row[$property]);
				if ($row instanceof Entity) {
					$row->setDirty($field, false);
				}

				return $row;
			});
		});

		return $query;
	}

	/**
	 * Generates comma-delimited string of tag names from tag array(), needed for
	 * initialization of data for text input
	 *
	 * @param array $data Tag data array to convert to string.
	 * @return array|string
	 */
	public function prepareTagsForOutput(array $data) {
		$tags = [];

		foreach ($data as $tag) {
			if ($this->_config['namespace'] && $this->_config['separator'] !== null) {
				$tags[] = $tag['namespace'] . $this->_config['separator'] . $tag['label'];
			} else {
				$tags[] = $tag['label'];
			}
		}

		if ($this->_config['strategy'] === 'array') {
			return $tags;
		}

		return implode($this->_config['delimiter'] . ' ', $tags);
	}

	/**
	 * Binds all required associations if an association of the same name has
	 * not already been configured.
	 *
	 * @return void
	 */
	public function bindAssociations() {
		$config = $this->getConfig();
		$tagsAlias = $config['tagsAlias'];
		$tagsAssoc = $config['tagsAssoc'];
		$taggedAlias = $config['taggedAlias'];
		$taggedAssoc = $config['taggedAssoc'];

		$table = $this->_table;
		$tableAlias = $this->_table->getAlias();

		$modelAlias = $this->getConfig('fkModelAlias') ?: $tableAlias;
		$assocConditions = [$taggedAlias . '.' . $this->getConfig('fkModelField') => $modelAlias];

		if (!$table->hasAssociation($taggedAlias)) {
			$table->hasMany($taggedAlias, $taggedAssoc + [
				'foreignKey' => $tagsAssoc['foreignKey'],
				'conditions' => $assocConditions,
			]);
		}

		if (!$table->hasAssociation($tagsAlias)) {
			$table->belongsToMany($tagsAlias, $tagsAssoc + [
				'through' => $table->{$taggedAlias}->getTarget(),
				'conditions' => $assocConditions,
			]);
		}

		if (!$table->{$tagsAlias}->hasAssociation($tableAlias)) {
			$table->{$tagsAlias}
				->belongsToMany($tableAlias, [
					'className' => get_class($table),
				] + $tagsAssoc);
		}

		if (!$table->{$taggedAlias}->hasAssociation($tableAlias)) {
			$table->{$taggedAlias}
				->belongsTo($tableAlias, [
					'className' => get_class($table),
					'foreignKey' => $tagsAssoc['foreignKey'],
					'conditions' => $assocConditions,
					'joinType' => 'INNER',
				]);
		}

		if (!$table->{$taggedAlias}->hasAssociation($tableAlias . $tagsAlias)) {
			$table->{$taggedAlias}
				->belongsTo($tableAlias . $tagsAlias, [
					'className' => $tagsAssoc['className'],
					'foreignKey' => $tagsAssoc['targetForeignKey'],
					'conditions' => $assocConditions,
					'joinType' => 'INNER',
				]);
		}
	}

	/**
	 * Attaches the `CounterCache` behavior to the `Tagged` table to keep counts
	 * on both the `Tags` and the tagged entities.
	 *
	 * @throws \RuntimeException If configured counter cache field does not exist in table.
	 * @return void
	 */
	public function attachCounters() {
		$config = $this->getConfig();
		$tagsAlias = $config['tagsAlias'];
		$taggedAlias = $config['taggedAlias'];

		$taggedTable = $this->_table->{$taggedAlias};

		if (!$taggedTable->hasBehavior('CounterCache')) {
			$taggedTable->addBehavior('CounterCache');
		}

		/** @var \Cake\ORM\Behavior\CounterCacheBehavior $counterCache */
		$counterCache = $taggedTable->behaviors()->CounterCache;

		if (!$counterCache->getConfig($tagsAlias)) {
			$counterCache->setConfig($tagsAlias, $config['tagsCounter']);
		}

		if (!$config['taggedCounter']) {
			return;
		}

		$taggedCounterConfig = $this->_getTaggedCounterConfig($config['taggedCounter']);

		foreach ($taggedCounterConfig as $field => $o) {
			if (!$this->_table->hasField($field)) {
				throw new RuntimeException(sprintf(
					'Field "%s" does not exist in table "%s"',
					$field,
					$this->_table->getTable(),
				));
			}

			$modelAlias = $config['fkModelAlias'] ?: $this->_table->getAlias();
			$taggedCounterConfig[$field]['conditions'] = [
				$taggedTable->aliasField($this->getConfig('fkModelField')) => $modelAlias,
			];
		}
		if (!$counterCache->getConfig($this->_table->getAlias())) {
			$counterCache->setConfig($this->_table->getAlias(), $taggedCounterConfig);
		}
	}

	/**
	 * @param array|string $config
	 * @return array
	 */
	protected function _getTaggedCounterConfig($config) {
		if (!is_array($config)) {
			return [$config => ['conditions' => []]];
		}

		return $config;
	}

	/**
	 * Customer finder method using slug/tag lookup.
	 *
	 * It accepts both string or array (multiple strings) for the slug/tag value(s).
	 *
	 * {finderField} via config can be either 'slug' or 'label' of Tags table. Defaults to slug.
	 *
	 * Usage:
	 *   $query->find('tagged', ['{finderField}' => 'example-tag']);
	 * or:
	 *   $query->find('tagged', ['{finderField}' => ['one', 'two']);
	 *
	 * @param \Cake\ORM\Query $query
	 * @param array<string, mixed> $options
	 * @throws \RuntimeException
	 * @return \Cake\ORM\Query
	 */
	public function findByTag(Query $query, array $options) {
		$finderField = $optionsKey = $this->getConfig('finderField');
		if (!$finderField) {
			$finderField = $optionsKey = 'slug';
		}

		if (!isset($options[$optionsKey])) {
			throw new RuntimeException(sprintf('Expected key `%s` not present in find(\'tagged\') options argument.', $optionsKey));
		}
		$filterValue = $options[$optionsKey];
		if (!$filterValue) {
			return $query;
		}

		$subQuery = $this->buildQuerySnippet($filterValue, $finderField);
		if (is_string($subQuery)) {
			$query->matching($this->getConfig('tagsAlias'), function (QueryInterface $q) use ($finderField, $subQuery) {
				$key = $this->getConfig('tagsAlias') . '.' . $finderField;

				return $q->where([
					$key => $subQuery,
				]);
			});

			return $query;
		}

		$modelAlias = $this->getConfig('fkModelAlias') ?: $this->_table->getAlias();

		return $query->where([$modelAlias . '.id IN' => $subQuery]);
	}

	/**
	 * Customer finder method.
	 *
	 * Usage:
	 *   $query->find('untagged');
	 *
	 * Define a field if you have multiple counter cache fields set up:
	 *   $query->find('untagged', ['counterField' => 'my_tag_count']);
	 * Otherwise it will fallback to the first in the list.
	 *
	 * Set 'counterField' to false to do a live lookup in the pivot table.
	 * It will automatically do the live lookup if you do not have any counter cache fields.
	 *
	 * @param \Cake\ORM\Query $query
	 * @param array<string, mixed> $options
	 * @return \Cake\ORM\Query
	 */
	public function findUntagged(Query $query, array $options) {
		$taggedCounters = $this->getConfig('taggedCounter') ? array_keys($this->_getTaggedCounterConfig($this->getConfig('taggedCounter'))) : [];
		$options += [
			'counterField' => $taggedCounters ? reset($taggedCounters) : null,
		];

		$modelAlias = $this->getConfig('fkModelAlias') ?: $this->_table->getAlias();
		if ($options['counterField']) {
			return $query->where([$this->_table->getAlias() . '.' . $options['counterField'] => 0]);
		}

		$foreignKey = $this->getConfig('tagsAssoc.foreignKey');
		$conditions = [$this->getConfig('fkModelField') => $modelAlias];
		$this->_table->hasOne('NoTags', ['className' => $this->getConfig('taggedAssoc.className'), 'foreignKey' => $foreignKey, 'conditions' => $conditions]);
		$query = $query->contain(['NoTags'])->where(['NoTags.id IS' => null]);

		return $query;
	}

	/**
	 * Normalizes tags.
	 *
	 * @param array<string>|string $tags List of tags as an array or a delimited string (comma by default).
	 * @return array Normalized tags valid to be marshaled.
	 */
	public function normalizeTags($tags) {
		if (is_string($tags)) {
			$tags = explode($this->getConfig('delimiter'), $tags) ?: [];
		}

		$result = [];

		$modelAlias = $this->getConfig('fkModelAlias') ?: $this->_table->getAlias();

		$common = ['_joinData' => [$this->getConfig('fkModelField') => $modelAlias]];
		$namespace = $this->getConfig('namespace');

		$tagsTable = $this->_table->{$this->getConfig('tagsAlias')};
		$displayField = $tagsTable->getDisplayField();

		$keys = [];
		foreach ($tags as $tag) {
			$tag = trim($tag);
			if (empty($tag)) {
				continue;
			}
			$tagKey = $this->_getTagKey($tag);
			if (in_array($tagKey, $keys, true)) {
				continue;
			}
			$keys[] = $tagKey;

			$existingTag = $this->_tagExists($tagKey);
			if ($existingTag) {
				$result[] = $common + ['id' => $existingTag->id];

				continue;
			}
			[$customNamespace, $label] = $this->_normalizeTag($tag);

			$result[] = $common + [
				'slug' => $tagKey,
				'namespace' => $customNamespace ?: $namespace,
				'label' => $label,
			] + compact($displayField);
		}

		return $result;
	}

	/**
	 * Generates the unique tag key.
	 *
	 * @param string $tag Tag label.
	 * @throws \RuntimeException
	 * @return string
	 */
	protected function _getTagKey($tag) {
		$slug = $this->getConfig('slug');
		if ($slug) {
			if (!is_callable($slug)) {
				throw new RuntimeException('You must use a valid callable for custom slugging.');
			}

			return $slug($tag);
		}

		return mb_strtolower(Text::slug($tag));
	}

	/**
	 * Checks if a tag already exists and returns the entity if so.
	 *
	 * @param string $slug Tag key.
	 * @return \Cake\Datasource\EntityInterface|null
	 */
	protected function _tagExists($slug) {
		$tagsTable = $this->_table->{$this->getConfig('tagsAlias')}->getTarget();

		$result = $tagsTable->find()
			->where([
				$tagsTable->aliasField('slug') => $slug,
			])
			->select([
				$tagsTable->aliasField($tagsTable->getPrimaryKey()),
			])
			->first();

		if ($result) {
			return $result;
		}

		return null;
	}

	/**
	 * Normalizes a tag string by trimming unnecessary whitespace and extracting the tag identifier
	 * from a tag in case it exists.
	 *
	 * @param string $tag Tag.
	 * @return array<string> The tag's ID and label.
	 */
	protected function _normalizeTag($tag) {
		$namespacePart = null;
		$labelPart = $tag;
		$separator = (string)$this->getConfig('separator') ?: null;
		if ($separator === null) {
			return [
				'',
				$tag,
			];
		}

		if (strpos($tag, $separator) !== false) {
			[$namespacePart, $labelPart] = explode($separator, $tag, 2);
		}

		return [
			trim((string)$namespacePart),
			trim($labelPart),
		];
	}

	/**
	 * @param array<string>|string $filterValue
	 * @param string $finderField
	 *
	 * @return \Cake\ORM\Query|string
	 */
	protected function buildQuerySnippet($filterValue, string $finderField) {
		$key = $this->getConfig('tagsAlias') . '.' . $finderField;

		if (is_array($filterValue)) {
			$taggedAlias = $this->getConfig('taggedAlias');
			$foreignKey = $this->getConfig('tagsAssoc.foreignKey');
			$conditions = [
				$key . ' IN' => $filterValue,
			];

			return $this->_table->{$taggedAlias}->find()
				->contain([$this->getConfig('tagsAlias')])
				->select($taggedAlias . '.' . $foreignKey)
				->where($conditions);
		}

		if ($this->getConfig('andSeparator') && strpos($filterValue, $this->getConfig('andSeparator')) !== false) {
			$andValues = $this->parseFilter($filterValue, $this->getConfig('andSeparator'));

			$taggedAlias = $this->getConfig('taggedAlias');
			$foreignKey = $this->getConfig('tagsAssoc.foreignKey');
			$conditions = [
				$key . ' IN' => $andValues,
			];

			return $this->_table->{$taggedAlias}->find()
				->contain([$this->getConfig('tagsAlias')])
				->group($taggedAlias . '.' . $foreignKey)
				->having('COUNT(*) = ' . count($andValues))
				->select($taggedAlias . '.' . $foreignKey)
				->where($conditions);
		}

		if ($this->getConfig('orSeparator') && strpos($filterValue, $this->getConfig('orSeparator')) !== false) {
			$orValues = $this->parseFilter($filterValue, $this->getConfig('orSeparator'));
			$taggedAlias = $this->getConfig('taggedAlias');
			$foreignKey = $this->getConfig('tagsAssoc.foreignKey');
			$conditions = [
				$key . ' IN' => $orValues,
			];

			return $this->_table->{$taggedAlias}->find()
				->contain([$this->getConfig('tagsAlias')])
				->select($taggedAlias . '.' . $foreignKey)
				->where($conditions);
		}

		return $filterValue;
	}

	/**
	 * @param string $filterValue
	 * @param string $operator
	 *
	 * @return array<string>
	 */
	protected function parseFilter(string $filterValue, string $operator) {
		$pieces = explode($operator, $filterValue) ?: [];

		$elements = [];
		foreach ($pieces as $piece) {
			$elements[] = trim($piece);
		}

		return array_unique($elements);
	}

}
