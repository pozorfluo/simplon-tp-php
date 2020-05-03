<?php

/**
 * Setup
 * Retrieve configuration
 * Run the app !
 * 
 * note
 *   This is is the main entry point
 * 
 * todo
 *   - [x] Redirect to parameterized index.php
 *   - [x] Use Dispatcher to call Controller/Action/Param
 *   - [x] Use Controller to request, filter, hand over Model data
 *   - [x] Use View to compose model data over layout, template
 *     + [x] Inline css, js when rendering layout, templates
 *   - [ ] Plug in Model
 *     + [ ] Use Validatable interface to check Entity going in and out
 *   - [x] Implement a simple file cache
 *     + [ ] Allow for Controller, Model, View to invalidate cached files
 *     + [ ] Handle getting hammered with requests that resolve to a valid
 *           Controller but end up swamping the cache because of distinct
 *           query strings filled with junk parameters
 *     + [ ] Handle requests resolving to super long file name more gracefully
 *     + [x] Check if all characters allowed in a query string are valid in
 *           a filename
 *       - [ ] Consider a rewrite rule or some validation
 *   - [x] Use a configuration file
 *   - [ ] Considered supporting Deferred components that are rendered via Js 
 *         hooks and placeholders after all regular components are fist pushed 
 *         and painted.
 */

declare(strict_types=1);

define('ROOT', __DIR__ . '/');

require ROOT . 'src/Helpers/AutoLoader.php';

use Helpers\Dispatcher as Dispatcher;
use Controllers\Homepage;

//------------------------------------------------------------------ session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
//------------------------------------------------------------------- config
/**
 * Get list of registered components, database configs, etc... from config file
 * or provide/build some defaults
 */
$t = microtime(true);

$config_path = ROOT . '.env';
$config_exists = is_file($config_path);


if ($config_exists) {
    $config = json_decode(file_get_contents($config_path), true);
}

if (!isset($config['components'])) {
    $src_dir = new RecursiveDirectoryIterator(ROOT . 'src/');
    $iterator = new RecursiveIteratorIterator($src_dir);
    $php_files = new RegexIterator(
        $iterator,
        '/^.+\.php$/i',
        RecursiveRegexIterator::GET_MATCH
    );

    foreach ($php_files as $php_file) {
        $component = array_slice(explode('/', $php_file[0]), -2);
        $config['components'][$component[0]][] = substr($component[1], 0, -4);
    }
    $config_exists = false;
    echo 'components config missing ! Defaults emitted. <br />';
}
if (!isset($config['db_configs'])) {
    $config['db_configs'] = [
        'default' => [
            'DB_DRIVER' => 'mysql',
            'DB_HOST' => '127.0.0.1',
            'DB_PORT' => '3306',
            'DB_NAME' => 'default',
            'DB_CHARSET' => 'utf8mb4',
            'DB_USER' => null,
            'DB_PASSWORD' => null,
        ]
    ];
    $config_exists = false;
    echo 'db_configs config missing ! Defaults emitted.<br />';
}

$time_spent['config'] = (microtime(true) - $t);

//---------------------------------------------------------------------- run
$t = microtime(true);

$dispatcher = new Dispatcher($config);
$dispatcher->call()->cache();

$time_spent['serving_page'] = (microtime(true) - $t);
//------------------------------------------------------------------- config
/**
 * Serialize config to file if needed once the page is served 
 */
$t = microtime(true);

if (!$config_exists) {
    $config_file = fopen($config_path, 'w');
    fwrite($config_file, json_encode($config));
    fclose($config_file);
}

$time_spent['serialize_config'] = (microtime(true) - $t);


//-------------------------------------------------------------------- debug
$t = microtime(true);

require ROOT . 'src/Helpers/Utilities.php';
require ROOT . 'src/Templates/GlobalsDump.php';

$time_spent['display_debug'] = (microtime(true) - $t);
