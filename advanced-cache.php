<?php 




$config = '/home/ift/cestaportuguesa.pt/wp-content/breeze-config/breeze-config.php';
if ( empty( $config ) || ! @file_exists( $config ) ) { return; }
$GLOBALS['breeze_config'] = include $config;
if ( empty( $GLOBALS['breeze_config'] ) || empty( $GLOBALS['breeze_config']['cache_options']['breeze-active'] ) ) { return; }
if ( @file_exists( '/home/ift/cestaportuguesa.pt/wp-content/plugins/breeze/inc/cache/execute-cache.php' ) ) {
	include_once '/home/ift/cestaportuguesa.pt/wp-content/plugins/breeze/inc/cache/execute-cache.php';
}