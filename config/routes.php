<?php
declare(strict_types=1);

use Cake\Routing\Route\DashedRoute;
use Cake\Routing\RouteBuilder;

/**
 * @var \Cake\Routing\RouteBuilder $routes
 */
$routes->prefix('Admin', function (RouteBuilder $routes): void {
	$routes->plugin('Tags', ['path' => '/tags'], function (RouteBuilder $routes): void {
		$routes->setRouteClass(DashedRoute::class);

		// Dashboard
		$routes->connect('/', ['controller' => 'TagsDashboard', 'action' => 'index']);

		// Tags CRUD
		$routes->connect('/tags', ['controller' => 'Tags', 'action' => 'index']);
		$routes->connect('/tags/add', ['controller' => 'Tags', 'action' => 'add']);
		$routes->connect('/tags/edit/{id}', ['controller' => 'Tags', 'action' => 'edit'], ['pass' => ['id']]);
		$routes->connect('/tags/view/{id}', ['controller' => 'Tags', 'action' => 'view'], ['pass' => ['id']]);
		$routes->connect('/tags/delete/{id}', ['controller' => 'Tags', 'action' => 'delete'], ['pass' => ['id']]);

		// Merge
		$routes->connect('/tags/merge', ['controller' => 'Tags', 'action' => 'merge']);
		$routes->connect('/tags/merge-preview', ['controller' => 'Tags', 'action' => 'mergePreview']);

		// Maintenance
		$routes->connect('/tags/duplicates', ['controller' => 'Tags', 'action' => 'duplicates']);
		$routes->connect('/tags/delete-orphaned', ['controller' => 'Tags', 'action' => 'deleteOrphaned']);
		$routes->connect('/tags/recalculate-counters', ['controller' => 'Tags', 'action' => 'recalculateCounters']);

		// Tools
		$routes->connect('/tags/export', ['controller' => 'Tags', 'action' => 'export']);
		$routes->connect('/tags/change-namespace', ['controller' => 'Tags', 'action' => 'changeNamespace']);

		$routes->fallbacks(DashedRoute::class);
	});
});
