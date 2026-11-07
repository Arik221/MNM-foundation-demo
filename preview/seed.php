<?php
/**
 * Activates the plugin + theme and installs the demonstration content (CLI).
 * php preview/seed.php /path/to/wordpress
 */
if ( 'cli' !== PHP_SAPI ) { exit; }
$root = rtrim( $argv[1] ?? '', '/' );
$_SERVER['HTTP_HOST'] = '127.0.0.1:8080';
$_SERVER['REQUEST_URI'] = '/';
require $root . '/wp-load.php';
require_once ABSPATH . 'wp-admin/includes/plugin.php';
wp_set_current_user( 1 );
if ( file_exists( get_theme_root() . '/mnm-foundation-theme/style.css' ) ) { switch_theme( 'mnm-foundation-theme' ); }
$r = activate_plugin( 'mnm-foundation-core/mnm-foundation-core.php' );
if ( is_wp_error( $r ) ) { fwrite( STDERR, $r->get_error_message() . "\n" ); exit( 1 ); }
mnm_seed_demo();
echo "Demo content installed.\n";
