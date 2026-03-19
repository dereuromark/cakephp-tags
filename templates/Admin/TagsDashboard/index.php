<?php
/**
 * @var \App\View\AppView $this
 * @var int $totalTags
 * @var array $namespaces
 * @var array<\Tags\Model\Entity\Tag> $mostUsedTags
 * @var int $orphanedCount
 * @var array<\Tags\Model\Entity\Tag> $recentTags
 * @var int $totalTagged
 * @var array $models
 */
?>

<h1 class="mb-4">
	<i class="fas fa-tachometer-alt me-2 text-muted"></i>
	<?= __d('tags', 'Dashboard') ?>
</h1>

<!-- Stats Cards -->
<div class="row g-3 mb-4">
	<div class="col-6 col-md-3">
		<?= $this->element('Tags.Tags/stats_card', [
			'label' => __d('tags', 'Total Tags'),
			'value' => $totalTags,
			'icon' => 'fa-tags',
			'color' => 'primary',
			'link' => $this->Url->build(['controller' => 'Tags', 'action' => 'index']),
		]) ?>
	</div>
	<div class="col-6 col-md-3">
		<?= $this->element('Tags.Tags/stats_card', [
			'label' => __d('tags', 'Tagged Items'),
			'value' => $totalTagged,
			'icon' => 'fa-link',
			'color' => 'success',
		]) ?>
	</div>
	<div class="col-6 col-md-3">
		<?= $this->element('Tags.Tags/stats_card', [
			'label' => __d('tags', 'Namespaces'),
			'value' => count($namespaces),
			'icon' => 'fa-folder',
			'color' => 'info',
		]) ?>
	</div>
	<div class="col-6 col-md-3">
		<?= $this->element('Tags.Tags/stats_card', [
			'label' => __d('tags', 'Orphaned Tags'),
			'value' => $orphanedCount,
			'icon' => 'fa-unlink',
			'color' => $orphanedCount > 0 ? 'warning' : 'secondary',
			'link' => $orphanedCount > 0 ? $this->Url->build(['controller' => 'Tags', 'action' => 'index', '?' => ['orphaned' => 1]]) : null,
		]) ?>
	</div>
</div>

<div class="row">
	<!-- Most Used Tags -->
	<div class="col-lg-6 mb-4">
		<div class="card card-tags h-100">
			<div class="card-header">
				<i class="fas fa-fire me-2 text-danger"></i>
				<?= __d('tags', 'Most Used Tags') ?>
			</div>
			<?php if ($mostUsedTags): ?>
			<div class="card-body p-0">
				<ul class="list-group list-group-flush">
					<?php foreach ($mostUsedTags as $tag): ?>
					<li class="list-group-item d-flex justify-content-between align-items-center">
						<span>
							<?php if ($tag->color): ?>
							<span class="tag-color-swatch me-2" style="background-color: <?= h($tag->color) ?>"></span>
							<?php endif; ?>
							<?php if ($tag->namespace): ?>
							<small class="text-muted"><?= h($tag->namespace) ?>:</small>
							<?php endif; ?>
							<?= h($tag->label) ?>
						</span>
						<span class="badge bg-primary rounded-pill"><?= $tag->counter ?></span>
					</li>
					<?php endforeach; ?>
				</ul>
			</div>
			<?php else: ?>
			<div class="card-body text-muted text-center">
				<i class="fas fa-tags fa-2x mb-2 opacity-50"></i>
				<p class="mb-0"><?= __d('tags', 'No tags used yet.') ?></p>
			</div>
			<?php endif; ?>
		</div>
	</div>

	<!-- Tags by Namespace -->
	<div class="col-lg-6 mb-4">
		<div class="card card-tags h-100">
			<div class="card-header">
				<i class="fas fa-folder-open me-2 text-info"></i>
				<?= __d('tags', 'Tags by Namespace') ?>
			</div>
			<?php if ($namespaces): ?>
			<div class="card-body p-0">
				<ul class="list-group list-group-flush">
					<?php foreach ($namespaces as $ns): ?>
					<li class="list-group-item d-flex justify-content-between align-items-center">
						<span>
							<?= $ns['namespace'] ? h($ns['namespace']) : '<em class="text-muted">' . __d('tags', '(no namespace)') . '</em>' ?>
						</span>
						<span class="badge bg-secondary rounded-pill"><?= $ns['count'] ?></span>
					</li>
					<?php endforeach; ?>
				</ul>
			</div>
			<?php else: ?>
			<div class="card-body text-muted text-center">
				<i class="fas fa-folder fa-2x mb-2 opacity-50"></i>
				<p class="mb-0"><?= __d('tags', 'No tags created yet.') ?></p>
			</div>
			<?php endif; ?>
		</div>
	</div>
</div>

<div class="row">
	<!-- Models Using Tags -->
	<div class="col-lg-6 mb-4">
		<div class="card card-tags h-100">
			<div class="card-header">
				<i class="fas fa-database me-2 text-success"></i>
				<?= __d('tags', 'Models Using Tags') ?>
			</div>
			<?php if ($models): ?>
			<div class="card-body p-0">
				<ul class="list-group list-group-flush">
					<?php foreach ($models as $model): ?>
					<li class="list-group-item d-flex justify-content-between align-items-center">
						<code><?= h($model['fk_model']) ?></code>
						<span class="badge bg-success rounded-pill"><?= $model['count'] ?></span>
					</li>
					<?php endforeach; ?>
				</ul>
			</div>
			<?php else: ?>
			<div class="card-body text-muted text-center">
				<i class="fas fa-database fa-2x mb-2 opacity-50"></i>
				<p class="mb-0"><?= __d('tags', 'No models tagged yet.') ?></p>
			</div>
			<?php endif; ?>
		</div>
	</div>

	<!-- Recently Created Tags -->
	<div class="col-lg-6 mb-4">
		<div class="card card-tags h-100">
			<div class="card-header">
				<i class="fas fa-clock me-2 text-warning"></i>
				<?= __d('tags', 'Recently Created Tags') ?>
			</div>
			<?php if ($recentTags): ?>
			<div class="card-body p-0">
				<ul class="list-group list-group-flush">
					<?php foreach ($recentTags as $tag): ?>
					<li class="list-group-item d-flex justify-content-between align-items-center">
						<span>
							<?php if ($tag->color): ?>
							<span class="tag-color-swatch me-2" style="background-color: <?= h($tag->color) ?>"></span>
							<?php endif; ?>
							<?php if ($tag->namespace): ?>
							<small class="text-muted"><?= h($tag->namespace) ?>:</small>
							<?php endif; ?>
							<?= h($tag->label) ?>
						</span>
						<small class="text-muted"><?= $this->Time->timeAgoInWords($tag->created) ?></small>
					</li>
					<?php endforeach; ?>
				</ul>
			</div>
			<?php else: ?>
			<div class="card-body text-muted text-center">
				<i class="fas fa-clock fa-2x mb-2 opacity-50"></i>
				<p class="mb-0"><?= __d('tags', 'No tags created yet.') ?></p>
			</div>
			<?php endif; ?>
		</div>
	</div>
</div>
