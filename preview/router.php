<?php
/**
 * Router for PHP's built-in web server so WordPress pretty permalinks work locally.
 * Usage: php -S 127.0.0.1:8080 -t /path/to/wordpress router.php
 */
$root = $_SERVER['DOCUMENT_ROOT'];
$path = parse_url( $_SERVER['REQUEST_URI'], PHP_URL_PATH );
$file = $root . $path;
if ( '/' !== $path && false === strpos( $path, '..' ) && is_file( $file ) ) {
	return false; // Serve static file / PHP file as-is.
}
if ( is_dir( $root . $path ) && is_file( rtrim( $root . $path, '/' ) . '/index.php' ) && '/' !== $path ) {
	chdir( $root . $path );
	require rtrim( $root . $path, '/' ) . '/index.php';
	return true;
}
$_SERVER['SCRIPT_NAME']     = '/index.php';
$_SERVER['SCRIPT_FILENAME'] = $root . '/index.php';
chdir( $root );
require $root . '/index.php';
