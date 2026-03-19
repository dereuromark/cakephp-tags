<?php
/**
 * @var \App\View\AppView $this
 * @var array $namespaces
 */
?>

<h1 class="mb-4">
	<i class="fas fa-exchange-alt me-2 text-muted"></i>
	<?= __d('tags', 'Change Namespace') ?>
</h1>

<div class="card card-tags">
	<div class="card-header">
		<i class="fas fa-folder-open me-2"></i>
		<?= __d('tags', 'Move Tags Between Namespaces') ?>
	</div>
	<div class="card-body">
		<div class="alert alert-info">
			<i class="fas fa-info-circle me-2"></i>
			<?= __d('tags', 'This will move all tags from the source namespace to the target namespace. You can also move tags to or from "no namespace".') ?>
		</div>

		<?= $this->Form->create(null) ?>
		<div class="row">
			<div class="col-md-5 mb-3">
				<label class="form-label" for="from-namespace">
					<i class="fas fa-arrow-right me-1 text-danger"></i>
					<?= __d('tags', 'Source Namespace') ?>
				</label>
				<select name="from_namespace" id="from-namespace" class="form-select" required>
					<option value=""><?= __d('tags', '(no namespace)') ?></option>
					<?php foreach ($namespaces as $ns): ?>
					<?php if ($ns !== null): ?>
					<option value="<?= h($ns) ?>"><?= h($ns) ?></option>
					<?php endif; ?>
					<?php endforeach; ?>
				</select>
			</div>

			<div class="col-md-2 d-flex align-items-center justify-content-center mb-3">
				<i class="fas fa-arrow-right fa-2x text-muted"></i>
			</div>

			<div class="col-md-5 mb-3">
				<label class="form-label" for="to-namespace">
					<i class="fas fa-bullseye me-1 text-success"></i>
					<?= __d('tags', 'Target Namespace') ?>
				</label>
				<div class="input-group">
					<select name="to_namespace" id="to-namespace-select" class="form-select">
						<option value=""><?= __d('tags', '(no namespace)') ?></option>
						<?php foreach ($namespaces as $ns): ?>
						<?php if ($ns !== null): ?>
						<option value="<?= h($ns) ?>"><?= h($ns) ?></option>
						<?php endif; ?>
						<?php endforeach; ?>
					</select>
				</div>
				<div class="mt-2">
					<label class="form-label small text-muted" for="to-namespace-new"><?= __d('tags', 'Or enter a new namespace:') ?></label>
					<input type="text" name="to_namespace" id="to-namespace-new" class="form-control" placeholder="<?= __d('tags', 'New namespace name...') ?>">
				</div>
			</div>
		</div>

		<div class="d-flex gap-2">
			<button type="submit" class="btn btn-primary">
				<i class="fas fa-exchange-alt me-1"></i>
				<?= __d('tags', 'Move Tags') ?>
			</button>
			<a href="<?= $this->Url->build(['action' => 'index']) ?>" class="btn btn-outline-secondary">
				<?= __d('tags', 'Abort') ?>
			</a>
		</div>
		<?= $this->Form->end() ?>
	</div>
</div>

<?php if ($namespaces): ?>
<div class="card card-tags mt-4">
	<div class="card-header">
		<i class="fas fa-list me-2"></i>
		<?= __d('tags', 'Current Namespaces') ?>
	</div>
	<div class="card-body p-0">
		<table class="table table-hover mb-0">
			<thead class="table-light">
				<tr>
					<th><?= __d('tags', 'Namespace') ?></th>
					<th class="text-end"><?= __d('tags', 'Actions') ?></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ($namespaces as $ns): ?>
				<tr>
					<td>
						<?php if ($ns === null): ?>
						<em class="text-muted"><?= __d('tags', '(no namespace)') ?></em>
						<?php else: ?>
						<code><?= h($ns) ?></code>
						<?php endif; ?>
					</td>
					<td class="text-end">
						<a href="<?= $this->Url->build(['action' => 'index', '?' => ['namespace' => $ns ?? '']]) ?>" class="btn btn-sm btn-outline-secondary">
							<i class="fas fa-eye me-1"></i>
							<?= __d('tags', 'View Tags') ?>
						</a>
					</td>
				</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	</div>
</div>
<?php endif; ?>

<?php $this->append('script'); ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
	var select = document.getElementById('to-namespace-select');
	var input = document.getElementById('to-namespace-new');

	// Clear input when select changes
	select.addEventListener('change', function() {
		if (this.value) {
			input.value = '';
		}
	});

	// Clear select when input is typed
	input.addEventListener('input', function() {
		if (this.value) {
			select.value = '';
		}
	});
});
</script>
<?php $this->end(); ?>
