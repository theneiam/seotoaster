<?php
date_default_timezone_set('UTC');

// @todo Add multisite support
defined('CORE') || define('CORE', realpath(dirname(__FILE__) . '/core'));
defined('SITE_NAME') || define('SITE_NAME', '');

// Define path to application directory
defined('APPLICATION_PATH')
    || define('APPLICATION_PATH', CORE . '/application');

// Define application environment
defined('APPLICATION_ENV')
    || define('APPLICATION_ENV', (getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : 'production'));

// Ensure library/ is on include_path
set_include_path(implode(PATH_SEPARATOR, array(
    realpath(APPLICATION_PATH . '/../library'),
    get_include_path(),
)));

/** Zend_Application */
require_once 'Zend/Application.php';

// Create application, bootstrap, and run
$application = new Zend_Application(
    APPLICATION_ENV,
    APPLICATION_PATH . '/configs/application.ini'
);
$application->bootstrap()
            ->run();