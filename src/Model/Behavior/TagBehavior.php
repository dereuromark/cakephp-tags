<?php
namespace Tags\Model\Behavior;

use ArrayObject;
use Cake\Core\Configure;
use Cake\Datasource\QueryInterface;
use Cake\Event\Event;
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
	 * @var array
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
		],
		'implementedMethods' => [
			'normalizeTags' => 'normalizeTags',
		],
		'implementedFinders' => [
			'tagged' => 'findByTag',
			'untagged' => 'findUntagged',
		],
		'finderField' => 'tag',
		'fkModelField' => 'fk_model',
		'fkModelAlias' => null,
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
	public function initialize(array $config) {
		$this->bindAssociations();
		$this->attachCounters();
	}

	/**
	 * Return lists of event's this behavior is interested in.
	 *
	 * @return array Events list.
	 */
	public function implementedEvents() {
		return $this->getConfig('implementedEvents');
	}

	/**
	 * Before marshal callback
	 *
	 * @param \Cake\Event\Event $event The Model.beforeMarshal event.
	 * @param \ArrayObject $data Data.
	 * @param \ArrayObject $options Options.
	 * @return void
	 * @throws \RuntimeException
	 */
	public function beforeMarshal(Event $event, ArrayObject $data, ArrayObject $options) {
		$field = $this->getConfig('field');
		$property = $this->getConfig('tagsAssoc.propertyName');
		$options['accessibleFields'][$property] = true;

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
	 * @param \Cake\Event\Event $event
	 * @param \Cake\ORM\Query $query
	 * @param \ArrayObject $options
	 * @return \Cake\ORM\Query
	 */
	public function beforeFind(Event $event, Query $query, ArrayObject $options) {
		$query->formatResults(function ($results) {
			/** @var \Cake\Collection\CollectionInterface $results */
			return $results->map(function ($row) {
				$field = $this->getConfig('field');
				$property = $this->getConfig('tagsAssoc.propertyName');

				if (!$row instanceOf Entity && !isset($row[$property])) {
					return $row;
				}

				$row[$field] = $this->prepareTagsForOutput((array)$row[$property]);
				if ($row instanceOf Entity) {
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
	 * @return string|array
	 */
	public function prepareTagsForOutput(array $data) {
		$tags = [];

		foreach ($data as $tag) {
			if ($this->_config['namespace']) {
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
					'className' => $table->getTable(),
				] + $tagsAssoc);
		}

		if (!$table->{$taggedAlias}->hasAssociation($tableAlias)) {
			$table->{$taggedAlias}
				->belongsTo($tableAlias, [
					'className' => $table->getTable(),
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
	 * @return void
	 * @throws \RuntimeException If configured counter cache field does not exist in table.
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
					$this->_table->getTable()
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
	 * @param string|array $config
	 * @return array
	 */
	protected function _getTaggedCounterConfig($config) {
		if (!is_array($config)) {
			return [$config => ['conditions' => []]];
		}

		return $config;
	}

	/**
	 * Customer finder method.
	 *
	 * Usage:
	 *   $query->find('tagged', ['{finderField}' => 'example-tag']);
	 *
	 * @param \Cake\ORM\Query $query
	 * @param array $options
	 * @return \Cake\ORM\Query
	 * @throws \RuntimeException
	 */
	public function findByTag(Query $query, array $options) {
		if (!isset($options[$this->getConfig('finderField')])) {
			throw new RuntimeException('Key not present');
		}
		$slug = $options[$this->getConfig('finderField')];
		if (empty($slug)) {
			return $query;
		}
		$query->matching($this->getConfig('tagsAlias'), function (QueryInterface $q) use ($slug) {
			return $q->where([
				$this->getConfig('tagsAlias') . '.slug' => $slug,
			]);
		});

		return $query;
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
	 * @param array $options
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
	 * @param array|string $tags List of tags as an array or a delimited string (comma by default).
	 * @return array Normalized tags valid to be marshaled.
	 */
	public function normalizeTags($tags) {
		if (is_string($tags)) {
			$tags = explode($this->getConfig('delimiter'), $tags);
		}

		$result = [];

		$modelAlias = $this->getConfig('fkModelAlias') ?: $this->_table->getAlias();

		$common = ['_joinData' => [$this->getConfig('fkModelField') => $modelAlias]];
		$namespace = $this->getConfig('namespace');
		if ($namespace) {
			$common += compact('namespace');
		}

		$tagsTable = $this->_table->{$this->getConfig('tagsAlias')};
		$primaryKey = $tagsTable->getPrimaryKey();
		$displayField = $tagsTable->getDisplayField();

		foreach ($tags as $tag) {
			$tag = trim($tag);
			if (empty($tag)) {
				continue;
			}
			$tagKey = $this->_getTagKey($tag);
			$existingTag = $this->_tagExists($tagKey);
			if (!empty($existingTag)) {
				$result[] = $common + ['id' => $existingTag];
				continue;
			}
			list($id, $label) = $this->_normalizeTag($tag);
			$result[] = $common + compact(empty($id) ? $displayField : $primaryKey) + [
				'slug' => $tagKey,
			];
		}

		return $result;
	}

	/**
	 * Generates the unique tag key.
	 *
	 * @param string $tag Tag label.
	 * @return string
	 */
	protected function _getTagKey($tag) {
		return strtolower(Text::slug($tag));
	}

	/**
	 * Checks if a tag already exists and returns the id if yes.
	 *
	 * @param string $tag Tag key.
	 * @return null|int
	 */
	protected function _tagExists($tag) {
		$tagsTable = $this->_table->{$this->getConfig('tagsAlias')}->getTarget();
		$result = $tagsTable->find()
			->where([
				$tagsTable->aliasField('slug') => $tag,
			])
			->select([
				$tagsTable->aliasField($tagsTable->getPrimaryKey())
			])
			->first();
		if (!empty($result)) {
			return $result->id;
		}
		return null;
	}

	/**
	 * Normalizes a tag string by trimming unnecessary whitespace and extracting the tag identifier
	 * from a tag in case it exists.
	 *
	 * @param string $tag Tag.
	 * @return array The tag's ID and label.
	 */
	protected function _normalizeTag($tag) {
		$namespace = null;
		$label = $tag;
		$separator = $this->getConfig('separator');
		if ($separator === null) {
			return [
				null,
				$tag,
			];
		}

		if (strpos($tag, $separator) !== false) {
			list($namespace, $label) = explode($separator, $tag);
		}

		return [
			trim($namespace),
			trim($label)
		];
	}

}
