<?php
/**
 * @var \App\View\AppView $this
 * @var \Tags\Model\Entity\Tag $tag
 * @var array<\Tags\Model\Entity\Tagged> $usages
 * @var array $usagesByModel
 */
?>

<div class="d-flex justify-content-between align-items-center mb-4">
	<h1 class="mb-0">
		<i class="fas fa-tag me-2 text-muted"></i>
		<?= h($tag->label) ?>
	</h1>
	<div class="btn-group">
		<a href="<?= $this->Url->build(['action' => 'edit', $tag->id]) ?>" class="btn btn-outline-primary">
			<i class="fas fa-edit me-1"></i>
			<?= __d('tags', 'Edit') ?>
		</a>
		<?= $this->Form->postButton(
			'<i class="fas fa-trash me-1"></i>' . __d('tags', 'Delete'),
			['action' => 'delete', $tag->id],
			[
				'class' => 'btn btn-outline-danger',
				'escapeTitle' => false,
				'form' => [
					'class' => 'd-inline',
					'data-confirm-message' => __d('tags', 'Delete tag "{0}"? This will also remove all associations.', $tag->label),
				],
			],
		) ?>
	</div>
</div>

<div class="row">
	<!-- Tag Details -->
	<div class="col-lg-6 mb-4">
		<div class="card card-tags h-100">
			<div class="card-header">
				<i class="fas fa-info-circle me-2"></i>
				<?= __d('tags', 'Tag Details') ?>
			</div>
			<div class="card-body">
				<table class="table table-borderless mb-0">
					<tr>
						<th style="width: 30%"><?= __d('tags', 'Label') ?></th>
						<td><strong><?= h($tag->label) ?></strong></td>
					</tr>
					<tr>
						<th><?= __d('tags', 'Slug') ?></th>
						<td><code><?= h($tag->slug) ?></code></td>
					</tr>
					<tr>
						<th><?= __d('tags', 'Namespace') ?></th>
						<td>
							<?php if ($tag->namespace) : ?>
							<span class="badge bg-secondary"><?= h($tag->namespace) ?></span>
							<?php else : ?>
							<span class="text-muted">-</span>
							<?php endif; ?>
						</td>
					</tr>
					<tr>
						<th><?= __d('tags', 'Color') ?></th>
						<td>
							<?php if ($tag->color) : ?>
							<span class="tag-color-swatch me-2" data-tag-color="<?= h($tag->color) ?>"></span>
							<code><?= h($tag->color) ?></code>
							<?php else : ?>
							<span class="text-muted">-</span>
							<?php endif; ?>
						</td>
					</tr>
					<tr>
						<th><?= __d('tags', 'Usage Count') ?></th>
						<td><span class="badge <?= $tag->counter > 0 ? 'bg-success' : 'bg-secondary' ?>"><?= $tag->counter ?></span></td>
					</tr>
					<tr>
						<th><?= __d('tags', 'Created') ?></th>
						<td><?= $this->Time->nice($tag->created) ?></td>
					</tr>
					<tr>
						<th><?= __d('tags', 'Modified') ?></th>
						<td><?= $this->Time->nice($tag->modified) ?></td>
					</tr>
				</table>
			</div>
		</div>
	</div>

	<!-- Usage by Model -->
	<div class="col-lg-6 mb-4">
		<div class="card card-tags h-100">
			<div class="card-header">
				<i class="fas fa-database me-2"></i>
				<?= __d('tags', 'Usage by Model') ?>
			</div>
			<?php if ($usagesByModel) : ?>
			<div class="card-body p-0">
				<ul class="list-group list-group-flush">
					<?php foreach ($usagesByModel as $usage) : ?>
					<li class="list-group-item d-flex justify-content-between align-items-center">
						<code><?= h($usage['fk_model']) ?></code>
						<span class="badge bg-primary rounded-pill"><?= $usage['count'] ?></span>
					</li>
					<?php endforeach; ?>
				</ul>
			</div>
			<?php else : ?>
			<div class="card-body text-muted text-center">
				<i class="fas fa-unlink fa-2x mb-2 opacity-50"></i>
				<p class="mb-0"><?= __d('tags', 'This tag is not used anywhere.') ?></p>
			</div>
			<?php endif; ?>
		</div>
	</div>
</div>

<!-- Recent Usages -->
<?php if ($usages) : ?>
<div class="card card-tags">
	<div class="card-header">
		<i class="fas fa-history me-2"></i>
		<?= __d('tags', 'Recent Usages') ?>
	</div>
	<div class="table-responsive">
		<table class="table table-tags table-hover mb-0">
			<thead>
				<tr>
					<th><?= __d('tags', 'Model') ?></th>
					<th><?= __d('tags', 'Foreign Key') ?></th>
					<th><?= __d('tags', 'Created') ?></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ($usages as $usage) : ?>
				<tr>
					<td><code><?= h($usage->fk_model) ?></code></td>
					<td><?= h($usage->fk_id) ?></td>
					<td><small class="text-muted"><?= $this->Time->timeAgoInWords($usage->created) ?></small></td>
				</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	</div>
</div>
<?php endif; ?>

<div class="mt-4">
	<a href="<?= $this->Url->build(['action' => 'index']) ?>" class="btn btn-outline-secondary">
		<i class="fas fa-arrow-left me-1"></i>
		<?= __d('tags', 'Back to Tags') ?>
	</a>
</div>
