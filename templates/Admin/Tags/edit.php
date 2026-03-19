<?php
/**
 * @var \App\View\AppView $this
 * @var \Tags\Model\Entity\Tag $tag
 * @var array $namespaces
 */
?>

<h1 class="mb-4">
	<i class="fas fa-edit me-2 text-muted"></i>
	<?= __d('tags', 'Edit Tag') ?>
</h1>

<div class="card card-tags">
	<div class="card-body">
		<?= $this->Form->create($tag) ?>
		<div class="row">
			<div class="col-md-6 mb-3">
				<label class="form-label" for="label"><?= __d('tags', 'Label') ?> <span class="text-danger">*</span></label>
				<?= $this->Form->control('label', [
					'label' => false,
					'class' => 'form-control' . ($tag->hasErrors('label') ? ' is-invalid' : ''),
					'placeholder' => __d('tags', 'Enter tag label...'),
				]) ?>
				<?php if ($tag->hasErrors('label')): ?>
				<div class="invalid-feedback"><?= implode(', ', $tag->getError('label')) ?></div>
				<?php endif; ?>
			</div>
			<div class="col-md-6 mb-3">
				<label class="form-label" for="slug"><?= __d('tags', 'Slug') ?> <span class="text-danger">*</span></label>
				<?= $this->Form->control('slug', [
					'label' => false,
					'class' => 'form-control' . ($tag->hasErrors('slug') ? ' is-invalid' : ''),
				]) ?>
				<?php if ($tag->hasErrors('slug')): ?>
				<div class="invalid-feedback"><?= implode(', ', $tag->getError('slug')) ?></div>
				<?php endif; ?>
			</div>
		</div>
		<div class="row">
			<div class="col-md-6 mb-3">
				<label class="form-label" for="namespace"><?= __d('tags', 'Namespace') ?></label>
				<?= $this->Form->control('namespace', [
					'label' => false,
					'class' => 'form-control' . ($tag->hasErrors('namespace') ? ' is-invalid' : ''),
					'placeholder' => __d('tags', 'Optional namespace...'),
					'list' => 'namespace-list',
				]) ?>
				<datalist id="namespace-list">
					<?php foreach ($namespaces as $ns): ?>
					<option value="<?= h($ns) ?>">
					<?php endforeach; ?>
				</datalist>
				<?php if ($tag->hasErrors('namespace')): ?>
				<div class="invalid-feedback"><?= implode(', ', $tag->getError('namespace')) ?></div>
				<?php endif; ?>
			</div>
			<div class="col-md-6 mb-3">
				<label class="form-label" for="color"><?= __d('tags', 'Color') ?></label>
				<div class="input-group">
					<?= $this->Form->control('color', [
						'type' => 'color',
						'label' => false,
						'class' => 'form-control form-control-color',
						'value' => $tag->color ?: '#6c757d',
						'title' => __d('tags', 'Choose a color'),
						'id' => 'color-picker',
					]) ?>
					<?= $this->Form->control('color', [
						'label' => false,
						'class' => 'form-control' . ($tag->hasErrors('color') ? ' is-invalid' : ''),
						'placeholder' => '#FF5733',
						'id' => 'color-text',
					]) ?>
				</div>
				<?php if ($tag->hasErrors('color')): ?>
				<div class="invalid-feedback d-block"><?= implode(', ', $tag->getError('color')) ?></div>
				<?php endif; ?>
				<small class="text-muted"><?= __d('tags', 'Optional hex color (e.g., #FF5733)') ?></small>
			</div>
		</div>
		<div class="d-flex gap-2">
			<?= $this->Form->button('<i class="fas fa-save me-1"></i>' . __d('tags', 'Save'), [
				'class' => 'btn btn-primary',
				'escapeTitle' => false,
			]) ?>
			<a href="<?= $this->Url->build(['action' => 'index']) ?>" class="btn btn-outline-secondary">
				<?= __d('tags', 'Abort') ?>
			</a>
		</div>
		<?= $this->Form->end() ?>
	</div>
</div>

<?php $this->append('script'); ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
	var colorPicker = document.getElementById('color-picker');
	var colorText = document.getElementById('color-text');

	if (colorPicker && colorText) {
		// Sync color picker with text input on load
		if (colorText.value && /^#[0-9A-Fa-f]{6}$/.test(colorText.value)) {
			colorPicker.value = colorText.value;
		}

		colorPicker.addEventListener('input', function() {
			colorText.value = this.value.toUpperCase();
		});
		colorText.addEventListener('input', function() {
			if (/^#[0-9A-Fa-f]{6}$/.test(this.value)) {
				colorPicker.value = this.value;
			}
		});
	}
});
</script>
<?php $this->end(); ?>
