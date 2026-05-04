<?php
/**
 * Tags Admin Layout
 *
 * Self-contained admin layout using Bootstrap 5 and Font Awesome 6 via CDN.
 * Completely isolated from host application's CSS/JS.
 *
 * @var \Cake\View\View $this
 */
$cspNonce = (string)$this->getRequest()->getAttribute('cspNonce', '');
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title><?= $this->fetch('title') ? strip_tags($this->fetch('title')) . ' - ' : '' ?>Tags Admin</title>

	<!-- Bootstrap 5.3.3 CSS -->
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">

	<!-- Font Awesome 6.7.2 -->
	<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" rel="stylesheet" integrity="sha512-Evv84Mr4kqVGRNSgIGL/F/aIDqQb7xQ2vcrdIwxfjThSH8CSR7PBEakCr51Ck+w+/U6swU2Im1vVX0SVk9ABhg==" crossorigin="anonymous">

	<style>
		:root {
			--tags-primary: #0d6efd;
			--tags-success: #198754;
			--tags-warning: #ffc107;
			--tags-danger: #dc3545;
			--tags-info: #0dcaf0;
			--tags-secondary: #6c757d;
			--tags-dark: #212529;
			--tags-light: #f8f9fa;
			--tags-sidebar-bg: linear-gradient(135deg, #4a5568 0%, #2d3748 100%);
			--tags-sidebar-width: 260px;
		}

		body {
			font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
			background-color: #f4f6f9;
			min-height: 100vh;
		}

		/* Navbar */
		.tags-navbar {
			background: var(--tags-dark);
			box-shadow: 0 2px 4px rgba(0,0,0,0.1);
		}

		.tags-navbar .navbar-brand {
			font-weight: 600;
			color: #fff;
		}

		.tags-navbar .navbar-brand i {
			color: var(--tags-info);
		}

		/* Sidebar */
		.tags-sidebar {
			background: var(--tags-sidebar-bg);
			min-height: calc(100vh - 56px);
			width: var(--tags-sidebar-width);
			position: fixed;
			left: 0;
			top: 56px;
			padding: 1.5rem 0;
			overflow-y: auto;
		}

		.tags-sidebar .nav-section {
			padding: 0 1rem;
			margin-bottom: 1.5rem;
		}

		.tags-sidebar .nav-section-title {
			color: rgba(255,255,255,0.5);
			font-size: 0.75rem;
			text-transform: uppercase;
			letter-spacing: 0.05em;
			padding: 0 0.75rem;
			margin-bottom: 0.5rem;
		}

		.tags-sidebar .nav-link {
			color: rgba(255,255,255,0.8);
			padding: 0.6rem 0.75rem;
			border-radius: 0.375rem;
			margin-bottom: 0.25rem;
			transition: all 0.2s ease;
		}

		.tags-sidebar .nav-link:hover {
			color: #fff;
			background: rgba(255,255,255,0.1);
		}

		.tags-sidebar .nav-link.active {
			color: #fff;
			background: var(--tags-primary);
		}

		.tags-sidebar .nav-link i {
			width: 1.25rem;
			margin-right: 0.5rem;
		}

		/* Main Content */
		.tags-main {
			margin-left: var(--tags-sidebar-width);
			padding: 1.5rem;
			min-height: calc(100vh - 56px);
		}

		/* Stats Cards */
		.stats-card {
			border: none;
			border-radius: 0.5rem;
			box-shadow: 0 2px 4px rgba(0,0,0,0.05);
			transition: transform 0.2s ease, box-shadow 0.2s ease;
			overflow: hidden;
		}

		.stats-card:hover {
			transform: translateY(-2px);
			box-shadow: 0 4px 12px rgba(0,0,0,0.1);
		}

		.stats-card .card-body {
			padding: 1.25rem;
		}

		.stats-card .stats-icon {
			width: 48px;
			height: 48px;
			border-radius: 0.5rem;
			display: flex;
			align-items: center;
			justify-content: center;
			font-size: 1.25rem;
		}

		.stats-card .stats-value {
			font-size: 1.75rem;
			font-weight: 700;
			line-height: 1.2;
		}

		.stats-card .stats-label {
			color: var(--tags-secondary);
			font-size: 0.875rem;
		}

		/* Tables */
		.table-tags {
			background: #fff;
			border-radius: 0.5rem;
			overflow: hidden;
			box-shadow: 0 2px 4px rgba(0,0,0,0.05);
		}

		.table-tags thead th {
			background: var(--tags-light);
			border-bottom: 2px solid #dee2e6;
			font-weight: 600;
			text-transform: uppercase;
			font-size: 0.75rem;
			letter-spacing: 0.05em;
			color: var(--tags-secondary);
		}

		.table-tags tbody tr:hover {
			background-color: rgba(13, 110, 253, 0.05);
		}

		/* Cards */
		.card-tags {
			border: none;
			border-radius: 0.5rem;
			box-shadow: 0 2px 4px rgba(0,0,0,0.05);
		}

		.card-tags .card-header {
			background: var(--tags-light);
			border-bottom: 1px solid #dee2e6;
			font-weight: 600;
		}

		/* Tag badges */
		.tag-badge {
			display: inline-flex;
			align-items: center;
			padding: 0.35em 0.65em;
			font-size: 0.875rem;
			font-weight: 500;
			border-radius: 0.375rem;
		}

		.tag-color-swatch {
			width: 16px;
			height: 16px;
			border-radius: 3px;
			border: 1px solid rgba(0,0,0,0.1);
			display: inline-block;
		}

		/* Column-width utility (replaces inline `<th style="width:30%">`). */
		.tags-col-w-30 { width: 30%; }

		/* Alerts/Flash */
		.tags-flash {
			border: none;
			border-radius: 0.5rem;
			box-shadow: 0 2px 4px rgba(0,0,0,0.1);
		}

		/* Mobile Navigation */
		.tags-mobile-toggle {
			color: #fff;
			background: transparent;
			border: 1px solid rgba(255,255,255,0.2);
			padding: 0.5rem 1rem;
			border-radius: 0.375rem;
		}

		/* Responsive */
		@media (max-width: 991.98px) {
			.tags-sidebar {
				display: none;
				position: fixed;
				z-index: 1040;
				width: 100%;
				top: 56px;
				left: 0;
				padding-bottom: 2rem;
			}

			.tags-sidebar.show {
				display: block;
			}

			.tags-main {
				margin-left: 0;
			}
		}

		/* Form styles */
		.form-label {
			font-weight: 500;
		}

		/* Namespace filter pills */
		.namespace-filters .btn {
			margin-right: 0.25rem;
			margin-bottom: 0.25rem;
		}
	</style>

	<?= $this->fetch('css') ?>
</head>
<body>
	<!-- Top Navbar -->
	<nav class="navbar navbar-expand-lg tags-navbar">
		<div class="container-fluid">
			<a class="navbar-brand" href="<?= $this->Url->build(['plugin' => 'Tags', 'prefix' => 'Admin', 'controller' => 'TagsDashboard', 'action' => 'index']) ?>">
				<i class="fas fa-tags me-2"></i>
				Tags
			</a>

			<?php
			$adminBackUrl = \Cake\Core\Configure::read('Tags.adminBackUrl');
			$hasAdminBack = $adminBackUrl !== null && $adminBackUrl !== '';
			$adminBackLabel = (string)\Cake\Core\Configure::read('Tags.adminBackLabel', __d('tags', 'Back to App'));
			?>
			<?php if ($hasAdminBack) { ?>
			<a class="btn btn-outline-light btn-sm ms-auto" href="<?= $this->Url->build($adminBackUrl) ?>">
				<i class="fas fa-arrow-left me-1"></i>
				<?= h($adminBackLabel) ?>
			</a>
			<?php } ?>

			<!-- Mobile toggle -->
			<button class="navbar-toggler tags-mobile-toggle d-lg-none <?= $hasAdminBack ? 'ms-2' : 'ms-auto' ?>" type="button" data-tags-sidebar-toggle="1">
				<i class="fas fa-bars"></i>
			</button>
		</div>
	</nav>

	<!-- Sidebar -->
	<?= $this->element('Tags.Tags/sidebar') ?>

	<!-- Main Content -->
	<main class="tags-main">
		<!-- Flash Messages -->
		<div class="tags-flash">
			<?= $this->element('Tags.flash/flash') ?>
		</div>

		<?= $this->fetch('content') ?>
	</main>

	<!-- Bootstrap JS -->
	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>

	<script<?= $cspNonce !== '' ? ' nonce="' . h($cspNonce) . '"' : '' ?>>
	// Mobile sidebar toggle (CSP-safe replacement for inline onclick)
	document.querySelectorAll('[data-tags-sidebar-toggle]').forEach(function(btn) {
		btn.addEventListener('click', function() {
			var sidebar = document.querySelector('.tags-sidebar');
			if (sidebar) {
				sidebar.classList.toggle('show');
			}
		});
	});

	// Confirmation dialogs for postButton forms (CSP-safe replacement for postLink + confirm)
	document.querySelectorAll('form[data-confirm-message]').forEach(function(form) {
		form.addEventListener('submit', function(e) {
			if (!confirm(form.dataset.confirmMessage)) {
				e.preventDefault();
			}
		});
	});

	// Tag color swatches (CSP-safe replacement for inline style="background-color:...")
	document.querySelectorAll('.tag-color-swatch[data-tag-color]').forEach(function(el) {
		el.style.backgroundColor = el.dataset.tagColor;
	});
	</script>

	<?= $this->fetch('script') ?>
	<?= $this->fetch('postLink') ?>
</body>
</html>
