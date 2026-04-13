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
 * @property \Tags\Model\Table\TaggedTable&\Cake\ORM\Association\HasMany $Tagged
 *
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

		$this->hasMany('Tagged', [
			'className' => 'Tags.Tagged',
			'foreignKey' => 'tag_id',
			'dependent' => true,
		]);
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
		if (isset($data['namespace']) && $data['namespace'] === '') {
			$data['namespace'] = null;
		}

		if (isset($data['label']) && array_key_exists('slug', (array)$data) && ($data['slug'] === '' || $data['slug'] === 0 || $data['slug'] === null)) {
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

	/**
	 * Find potential duplicate tags based on similar slugs.
	 *
	 * Detects duplicates by finding tags where one slug is a prefix of another,
	 * or tags that differ only by common suffixes (s, es, ing, ed).
	 *
	 * @param string|null $namespace Optional namespace to filter by.
	 * @return array<array<\Tags\Model\Entity\Tag>> Groups of potentially duplicate tags.
	 */
	public function findDuplicates(?string $namespace = null): array {
		$query = $this->find()
			->orderByAsc('namespace')
			->orderByAsc('slug');

		if ($namespace !== null) {
			$query->where(['namespace' => $namespace ?: null]);
		}

		/** @var array<\Tags\Model\Entity\Tag> $tags */
		$tags = $query->all()->toArray();

		$duplicates = [];
		$processed = [];

		foreach ($tags as $i => $tag) {
			if (isset($processed[$tag->id])) {
				continue;
			}

			$group = [$tag];
			$baseSlug = $this->normalizeSlug($tag->slug);

			foreach ($tags as $j => $otherTag) {
				if ($i === $j || isset($processed[$otherTag->id])) {
					continue;
				}

				// Must be same namespace
				if ($tag->namespace !== $otherTag->namespace) {
					continue;
				}

				$otherBaseSlug = $this->normalizeSlug($otherTag->slug);

				// Check if slugs are similar
				if ($baseSlug === $otherBaseSlug ||
					str_starts_with($otherBaseSlug, $baseSlug) ||
					str_starts_with($baseSlug, $otherBaseSlug) ||
					levenshtein($baseSlug, $otherBaseSlug) <= 2
				) {
					$group[] = $otherTag;
					$processed[$otherTag->id] = true;
				}
			}

			if (count($group) > 1) {
				$duplicates[] = $group;
				$processed[$tag->id] = true;
			}
		}

		return $duplicates;
	}

	/**
	 * Normalize a slug for duplicate comparison.
	 *
	 * Removes common suffixes like pluralization.
	 *
	 * @param string $slug The slug to normalize.
	 * @return string The normalized slug.
	 */
	protected function normalizeSlug(string $slug): string {
		// Remove common suffixes
		$suffixes = ['ies', 'es', 's', 'ing', 'ed'];
		foreach ($suffixes as $suffix) {
			if (str_ends_with($slug, $suffix) && strlen($slug) > strlen($suffix) + 2) {
				return substr($slug, 0, -strlen($suffix));
			}
		}

		return $slug;
	}

	/**
	 * Count slug conflicts for a namespace move.
	 *
	 * A conflict exists when the target namespace already contains a tag with the
	 * same slug as one of the source namespace tags.
	 *
	 * @param string|null $fromNamespace Source namespace.
	 * @param string|null $toNamespace Target namespace.
	 * @return int Number of conflicting target tags.
	 */
	public function countNamespaceConflicts(?string $fromNamespace, ?string $toNamespace): int {
		$sourceSlugs = $this->find()
			->select(['slug'])
			->where($this->namespaceConditions($fromNamespace))
			->disableHydration()
			->all()
			->extract('slug')
			->toList();

		if (!$sourceSlugs) {
			return 0;
		}

		return $this->find()
			->where($this->namespaceConditions($toNamespace))
			->where(['slug IN' => $sourceSlugs])
			->count();
	}

	/**
	 * Move all tags from one namespace to another.
	 *
	 * @param string|null $fromNamespace Source namespace.
	 * @param string|null $toNamespace Target namespace.
	 * @throws \RuntimeException When duplicate slugs already exist in the target namespace.
	 * @return int Number of updated rows.
	 */
	public function moveNamespace(?string $fromNamespace, ?string $toNamespace): int {
		$conflicts = $this->countNamespaceConflicts($fromNamespace, $toNamespace);
		if ($conflicts) {
			throw new RuntimeException(sprintf(
				'Cannot move namespace: %d conflicting slug(s) already exist in the target namespace.',
				$conflicts,
			));
		}

		return $this->updateAll(
			['namespace' => $toNamespace],
			$this->namespaceConditions($fromNamespace),
		);
	}

	/**
	 * Delete all orphaned tags (counter = 0).
	 *
	 * @param string|null $namespace Optional namespace to filter by.
	 * @return int Number of deleted tags.
	 */
	public function deleteOrphaned(?string $namespace = null): int {
		$conditions = ['counter' => 0];

		if ($namespace !== null) {
			$conditions['namespace'] = $namespace ?: null;
		}

		return $this->deleteAll($conditions);
	}

	/**
	 * Recalculate counter cache for all tags.
	 *
	 * Useful when counters get out of sync.
	 *
	 * @return int Number of tags updated.
	 */
	public function recalculateCounters(): int {
		$taggedTable = $this->getAssociation('Tagged')->getTarget();
		$updated = 0;

		// Get actual counts from tagged table
		$counts = $taggedTable->find()
			->select([
				'tag_id',
				'count' => $taggedTable->find()->func()->count('*'),
			])
			->groupBy('tag_id')
			->disableHydration()
			->all()
			->combine('tag_id', 'count')
			->toArray();

		// Update all tags
		/** @var array<\Tags\Model\Entity\Tag> $tags */
		$tags = $this->find()->all()->toArray();
		foreach ($tags as $tag) {
			$newCount = $counts[$tag->id] ?? 0;
			if ($tag->counter !== $newCount) {
				$tag->counter = $newCount;
				$this->save($tag);
				$updated++;
			}
		}

		return $updated;
	}

	/**
	 * Merge one tag into another.
	 *
	 * All associations from the source tag will be moved to the target tag.
	 * Duplicate associations (where an item already has both tags) will be removed.
	 * The source tag will be deleted after the merge.
	 *
	 * @param int $sourceId The ID of the tag to merge from (will be deleted).
	 * @param int $targetId The ID of the tag to merge into (will remain).
	 * @return bool True on success, false on failure.
	 */
	public function merge(int $sourceId, int $targetId): bool {
		$sourceTag = $this->get($sourceId);
		$targetTag = $this->get($targetId);

		// Verify same namespace
		if ($sourceTag->namespace !== $targetTag->namespace) {
			return false;
		}

		$taggedTable = $this->getAssociation('Tagged')->getTarget();
		$connection = $this->getConnection();

		return $connection->transactional(function () use ($taggedTable, $sourceId, $targetId, $sourceTag, $targetTag) {
			// Find IDs of duplicate associations (items that already have the target tag)
			$duplicateIds = $taggedTable->find()
				->select(['Tagged.id'])
				->innerJoin(
					['t2' => 'tags_tagged'],
					[
						't2.tag_id' => $targetId,
						't2.fk_id = Tagged.fk_id',
						't2.fk_model = Tagged.fk_model',
					],
				)
				->where(['Tagged.tag_id' => $sourceId])
				->all()
				->extract('id')
				->toArray();

			// Delete duplicates
			if ($duplicateIds) {
				$taggedTable->deleteAll(['id IN' => $duplicateIds]);
			}

			// Move remaining associations from source to target
			$taggedTable->updateAll(
				['tag_id' => $targetId],
				['tag_id' => $sourceId],
			);

			// Update target tag counter
			$newCount = $taggedTable->find()
				->where(['tag_id' => $targetId])
				->count();
			$targetTag->counter = $newCount;
			$this->saveOrFail($targetTag);

			// Delete source tag
			return $this->delete($sourceTag);
		});
	}

	/**
	 * Build namespace conditions, including null namespace support.
	 *
	 * @param string|null $namespace Namespace value.
	 * @return array<string, string|null>
	 */
	protected function namespaceConditions(?string $namespace): array {
		if ($namespace === null) {
			return ['namespace IS' => null];
		}

		return ['namespace' => $namespace];
	}

}
