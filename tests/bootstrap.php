<?php
/**
 * Bootstrap for eea-ticketing tests
 */

use EETests\bootstrap\AddonLoader;

$core_tests_dir = dirname(dirname(dirname(__FILE__))) . '/event-espresso-core/tests/';
require $core_tests_dir . 'includes/CoreLoader.php';
require $core_tests_dir . 'includes/AddonLoader.php';

define('EE_TICKETING_PLUGIN_DIR', dirname(dirname(__FILE__)) . '/');
define('EE_TICKETING_TESTS_DIR', EE_TICKETING_PLUGIN_DIR . 'tests');


$addon_loader = new AddonLoader(
    EE_TICKETING_TESTS_DIR,
    EE_TICKETING_PLUGIN_DIR,
    'eea-ticketing.php'
);
$addon_loader->init();
