<?php
// For full list of configs see behavior and helper
return [
	'Tags' => [
		'slugBehavior' => true, // true = auto detect slugging
		'strategy' => 'string', // string or array
		'delimiter' => ',', // separating the tags
		'separator' => null, // for namespace prefix, e.g.: ':'
	]
];
