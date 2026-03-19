<?php
declare(strict_types=1);

namespace Tags\Controller\Admin;

/**
 * TagsDashboard Controller
 *
 * @property \Tags\Model\Table\TagsTable $Tags
 * @property \Tags\Model\Table\TaggedTable $Tagged
 */
class TagsDashboardController extends TagsAppController {

	/**
	 * @var string|null
	 */
	protected ?string $defaultTable = 'Tags.Tags';

	/**
	 * Dashboard index action.
	 *
	 * @return void
	 */
	public function index(): void {
		$tagsTable = $this->fetchTable('Tags.Tags');
		$taggedTable = $this->fetchTable('Tags.Tagged');

		// Total tags count
		$totalTags = $tagsTable->find()->count();

		// Tags by namespace
		$namespaces = $tagsTable->find()
			->select([
				'namespace',
				'count' => $tagsTable->find()->func()->count('*'),
			])
			->groupBy('namespace')
			->disableHydration()
			->all()
			->toArray();

		// Most used tags (top 10)
		$mostUsedTags = $tagsTable->find()
			->where(['counter >' => 0])
			->orderByDesc('counter')
			->limit(10)
			->all()
			->toArray();

		// Orphaned tags (never used or counter = 0)
		$orphanedCount = $tagsTable->find()
			->where(['counter' => 0])
			->count();

		// Recently created tags (last 10)
		$recentTags = $tagsTable->find()
			->orderByDesc('created')
			->limit(10)
			->all()
			->toArray();

		// Total tagged associations
		$totalTagged = $taggedTable->find()->count();

		// Models using tags
		$models = $taggedTable->find()
			->select([
				'fk_model',
				'count' => $taggedTable->find()->func()->count('*'),
			])
			->groupBy('fk_model')
			->disableHydration()
			->all()
			->toArray();

		$this->set(compact(
			'totalTags',
			'namespaces',
			'mostUsedTags',
			'orphanedCount',
			'recentTags',
			'totalTagged',
			'models',
		));
	}

}
