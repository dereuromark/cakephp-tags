<?php
/**
 * @var \App\View\AppView $this
 * @var array<array<\Tags\Model\Entity\Tag>> $duplicateGroups
 */
?>

<h1 class="mb-4">
	<i class="fas fa-clone me-2 text-muted"></i>
	<?= __d('tags', 'Potential Duplicates') ?>
</h1>

<?php if ($duplicateGroups): ?>
<div class="alert alert-info">
	<i class="fas fa-info-circle me-2"></i>
	<?= __d('tags', 'Found {0} groups of potentially duplicate tags. Review and merge as needed.', count($duplicateGroups)) ?>
</div>

<?php foreach ($duplicateGroups as $index => $group): ?>
<div class="card card-tags mb-3">
	<div class="card-header d-flex justify-content-between align-items-center">
		<span>
			<i class="fas fa-tags me-2"></i>
			<?= __d('tags', 'Group {0}', $index + 1) ?>
			<?php if ($group[0]->namespace): ?>
			<span class="badge bg-secondary ms-2"><?= h($group[0]->namespace) ?></span>
			<?php endif; ?>
		</span>
		<span class="badge bg-warning text-dark"><?= count($group) ?> <?= __d('tags', 'tags') ?></span>
	</div>
	<div class="card-body p-0">
		<table class="table table-hover mb-0">
			<thead class="table-light">
				<tr>
					<th><?= __d('tags', 'Label') ?></th>
					<th><?= __d('tags', 'Slug') ?></th>
					<th class="text-center"><?= __d('tags', 'Usage') ?></th>
					<th class="text-end"><?= __d('tags', 'Actions') ?></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ($group as $tag): ?>
				<tr>
					<td>
						<?php if ($tag->color): ?>
						<span class="tag-color-swatch me-2" style="background-color: <?= h($tag->color) ?>"></span>
						<?php endif; ?>
						<?= h($tag->label) ?>
					</td>
					<td><code><?= h($tag->slug) ?></code></td>
					<td class="text-center">
						<span class="badge bg-<?= $tag->counter > 0 ? 'primary' : 'secondary' ?>"><?= $tag->counter ?></span>
					</td>
					<td class="text-end">
						<div class="btn-group btn-group-sm">
							<a href="<?= $this->Url->build(['action' => 'view', $tag->id]) ?>" class="btn btn-outline-secondary" title="<?= __d('tags', 'View') ?>">
								<i class="fas fa-eye"></i>
							</a>
							<a href="<?= $this->Url->build(['action' => 'edit', $tag->id]) ?>" class="btn btn-outline-primary" title="<?= __d('tags', 'Edit') ?>">
								<i class="fas fa-edit"></i>
							</a>
						</div>
					</td>
				</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	</div>
	<div class="card-footer">
		<?php
		// Find the tag with highest usage to suggest as target
		$sortedGroup = $group;
		usort($sortedGroup, fn($a, $b) => $b->counter <=> $a->counter);
		$suggestedTarget = $sortedGroup[0];
		?>
		<a href="<?= $this->Url->build(['action' => 'merge', '?' => ['suggested_target' => $suggestedTarget->id]]) ?>" class="btn btn-sm btn-warning">
			<i class="fas fa-compress-arrows-alt me-1"></i>
			<?= __d('tags', 'Merge Tags in This Group') ?>
		</a>
		<small class="text-muted ms-2">
			<?= __d('tags', 'Suggested target: {0} ({1} uses)', h($suggestedTarget->label), $suggestedTarget->counter) ?>
		</small>
	</div>
</div>
<?php endforeach; ?>

<?php else: ?>
<div class="alert alert-success">
	<i class="fas fa-check-circle me-2"></i>
	<?= __d('tags', 'No potential duplicates found. Your tags look good!') ?>
</div>
<?php endif; ?>

<div class="mt-4">
	<a href="<?= $this->Url->build(['controller' => 'TagsDashboard', 'action' => 'index']) ?>" class="btn btn-outline-secondary">
		<i class="fas fa-arrow-left me-1"></i>
		<?= __d('tags', 'Back to Dashboard') ?>
	</a>
</div>
