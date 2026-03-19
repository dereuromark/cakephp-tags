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
		</nav>
	</div>
</aside>
