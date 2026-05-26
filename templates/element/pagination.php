<?php
/**
 * Standalone pagination element with Bootstrap 5 styling.
 *
 * Sets explicit templates to avoid style leakage from app templates.
 *
 * @var \Cake\View\View $this
 * @var string|null $counterText
 */

if (!$this->Paginator->hasPage()) {
	return;
}

$counterText ??= __d('tags', 'Page {{page}} of {{pages}}, showing {{current}} record(s) out of {{count}} total');

$this->Paginator->setTemplates([
	'nextActive' => '<li class="page-item"><a class="page-link" rel="next" href="{{url}}">{{text}}</a></li>',
	'nextDisabled' => '<li class="page-item disabled"><span class="page-link">{{text}}</span></li>',
	'prevActive' => '<li class="page-item"><a class="page-link" rel="prev" href="{{url}}">{{text}}</a></li>',
	'prevDisabled' => '<li class="page-item disabled"><span class="page-link">{{text}}</span></li>',
	'first' => '<li class="page-item"><a class="page-link" href="{{url}}">{{text}}</a></li>',
	'last' => '<li class="page-item"><a class="page-link" href="{{url}}">{{text}}</a></li>',
	'number' => '<li class="page-item"><a class="page-link" href="{{url}}">{{text}}</a></li>',
	'current' => '<li class="page-item active"><span class="page-link">{{text}}</span></li>',
]);
?>
<nav class="mt-3" aria-label="<?= __d('tags', 'Page navigation') ?>">
	<ul class="pagination pagination-sm justify-content-center mb-2">
		<?= $this->Paginator->first('«') ?>
		<?= $this->Paginator->prev('‹') ?>
		<?= $this->Paginator->numbers() ?>
		<?= $this->Paginator->next('›') ?>
		<?= $this->Paginator->last('»') ?>
	</ul>
	<p class="text-center text-muted small mb-0">
		<?= $this->Paginator->counter($counterText) ?>
	</p>
</nav>
