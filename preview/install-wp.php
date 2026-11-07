<?php
/**
 * Installs a fresh WordPress site in the local preview (CLI only).
 * php preview/install-wp.php /path/to/wordpress
 */
if ( 'cli' !== PHP_SAPI ) { exit; }
$root = rtrim( $argv[1] ?? '', '/' );
if ( ! is_file( $root . '/wp-load.php' ) ) { fwrite( STDERR, "Usage: php install-wp.php /path/to/wordpress\n" ); exit( 1 ); }
define( 'WP_INSTALLING', true );
$_SERVER['HTTP_HOST']   = '127.0.0.1:8080';
$_SERVER['REQUEST_URI'] = '/';
require $root . '/wp-load.php';
require_once ABSPATH . 'wp-admin/includes/upgrade.php';
if ( is_blog_installed() ) { echo "Already installed.\n"; exit; }
$pass = getenv( 'MNM_ADMIN_PASS' ) ? getenv( 'MNM_ADMIN_PASS' ) : wp_generate_password( 20, true, false );
$r    = wp_install( 'MNM Foundation', 'admin', 'admin@example.invalid', true, '', $pass );
echo "Installed. Admin user: admin  Password: {$pass}\n(Set MNM_ADMIN_PASS to choose your own. Local preview only.)\n";
