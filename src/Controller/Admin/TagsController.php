<?php
declare(strict_types=1);

namespace Tags\Controller\Admin;

use Cake\Http\Response;

/**
 * Tags Controller
 *
 * @property \Tags\Model\Table\TagsTable $Tags
 * @property \Tags\Model\Table\TaggedTable $Tagged
 */
class TagsController extends TagsAppController {

	/**
	 * @var string|null
	 */
	protected ?string $defaultTable = 'Tags.Tags';

	/**
	 * Index action - list all tags.
	 *
	 * @return void
	 */
	public function index(): void {
		$query = $this->Tags->find();

		// Filter by namespace
		$namespace = $this->request->getQuery('namespace');
		if ($namespace !== null) {
			if ($namespace === '') {
				$query->where(['namespace IS' => null]);
			} else {
				$query->where(['namespace' => $namespace]);
			}
		}

		// Filter orphaned tags (counter = 0)
		if ($this->request->getQuery('orphaned')) {
			$query->where(['counter' => 0]);
		}

		// Search by label/slug
		$search = $this->request->getQuery('search');
		if ($search) {
			$query->where([
				'OR' => [
					'label LIKE' => '%' . $search . '%',
					'slug LIKE' => '%' . $search . '%',
				],
			]);
		}

		$query->orderByAsc('namespace')->orderByAsc('label');

		$tags = $this->paginate($query);

		// Get unique namespaces for filter
		$namespaces = $this->Tags->find()
			->select(['namespace'])
			->distinct()
			->orderByAsc('namespace')
			->all()
			->extract('namespace')
			->toArray();

		$this->set(compact('tags', 'namespaces', 'namespace', 'search'));
	}

	/**
	 * View action - view a single tag.
	 *
	 * @param string|null $id Tag id.
	 * @return void
	 */
	public function view(?string $id = null): void {
		$tag = $this->Tags->get($id);

		// Get recent usages
		$taggedTable = $this->fetchTable('Tags.Tagged');
		$usages = $taggedTable->find()
			->where(['tag_id' => $id])
			->orderByDesc('created')
			->limit(20)
			->all()
			->toArray();

		// Get usage by model
		$usagesByModel = $taggedTable->find()
			->select([
				'fk_model',
				'count' => $taggedTable->find()->func()->count('*'),
			])
			->where(['tag_id' => $id])
			->groupBy('fk_model')
			->disableHydration()
			->all()
			->toArray();

		$this->set(compact('tag', 'usages', 'usagesByModel'));
	}

	/**
	 * Add action - create a new tag.
	 *
	 * @return \Cake\Http\Response|null
	 */
	public function add(): ?Response {
		$tag = $this->Tags->newEmptyEntity();

		if ($this->request->is('post')) {
			$tag = $this->Tags->patchEntity($tag, $this->request->getData());
			if ($this->Tags->save($tag)) {
				$this->Flash->success(__d('tags', 'The tag has been saved.'));

				return $this->redirect(['action' => 'index']);
			}
			$this->Flash->error(__d('tags', 'The tag could not be saved. Please try again.'));
		}

		// Get unique namespaces for suggestions
		$namespaces = $this->Tags->find()
			->select(['namespace'])
			->distinct()
			->where(['namespace IS NOT' => null])
			->orderByAsc('namespace')
			->all()
			->extract('namespace')
			->toArray();

		$this->set(compact('tag', 'namespaces'));

		return null;
	}

	/**
	 * Edit action - edit an existing tag.
	 *
	 * @param string|null $id Tag id.
	 * @return \Cake\Http\Response|null
	 */
	public function edit(?string $id = null): ?Response {
		$tag = $this->Tags->get($id);

		if ($this->request->is(['patch', 'post', 'put'])) {
			$tag = $this->Tags->patchEntity($tag, $this->request->getData());
			if ($this->Tags->save($tag)) {
				$this->Flash->success(__d('tags', 'The tag has been saved.'));

				return $this->redirect(['action' => 'index']);
			}
			$this->Flash->error(__d('tags', 'The tag could not be saved. Please try again.'));
		}

		// Get unique namespaces for suggestions
		$namespaces = $this->Tags->find()
			->select(['namespace'])
			->distinct()
			->where(['namespace IS NOT' => null])
			->orderByAsc('namespace')
			->all()
			->extract('namespace')
			->toArray();

		$this->set(compact('tag', 'namespaces'));

		return null;
	}

