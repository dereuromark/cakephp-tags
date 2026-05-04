<?php
declare(strict_types=1);

/*
 * Example application-level overrides for the Tags plugin.
 *
 * Copy the relevant parts into your app's config (e.g. `config/app_local.php`)
 * and adjust as needed.
 */
return [
	'Tags' => [
		// Admin layout configuration:
		// - null (default): Uses 'Tags.tags' isolated Bootstrap 5 layout
		// - false: Disables plugin layout, uses app's default layout
		// - string: Uses specified layout
		'adminLayout' => null,

		// Back-to-App link in the admin header (opt-in). When set, an outline
		// button appears in the top navbar so admins can escape the
		// plugin-isolated layout. Accepts anything Router::url() takes — Cake
		// URL array, path string, or full URL. Use 'plugin' => false to
		// anchor the builder to the host app rather than the Tags plugin.
		// 'adminBackUrl' => ['plugin' => false, 'prefix' => 'Admin', 'controller' => 'Overview', 'action' => 'index'],
		// 'adminBackLabel' => 'Back to admin', // Optional. Defaults to "Back to App".

		// Standalone mode:
		// - false (default): Extends App\Controller\AppController (inherits app auth, components)
		// - true: Isolated admin that doesn't depend on the host app
		'standalone' => false,

		// Admin access gate (optional, defense-in-depth).
		//
		// Unset = no-op; the host AppController's auth is the only gate.
		// Set to a Closure that receives the current request and returns
		// literal true to grant access; anything else (non-Closure, returns
		// false, returns a truthy non-bool, or throws) yields a 403.
		//
		// Particularly useful when `Tags.standalone` is on (the host
		// AppController is bypassed in that mode, so this gate becomes the
		// only plugin-side protection).
		//
		// Example — restrict to admin role on the cakephp/authentication identity:
		// 'accessCheck' => function (\Cake\Http\ServerRequest $request): bool {
		//     $identity = $request->getAttribute('identity');
		//     return $identity !== null && in_array('admin', (array)$identity->roles, true);
		// },

		// Behavior defaults (override per addBehavior() call as needed):
		// 'taggedCounter' => true,
		// 'strategy' => 'string', // 'string' or 'array'
		// 'delimiter' => ',',
		// 'namespace' => null,
		// 'separator' => null,
		// 'andSeparator' => null,
		// 'orSeparator' => null,
	],
];
