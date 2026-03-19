<?php
/**
 * Tags Admin Sidebar Navigation
 *
 * @var \Cake\View\View $this
 */

$controller = $this->getRequest()->getParam('controller');
$action = $this->getRequest()->getParam('action');

$isActive = function (string $c, ?array $actions = null) use ($controller, $action): string {
	if ($controller !== $c) {
		return '';
	}
	if ($actions === null) {
		return 'active';
	}

	return in_array($action, $actions, true) ? 'active' : '';
};
?>
<aside class="tags-sidebar d-none d-lg-block">
	<!-- Navigation -->
	<div class="nav-section">
		<div class="nav-section-title"><?= __d('tags', 'Navigation') ?></div>
		<nav class="nav flex-column">
			<a class="nav-link <?= $isActive('TagsDashboard', ['index']) ?>" href="<?= $this->Url->build(['plugin' => 'Tags', 'prefix' => 'Admin', 'controller' => 'TagsDashboard', 'action' => 'index']) ?>">
				<i class="fas fa-tachometer-alt"></i>
				<?= __d('tags', 'Dashboard') ?>
			</a>
			<a class="nav-link <?= $isActive('Tags', ['index', 'view', 'add', 'edit']) ?>" href="<?= $this->Url->build(['plugin' => 'Tags', 'prefix' => 'Admin', 'controller' => 'Tags', 'action' => 'index']) ?>">
				<i class="fas fa-tags"></i>
				<?= __d('tags', 'Tags') ?>
			</a>
		</nav>
	</div>

	<!-- Actions -->
	<div class="nav-section">
		<div class="nav-section-title"><?= __d('tags', 'Actions') ?></div>
		<nav class="nav flex-column">
			<a class="nav-link <?= $isActive('Tags', ['add']) ?>" href="<?= $this->Url->build(['plugin' => 'Tags', 'prefix' => 'Admin', 'controller' => 'Tags', 'action' => 'add']) ?>">
				<i class="fas fa-plus"></i>
				<?= __d('tags', 'Add Tag') ?>
			</a>
			<a class="nav-link <?= $isActive('Tags', ['merge', 'mergePreview']) ?>" href="<?= $this->Url->build(['plugin' => 'Tags', 'prefix' => 'Admin', 'controller' => 'Tags', 'action' => 'merge']) ?>">
				<i class="fas fa-compress-arrows-alt"></i>
				<?= __d('tags', 'Merge Tags') ?>
			</a>
			<a class="nav-link <?= $isActive('Tags', ['duplicates']) ?>" href="<?= $this->Url->build(['plugin' => 'Tags', 'prefix' => 'Admin', 'controller' => 'Tags', 'action' => 'duplicates']) ?>">
				<i class="fas fa-clone"></i>
				<?= __d('tags', 'Find Duplicates') ?>
			</a>
			<a class="nav-link <?= $isActive('Tags', ['orphaned']) ?>" href="<?= $this->Url->build(['plugin' => 'Tags', 'prefix' => 'Admin', 'controller' => 'Tags', 'action' => 'orphaned']) ?>">
				<i class="fas fa-unlink"></i>
				<?= __d('tags', 'Orphaned Tags') ?>
			</a>
			<a class="nav-link <?= $isActive('Tags', ['changeNamespace']) ?>" href="<?= $this->Url->build(['plugin' => 'Tags', 'prefix' => 'Admin', 'controller' => 'Tags', 'action' => 'changeNamespace']) ?>">
				<i class="fas fa-exchange-alt"></i>
				<?= __d('tags', 'Change Namespace') ?>
			</a>
		</nav>
	</div>

	<!-- Tools -->
	<div class="nav-section">
		<div class="nav-section-title"><?= __d('tags', 'Tools') ?></div>
		<nav class="nav flex-column">
			<a class="nav-link" href="<?= $this->Url->build(['plugin' => 'Tags', 'prefix' => 'Admin', 'controller' => 'Tags', 'action' => 'export']) ?>">
				<i class="fas fa-download"></i>
				<?= __d('tags', 'Export CSV') ?>
			</a>
		</nav>
	</div>
</aside>
