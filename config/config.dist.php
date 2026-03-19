<?php
// For full list of configs see behavior and helper

return [
	'Tags' => [
		'slug' => null, // Auto slug using Text::slug()
		'strategy' => 'string', // string or array
		'delimiter' => ',', // separating the tags
		'separator' => null, // for namespace prefix, e.g.: ':'

		// Admin dashboard options
		'standalone' => false, // Set to true for isolated admin that doesn't depend on the host app
		'adminLayout' => null, // null = plugin layout, false = app layout, string = custom layout
	],
];