	/**
	 * Delete action - delete a tag.
	 *
	 * @param string|null $id Tag id.
	 * @return \Cake\Http\Response|null
	 */
	public function delete(?string $id = null): ?Response {
		$this->request->allowMethod(['post', 'delete']);

		$tag = $this->Tags->get($id);

		if ($this->Tags->delete($tag)) {
			$this->Flash->success(__d('tags', 'The tag has been deleted.'));
		} else {
			$this->Flash->error(__d('tags', 'The tag could not be deleted. Please try again.'));
		}

		return $this->redirect(['action' => 'index']);
	}

	/**
	 * Merge action - select tags to merge.
	 *
	 * @return void
	 */
	public function merge(): void {
		// Get tags grouped by namespace for dropdowns
		/** @var array<\Tags\Model\Entity\Tag> $tags */
		$tags = $this->Tags->find()
			->orderByAsc('namespace')
			->orderByAsc('label')
			->all()
			->toArray();

		// Group by namespace
		$tagsByNamespace = [];
		foreach ($tags as $tag) {
			$ns = $tag->namespace ?: '';
			if (!isset($tagsByNamespace[$ns])) {
				$tagsByNamespace[$ns] = [];
			}
			$tagsByNamespace[$ns][] = $tag;
		}

		$this->set(compact('tags', 'tagsByNamespace'));
	}

	/**
	 * Merge preview action - show preview and execute merge.
	 *
	 * @return \Cake\Http\Response|null
	 */
	public function mergePreview(): ?Response {
		$sourceId = $this->request->getQuery('source') ?: $this->request->getData('source');
		$targetId = $this->request->getQuery('target') ?: $this->request->getData('target');

		if (!$sourceId || !$targetId) {
			$this->Flash->error(__d('tags', 'Please select both source and target tags.'));

			return $this->redirect(['action' => 'merge']);
		}

		if ($sourceId === $targetId) {
			$this->Flash->error(__d('tags', 'Source and target tags must be different.'));

			return $this->redirect(['action' => 'merge']);
		}

		$sourceTag = $this->Tags->get($sourceId);
		$targetTag = $this->Tags->get($targetId);

		// Check namespace match
		if ($sourceTag->namespace !== $targetTag->namespace) {
			$this->Flash->error(__d('tags', 'Tags must be in the same namespace to merge.'));

			return $this->redirect(['action' => 'merge']);
		}

		$taggedTable = $this->fetchTable('Tags.Tagged');

		// Count items that will be re-tagged
		$itemsToRetag = $taggedTable->find()
			->where(['tag_id' => $sourceId])
			->count();

		// Count duplicates (items that have both tags)
		$duplicates = $taggedTable->find()
			->where(['tag_id' => $sourceId])
			->innerJoin(
				['t2' => 'tags_tagged'],
				[
					't2.tag_id' => $targetId,
					't2.fk_id = Tagged.fk_id',
					't2.fk_model = Tagged.fk_model',
				],
			)
			->count();

		// Execute merge on POST
		if ($this->request->is('post') && $this->request->getData('confirm')) {
			$merged = $this->Tags->merge((int)$sourceId, (int)$targetId);

			if ($merged) {
				$this->Flash->success(__d('tags', 'Tags have been merged successfully. {0} items were re-tagged.', $itemsToRetag - $duplicates));

				return $this->redirect(['action' => 'index']);
			}

			$this->Flash->error(__d('tags', 'The tags could not be merged. Please try again.'));
		}

		$this->set(compact('sourceTag', 'targetTag', 'itemsToRetag', 'duplicates'));

		return null;
	}

	/**
	 * Duplicates action - show potential duplicate tags.
	 *
	 * @return void
	 */
	public function duplicates(): void {
		$duplicateGroups = $this->Tags->findDuplicates();

		$this->set(compact('duplicateGroups'));
	}

