<?php
/*
Plugin Name: Metapic
Plugin URI: http://metapic.se/
Description: Metapic image tagging
Version: 1.1
Author: Metapic
Author URI: http://metapic.se/
License: GPL v2
Text Domain: metapic
Network: true
*/

namespace MetaPic;

global $wp_mtpc;

$plugin_dir = dirname( __FILE__ );
$plugin_url = plugins_url() . '/' . basename( __DIR__ );

require_once( $plugin_dir . '/vendor/autoload.php' );
require_once( $plugin_dir . '/classes/WP_MTPC.php' );

$wp_mtpc = new WP_MTPC( $plugin_dir, $plugin_url );

register_activation_hook( __FILE__, function () use ( $wp_mtpc ) {
	$wp_mtpc->activate();
} );