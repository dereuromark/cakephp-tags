<?php
/**
 * @var \App\View\AppView $this
 * @var \Tags\Model\Entity\Tag $sourceTag
 * @var \Tags\Model\Entity\Tag $targetTag
 * @var int $itemsToRetag
 * @var int $duplicates
 */

$itemsToMove = $itemsToRetag - $duplicates;
?>

<h1 class="mb-4">
	<i class="fas fa-compress-arrows-alt me-2 text-muted"></i>
	<?= __d('tags', 'Merge Preview') ?>
</h1>

<div class="card card-tags mb-4">
	<div class="card-header">
		<i class="fas fa-eye me-2"></i>
		<?= __d('tags', 'Merge Preview') ?>
	</div>
	<div class="card-body">
		<div class="row text-center mb-4">
			<div class="col-md-5">
				<div class="card bg-danger bg-opacity-10 border-danger">
					<div class="card-body">
						<h5 class="text-danger">
							<i class="fas fa-arrow-right me-1"></i>
							<?= __d('tags', 'Source (will be deleted)') ?>
						</h5>
						<div class="fs-4 fw-bold"><?= h($sourceTag->label) ?></div>
						<?php if ($sourceTag->namespace) : ?>
						<span class="badge bg-secondary"><?= h($sourceTag->namespace) ?></span>
						<?php endif; ?>
						<?php if ($sourceTag->color) : ?>
						<span class="tag-color-swatch ms-2" style="background-color: <?= h($sourceTag->color) ?>"></span>
						<?php endif; ?>
						<div class="mt-2 text-muted">
							<code><?= h($sourceTag->slug) ?></code>
							<span class="badge bg-secondary ms-2"><?= $sourceTag->counter ?> uses</span>
						</div>
					</div>
				</div>
			</div>

			<div class="col-md-2 d-flex align-items-center justify-content-center">
				<i class="fas fa-arrow-right fa-2x text-muted"></i>
			</div>

			<div class="col-md-5">
				<div class="card bg-success bg-opacity-10 border-success">
					<div class="card-body">
						<h5 class="text-success">
							<i class="fas fa-bullseye me-1"></i>
							<?= __d('tags', 'Target (will remain)') ?>
						</h5>
						<div class="fs-4 fw-bold"><?= h($targetTag->label) ?></div>
						<?php if ($targetTag->namespace) : ?>
						<span class="badge bg-secondary"><?= h($targetTag->namespace) ?></span>
						<?php endif; ?>
						<?php if ($targetTag->color) : ?>
						<span class="tag-color-swatch ms-2" style="background-color: <?= h($targetTag->color) ?>"></span>
						<?php endif; ?>
						<div class="mt-2 text-muted">
							<code><?= h($targetTag->slug) ?></code>
							<span class="badge bg-secondary ms-2"><?= $targetTag->counter ?> uses</span>
						</div>
					</div>
				</div>
			</div>
		</div>

		<!-- Impact Summary -->
		<div class="alert alert-warning">
			<h5 class="alert-heading">
				<i class="fas fa-exclamation-triangle me-2"></i>
				<?= __d('tags', 'Merge Impact') ?>
			</h5>
			<ul class="mb-0">
				<li>
					<strong><?= $itemsToMove ?></strong> <?= __d('tags', 'items will be re-tagged from "{0}" to "{1}"', h($sourceTag->label), h($targetTag->label)) ?>
				</li>
				<?php if ($duplicates > 0) : ?>
				<li>
					<strong><?= $duplicates ?></strong> <?= __d('tags', 'duplicate associations will be removed (items that already have both tags)') ?>
				</li>
				<?php endif; ?>
				<li>
					<?= __d('tags', 'The tag "{0}" will be permanently deleted', h($sourceTag->label)) ?>
				</li>
				<li>
					<?= __d('tags', 'Target tag "{0}" will have approximately {1} uses after merge', h($targetTag->label), $targetTag->counter + $itemsToMove) ?>
				</li>
			</ul>
		</div>

		<!-- Confirm Form -->
		<?= $this->Form->create(null, ['type' => 'post']) ?>
		<?= $this->Form->hidden('source', ['value' => $sourceTag->id]) ?>
		<?= $this->Form->hidden('target', ['value' => $targetTag->id]) ?>
		<?= $this->Form->hidden('confirm', ['value' => '1']) ?>

		<div class="d-flex gap-2">
			<button type="submit" class="btn btn-danger">
				<i class="fas fa-compress-arrows-alt me-1"></i>
				<?= __d('tags', 'Confirm Merge') ?>
			</button>
			<a href="<?= $this->Url->build(['action' => 'merge']) ?>" class="btn btn-outline-secondary">
				<i class="fas fa-arrow-left me-1"></i>
				<?= __d('tags', 'Back') ?>
			</a>
		</div>
		<?= $this->Form->end() ?>
	</div>
</div>
