<?php
/**
 * Test suite bootstrap.
 *
 * This function is used to find the location of CakePHP whether CakePHP
 * has been installed as a dependency of the plugin, or the plugin is itself
 * installed as a dependency of an application.
 */

use Cake\Cache\Cache;
use Cake\Core\Configure;
use Cake\Core\Plugin;
use Cake\Datasource\ConnectionManager;
use Cake\TestSuite\Fixture\SchemaLoader;
use Tags\TagsPlugin;
use Tools\ToolsPlugin;

if (!defined('DS')) {
	define('DS', DIRECTORY_SEPARATOR);
}
define('ROOT', dirname(__DIR__));
define('APP_DIR', 'src');
// Point app constants to the test app.
define('TESTS', ROOT . DS . 'tests' . DS);
define('TEST_ROOT', ROOT . DS . 'tests' . DS . 'test_app' . DS);
define('APP', TEST_ROOT . APP_DIR . DS);
define('TEST_FILES', ROOT . DS . 'tests' . DS . 'test_files' . DS);
define('TMP', ROOT . DS . 'tmp' . DS);
if (!is_dir(TMP)) {
	mkdir(TMP, 0770, true);
}
define('CONFIG', TESTS . 'config' . DS);
define('LOGS', TMP . 'logs' . DS);
define('CACHE', TMP . 'cache' . DS);
define('CAKE_CORE_INCLUDE_PATH', ROOT . '/vendor/cakephp/cakephp');
define('CORE_PATH', CAKE_CORE_INCLUDE_PATH . DS);
define('CAKE', CORE_PATH . APP_DIR . DS);

require dirname(__DIR__) . '/vendor/autoload.php';
require CORE_PATH . 'config/bootstrap.php';
require CAKE . 'functions.php';

Configure::write('App', [
	'namespace' => 'TestApp',
	'encoding' => 'utf-8',
]);

Plugin::getCollection()->add(new TagsPlugin());
Plugin::getCollection()->add(new ToolsPlugin());

require __DIR__ . '/config/routes.php';

$cache = [
	'default' => [
		'engine' => 'File',
	],
	'_cake_core_' => [
		'className' => 'File',
		'prefix' => 'crud_myapp_cake_core_',
		'path' => CACHE . 'persistent/',
		'serialize' => true,
		'duration' => '+10 seconds',
	],
	'_cake_model_' => [
		'className' => 'File',
		'prefix' => 'crud_my_app_cake_model_',
		'path' => CACHE . 'models/',
		'serialize' => 'File',
		'duration' => '+10 seconds',
	],
];
Cache::setConfig($cache);

// Ensure default test connection is defined
if (!getenv('DB_URL')) {
	putenv('DB_URL=sqlite:///:memory:');
}
ConnectionManager::setConfig('test', [
	'url' => getenv('DB_URL') ?: null,
	'timezone' => 'UTC',
	'quoteIdentifiers' => false,
	'cacheMetadata' => true,
]);

Configure::write('debug', true);

if (env('FIXTURE_SCHEMA_METADATA')) {
	$loader = new SchemaLoader();
	$loader->loadInternalFile(env('FIXTURE_SCHEMA_METADATA'));
}