	/**
	 * Delete orphaned tags action.
	 *
	 * @return \Cake\Http\Response|null
	 */
	public function deleteOrphaned(): ?Response {
		$this->request->allowMethod(['post', 'delete']);

		$count = $this->Tags->deleteOrphaned();

		if ($count > 0) {
			$this->Flash->success(__d('tags', '{0} orphaned tags have been deleted.', $count));
		} else {
			$this->Flash->info(__d('tags', 'No orphaned tags found.'));
		}

		return $this->redirect(['controller' => 'TagsDashboard', 'action' => 'index']);
	}

	/**
	 * Recalculate counters action.
	 *
	 * @return \Cake\Http\Response|null
	 */
	public function recalculateCounters(): ?Response {
		$this->request->allowMethod(['post']);

		$count = $this->Tags->recalculateCounters();

		if ($count > 0) {
			$this->Flash->success(__d('tags', '{0} tag counters have been updated.', $count));
		} else {
			$this->Flash->info(__d('tags', 'All counters are already correct.'));
		}

		return $this->redirect(['controller' => 'TagsDashboard', 'action' => 'index']);
	}

	/**
	 * Export tags to CSV.
	 *
	 * @return \Cake\Http\Response
	 */
	public function export(): Response {
		$namespace = $this->request->getQuery('namespace');

		$query = $this->Tags->find()
			->orderByAsc('namespace')
			->orderByAsc('label');

		if ($namespace !== null) {
			if ($namespace === '') {
				$query->where(['namespace IS' => null]);
			} else {
				$query->where(['namespace' => $namespace]);
			}
		}

		/** @var array<\Tags\Model\Entity\Tag> $tags */
		$tags = $query->all()->toArray();

		// Build CSV content
		$output = fopen('php://temp', 'r+');
		if ($output === false) {
			throw new \RuntimeException('Failed to open temporary file for CSV export');
		}

		// Header row
		fputcsv($output, ['id', 'namespace', 'slug', 'label', 'color', 'counter', 'created', 'modified']);

		// Data rows
		foreach ($tags as $tag) {
			fputcsv($output, [
				$tag->id,
				$tag->namespace ?? '',
				$tag->slug,
				$tag->label,
				$tag->color ?? '',
				$tag->counter,
				$tag->created->format('Y-m-d H:i:s'),
				$tag->modified->format('Y-m-d H:i:s'),
			]);
		}

		rewind($output);
		$csv = stream_get_contents($output);
		fclose($output);

		$filename = 'tags-export-' . date('Y-m-d-His') . '.csv';

		$response = $this->response
			->withType('csv')
			->withHeader('Content-Disposition', 'attachment; filename="' . $filename . '"')
			->withStringBody($csv ?: '');

		return $response;
	}

	/**
	 * Change namespace for tags.
	 *
	 * @return \Cake\Http\Response|null
	 */
	public function changeNamespace(): ?Response {
		// Get unique namespaces
		$namespaces = $this->Tags->find()
			->select(['namespace'])
			->distinct()
			->orderByAsc('namespace')
			->all()
			->extract('namespace')
			->toArray();

		if ($this->request->is('post')) {
			$fromNamespace = $this->request->getData('from_namespace');
			$toNamespace = $this->request->getData('to_namespace');

			// Handle empty string as null
			if ($fromNamespace === '') {
				$fromNamespace = null;
			}
			if ($toNamespace === '') {
				$toNamespace = null;
			}

			if ($fromNamespace === $toNamespace) {
				$this->Flash->error(__d('tags', 'Source and target namespaces must be different.'));

				$this->set(compact('namespaces'));

				return null;
			}

			$count = $this->Tags->updateAll(
				['namespace' => $toNamespace],
				['namespace IS' => $fromNamespace],
			);

			if ($count > 0) {
				$this->Flash->success(__d('tags', '{0} tags have been moved to the new namespace.', $count));

				return $this->redirect(['action' => 'changeNamespace']);
			}

			$this->Flash->info(__d('tags', 'No tags found in the source namespace.'));
		}

		$this->set(compact('namespaces'));

		return null;
	}

}
