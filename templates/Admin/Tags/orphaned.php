<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\Tags\Model\Entity\Tag> $orphanedTags
 * @var int $orphanedCount
 */
?>

<h1 class="mb-4">
	<i class="fas fa-unlink me-2 text-muted"></i>
	<?= __d('tags', 'Orphaned Tags') ?>
	<span class="badge bg-warning text-dark ms-2"><?= $orphanedCount ?></span>
</h1>

<?php if ($orphanedCount > 0): ?>
<div class="alert alert-warning">
	<i class="fas fa-exclamation-triangle me-2"></i>
	<?= __d('tags', 'Found {0} orphaned tags (never used or counter = 0). Review and delete as needed.', $orphanedCount) ?>
</div>

<div class="card card-tags mb-4">
	<div class="card-header d-flex justify-content-between align-items-center">
		<span>
			<i class="fas fa-list me-2"></i>
			<?= __d('tags', 'Orphaned Tags List') ?>
		</span>
		<?= $this->Form->postButton(
			'<i class="fas fa-trash-alt me-1"></i>' . __d('tags', 'Delete All Orphaned Tags'),
			['action' => 'deleteOrphaned'],
			[
				'class' => 'btn btn-danger btn-sm',
				'escapeTitle' => false,
				'form' => [
					'class' => 'd-inline',
					'data-confirm-message' => __d('tags', 'Are you sure you want to delete all {0} orphaned tags? This cannot be undone.', $orphanedCount),
				],
			]
		) ?>
	</div>
	<div class="card-body p-0">
		<table class="table table-hover mb-0">
			<thead class="table-light">
				<tr>
					<th><?= __d('tags', 'Namespace') ?></th>
					<th><?= __d('tags', 'Label') ?></th>
					<th><?= __d('tags', 'Slug') ?></th>
					<th><?= __d('tags', 'Created') ?></th>
					<th class="text-end"><?= __d('tags', 'Actions') ?></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ($orphanedTags as $tag): ?>
				<tr>
					<td>
						<?php if ($tag->namespace): ?>
						<code><?= h($tag->namespace) ?></code>
						<?php else: ?>
						<em class="text-muted"><?= __d('tags', '(none)') ?></em>
						<?php endif; ?>
					</td>
					<td>
						<?php if ($tag->color): ?>
						<span class="tag-color-swatch me-2" style="background-color: <?= h($tag->color) ?>"></span>
						<?php endif; ?>
						<?= h($tag->label) ?>
					</td>
					<td><code><?= h($tag->slug) ?></code></td>
					<td>
						<small class="text-muted"><?= $this->Time->timeAgoInWords($tag->created) ?></small>
					</td>
					<td class="text-end">
						<div class="btn-group btn-group-sm">
							<a href="<?= $this->Url->build(['action' => 'edit', $tag->id]) ?>" class="btn btn-outline-primary" title="<?= __d('tags', 'Edit') ?>">
								<i class="fas fa-edit"></i>
							</a>
							<?= $this->Form->postButton(
								'<i class="fas fa-trash-alt"></i>',
								['action' => 'delete', $tag->id],
								[
									'class' => 'btn btn-outline-danger',
									'escapeTitle' => false,
									'title' => __d('tags', 'Delete'),
									'form' => [
										'class' => 'd-inline',
										'data-confirm-message' => __d('tags', 'Are you sure you want to delete "{0}"?', $tag->label),
									],
								]
							) ?>
						</div>
					</td>
				</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	</div>
	<?php if ($this->Paginator->total() > 1): ?>
	<div class="card-footer">
		<nav aria-label="Page navigation">
			<ul class="pagination pagination-sm mb-0 justify-content-center">
				<?= $this->Paginator->first('«', ['class' => 'page-link']) ?>
				<?= $this->Paginator->prev('‹', ['class' => 'page-link']) ?>
				<?= $this->Paginator->numbers(['class' => 'page-link']) ?>
				<?= $this->Paginator->next('›', ['class' => 'page-link']) ?>
				<?= $this->Paginator->last('»', ['class' => 'page-link']) ?>
			</ul>
		</nav>
	</div>
	<?php endif; ?>
</div>

<?php else: ?>
<div class="alert alert-success">
	<i class="fas fa-check-circle me-2"></i>
	<?= __d('tags', 'No orphaned tags found. All tags are in use!') ?>
</div>
<?php endif; ?>

<div class="mt-4">
	<a href="<?= $this->Url->build(['controller' => 'TagsDashboard', 'action' => 'index']) ?>" class="btn btn-outline-secondary">
		<i class="fas fa-arrow-left me-1"></i>
		<?= __d('tags', 'Back to Dashboard') ?>
	</a>
</div>
