<?php

/**
 * Laravel - PHP Built-in Development Server Router
 *
 * This custom server.php suppresses PHP notices from the built-in server
 * that would otherwise corrupt JSON API responses.
 */

// Suppress all errors for the built-in server's logging attempt
// This prevents "file_put_contents(): Write of N bytes failed with errno=32 Broken pipe"
// notices from being prepended to JSON API responses.
error_reporting(0);
ini_set('display_errors', '0');

$publicPath = getcwd();

$uri = urldecode(
    parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? ''
);

// This file allows us to emulate Apache's "mod_rewrite" functionality from the
// built-in PHP web server. This provides a convenient way to test a Laravel
// application without having installed a "real" web server software here.
if ($uri !== '/' && file_exists($publicPath.$uri)) {
    return false;
}

require_once $publicPath.'/index.php';
