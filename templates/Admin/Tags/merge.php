<?php
/**
 * @var \App\View\AppView $this
 * @var array<\Tags\Model\Entity\Tag> $tags
 * @var array $tagsByNamespace
 */
$cspNonce = (string)$this->getRequest()->getAttribute('cspNonce', '');
?>

<h1 class="mb-4">
	<i class="fas fa-compress-arrows-alt me-2 text-muted"></i>
	<?= __d('tags', 'Merge Tags') ?>
</h1>

<div class="card card-tags">
	<div class="card-header">
		<i class="fas fa-info-circle me-2"></i>
		<?= __d('tags', 'Select Tags to Merge') ?>
	</div>
	<div class="card-body">
		<div class="alert alert-info">
			<i class="fas fa-info-circle me-2"></i>
			<?= __d('tags', 'Merging will move all associations from the source tag to the target tag, then delete the source tag. Tags must be in the same namespace.') ?>
		</div>

		<form method="get" action="<?= $this->Url->build(['action' => 'mergePreview']) ?>">
			<div class="row">
				<div class="col-md-5 mb-3">
					<label class="form-label" for="source">
						<i class="fas fa-arrow-right me-1 text-danger"></i>
						<?= __d('tags', 'Source Tag (will be deleted)') ?>
					</label>
					<select name="source" id="source" class="form-select" required>
						<option value=""><?= __d('tags', 'Select source tag...') ?></option>
						<?php foreach ($tagsByNamespace as $namespace => $nsTags) : ?>
						<optgroup label="<?= $namespace ? h($namespace) : __d('tags', '(no namespace)') ?>">
							<?php foreach ($nsTags as $tag) : ?>
							<option value="<?= $tag->id ?>" data-namespace="<?= h($tag->namespace ?? '') ?>">
								<?= h($tag->label) ?> (<?= $tag->counter ?> uses)
							</option>
							<?php endforeach; ?>
						</optgroup>
						<?php endforeach; ?>
					</select>
				</div>

				<div class="col-md-2 d-flex align-items-center justify-content-center mb-3">
					<i class="fas fa-arrow-right fa-2x text-muted"></i>
				</div>

				<div class="col-md-5 mb-3">
					<label class="form-label" for="target">
						<i class="fas fa-bullseye me-1 text-success"></i>
						<?= __d('tags', 'Target Tag (will receive associations)') ?>
					</label>
					<select name="target" id="target" class="form-select" required>
						<option value=""><?= __d('tags', 'Select target tag...') ?></option>
						<?php foreach ($tagsByNamespace as $namespace => $nsTags) : ?>
						<optgroup label="<?= $namespace ? h($namespace) : __d('tags', '(no namespace)') ?>">
							<?php foreach ($nsTags as $tag) : ?>
							<option value="<?= $tag->id ?>" data-namespace="<?= h($tag->namespace ?? '') ?>">
								<?= h($tag->label) ?> (<?= $tag->counter ?> uses)
							</option>
							<?php endforeach; ?>
						</optgroup>
						<?php endforeach; ?>
					</select>
				</div>
			</div>

			<div class="d-flex gap-2">
				<button type="submit" class="btn btn-primary">
					<i class="fas fa-search me-1"></i>
					<?= __d('tags', 'Preview Merge') ?>
				</button>
				<a href="<?= $this->Url->build(['action' => 'index']) ?>" class="btn btn-outline-secondary">
					<?= __d('tags', 'Abort') ?>
				</a>
			</div>
		</form>
	</div>
</div>

<?php $this->append('script'); ?>
<script<?= $cspNonce !== '' ? ' nonce="' . h($cspNonce) . '"' : '' ?>>
document.addEventListener('DOMContentLoaded', function() {
	var sourceSelect = document.getElementById('source');
	var targetSelect = document.getElementById('target');

	function filterTargetOptions() {
		var sourceOption = sourceSelect.options[sourceSelect.selectedIndex];
		var sourceNamespace = sourceOption ? sourceOption.dataset.namespace : '';
		var sourceValue = sourceSelect.value;

		// Show/hide target options based on namespace match
		Array.from(targetSelect.options).forEach(function(option) {
			if (option.value === '' || option.parentElement.tagName === 'SELECT') {
				return; // Skip placeholder
			}

			var optionNamespace = option.dataset.namespace || '';

			// Hide if same as source or different namespace
			if (option.value === sourceValue) {
				option.hidden = true;
				option.disabled = true;
			} else if (sourceValue && optionNamespace !== sourceNamespace) {
				option.hidden = true;
				option.disabled = true;
			} else {
				option.hidden = false;
				option.disabled = false;
			}
		});

		// Reset target if current selection is now hidden
		var currentTarget = targetSelect.options[targetSelect.selectedIndex];
		if (currentTarget && (currentTarget.hidden || currentTarget.disabled)) {
			targetSelect.value = '';
		}
	}

	sourceSelect.addEventListener('change', filterTargetOptions);
});
</script>
<?php $this->end();
