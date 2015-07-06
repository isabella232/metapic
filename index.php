<?php
/*
Plugin Name: Metapic
Plugin URI: http://metapic.se/
Description: Metapic image tagging
Version: 0.1.0
Author: Metapic
Author URI: http://metapic.se/
License: GPL v2
Text Domain: metapic
Network: true
*/

call_user_func(function () {
	$plugin_dir = dirname(__FILE__);
	$plugin_url = plugins_url() . '/' . basename(__DIR__);
	$mce_plugin_name = "metapic";
	require_once($plugin_dir . '/vendor/autoload.php');
	require_once($plugin_dir . '/classes/WP_MTPC.php');
	new WP_MTPC($plugin_dir, $plugin_url);

	add_action('admin_enqueue_scripts', function ($hook) use ($plugin_url) {
		if ($hook != 'post.php' && $hook != 'post-new.php') return;

		// Enqueue your scripts here!
//		wp_enqueue_script( 'metapic-jquery-ui', $plugin_url . '/js/vendor/metapic/old/jquery-ui-1.10.4.custom.min.js', ['jquery'] );
//		wp_enqueue_script( 'metapic-image-editor', $plugin_url . '/js/vendor/metapic/old/metapic-image-editor.min.js', ['metapic-angular'] );
//		wp_enqueue_script( 'metapic-jsonp', $plugin_url . '/js/vendor/metapic/old/jsonp-save-handler.min.js', ['metapic-image-editor'] );
	});



	add_action("wp_head", function () use ($plugin_url) {
		// Enqueue frontend scripts here!
		//wp_enqueue_style( 'metapic-frontend-css', $plugin_url . '/js/vendor/metapic/metapic.preLogin.css' );
		//wp_enqueue_style( 'metapic-frontend-css', $plugin_url . '/js/vendor/metapic/metapic.postLogin.css' );
	}, 100);



	/*
	add_filter( 'all_plugins', function($plugins) {
		// Hide hello dolly plugin
		if(is_plugin_active('metapic/index.php')) {
			unset( $plugins['metapic/index.php'] );
		}
		return $plugins;
	});*/
});