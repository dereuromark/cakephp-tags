<?php
/**
 * @var \App\View\AppView $this
 * @var \Cake\ORM\ResultSet<\Tags\Model\Entity\Tag> $tags
 * @var array $namespaces
 * @var string|null $namespace
 * @var string|null $search
 */
?>

<div class="d-flex justify-content-between align-items-center mb-4">
	<h1 class="mb-0">
		<i class="fas fa-tags me-2 text-muted"></i>
		<?= __d('tags', 'Tags') ?>
	</h1>
	<a href="<?= $this->Url->build(['action' => 'add']) ?>" class="btn btn-primary">
		<i class="fas fa-plus me-1"></i>
		<?= __d('tags', 'Add Tag') ?>
	</a>
</div>

<!-- Filters -->
<div class="card card-tags mb-4">
	<div class="card-body">
		<form method="get" class="row g-3">
			<div class="col-md-4">
				<label class="form-label"><?= __d('tags', 'Namespace') ?></label>
				<select name="namespace" class="form-select">
					<option value=""><?= __d('tags', 'All namespaces') ?></option>
					<?php foreach ($namespaces as $ns) : ?>
					<?php $optionValue = $ns ?? '__none__'; ?>
					<option value="<?= h($optionValue) ?>" <?= $namespace === $optionValue ? 'selected' : '' ?>>
						<?= $ns ? h($ns) : __d('tags', '(no namespace)') ?>
					</option>
					<?php endforeach; ?>
				</select>
			</div>
			<div class="col-md-4">
				<label class="form-label"><?= __d('tags', 'Search') ?></label>
				<input type="text" name="search" class="form-control" value="<?= h($search ?? '') ?>" placeholder="<?= __d('tags', 'Search by label or slug...') ?>">
			</div>
			<div class="col-md-4 d-flex align-items-end gap-2">
				<button type="submit" class="btn btn-outline-primary">
					<i class="fas fa-search me-1"></i>
					<?= __d('tags', 'Filter') ?>
				</button>
				<a href="<?= $this->Url->build(['action' => 'index']) ?>" class="btn btn-outline-secondary">
					<?= __d('tags', 'Clear') ?>
				</a>
			</div>
		</form>
	</div>
</div>

<!-- Tags Table -->
<div class="card card-tags">
	<div class="table-responsive">
		<table class="table table-tags table-hover mb-0">
			<thead>
				<tr>
					<th><?= $this->Paginator->sort('label', __d('tags', 'Label')) ?></th>
					<th><?= $this->Paginator->sort('slug', __d('tags', 'Slug')) ?></th>
					<th><?= $this->Paginator->sort('namespace', __d('tags', 'Namespace')) ?></th>
					<th><?= __d('tags', 'Color') ?></th>
					<th><?= $this->Paginator->sort('counter', __d('tags', 'Usage')) ?></th>
					<th><?= $this->Paginator->sort('modified', __d('tags', 'Modified')) ?></th>
					<th class="text-end"><?= __d('tags', 'Actions') ?></th>
				</tr>
			</thead>
			<tbody>
				<?php if ($tags->count() === 0) : ?>
				<tr>
					<td colspan="7" class="text-center text-muted py-4">
						<i class="fas fa-tags fa-2x mb-2 opacity-50"></i>
						<p class="mb-0"><?= __d('tags', 'No tags found.') ?></p>
					</td>
				</tr>
				<?php else : ?>
					<?php foreach ($tags as $tag) : ?>
				<tr>
					<td>
						<strong><?= h($tag->label) ?></strong>
					</td>
					<td>
						<code><?= h($tag->slug) ?></code>
					</td>
					<td>
						<?php if ($tag->namespace) : ?>
						<span class="badge bg-secondary"><?= h($tag->namespace) ?></span>
						<?php else : ?>
						<span class="text-muted">-</span>
						<?php endif; ?>
					</td>
					<td>
						<?php if ($tag->color) : ?>
						<span class="tag-color-swatch" style="background-color: <?= h($tag->color) ?>" title="<?= h($tag->color) ?>"></span>
						<code class="ms-1"><?= h($tag->color) ?></code>
						<?php else : ?>
						<span class="text-muted">-</span>
						<?php endif; ?>
					</td>
					<td>
						<span class="badge <?= $tag->counter > 0 ? 'bg-success' : 'bg-secondary' ?>"><?= $tag->counter ?></span>
					</td>
					<td>
						<small class="text-muted"><?= $this->Time->timeAgoInWords($tag->modified) ?></small>
					</td>
					<td class="text-end">
						<div class="btn-group btn-group-sm">
							<a href="<?= $this->Url->build(['action' => 'view', $tag->id]) ?>" class="btn btn-outline-primary" title="<?= __d('tags', 'View') ?>">
								<i class="fas fa-eye"></i>
							</a>
							<a href="<?= $this->Url->build(['action' => 'edit', $tag->id]) ?>" class="btn btn-outline-secondary" title="<?= __d('tags', 'Edit') ?>">
								<i class="fas fa-edit"></i>
							</a>
							<?= $this->Form->postLink(
								'<i class="fas fa-trash"></i>',
								['action' => 'delete', $tag->id],
								[
									'class' => 'btn btn-outline-danger',
									'escapeTitle' => false,
									'confirm' => __d('tags', 'Delete tag "{0}"? This will also remove all associations.', $tag->label),
									'title' => __d('tags', 'Delete'),
									'block' => true,
								],
							) ?>
						</div>
					</td>
				</tr>
				    <?php endforeach; ?>
				<?php endif; ?>
			</tbody>
		</table>
	</div>
	<?php if ($tags->count() > 0) : ?>
	<div class="card-footer d-flex justify-content-between align-items-center">
		<small class="text-muted">
			<?= $this->Paginator->counter(__d('tags', 'Page {{page}} of {{pages}}, showing {{current}} of {{count}} tags')) ?>
		</small>
		<nav>
			<ul class="pagination pagination-sm mb-0">
				<?= $this->Paginator->first('«') ?>
				<?= $this->Paginator->prev('‹') ?>
				<?= $this->Paginator->numbers() ?>
				<?= $this->Paginator->next('›') ?>
				<?= $this->Paginator->last('»') ?>
			</ul>
		</nav>
	</div>
	<?php endif; ?>
</div>
