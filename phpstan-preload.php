<?php

// Tell Symfony we are not running PHPUnit
$_SERVER['APP_ENV'] = 'dev';
$_SERVER['APP_DEBUG'] = '1';

// prevent Symfony PHPUnit bridge from loading tests/bootstrap.php
if (!defined('PHPUNIT_COMPOSER_INSTALL')) {
    define('PHPUNIT_COMPOSER_INSTALL', __DIR__ . '/vendor/autoload.php');
}