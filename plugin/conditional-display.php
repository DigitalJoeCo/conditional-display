<?php
/**
 * Plugin Name: Conditional Scripts
 * Description: Load scripts conditionally
 * Version: 1.0.3
 * Author: Digital Dyve
 * License: GPL2
 **/

if (! defined('ABSPATH')) {
    exit;
}

if (! file_exists($composer = __DIR__ . '/vendor/autoload.php')) {
    throw new \Exception('Please run "composer install"');
}

require_once $composer;

\DigitalDyve\ConditionalDisplay\ConditionalDisplay::getInstance()->init();
