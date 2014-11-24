<?php
/*
Plugin Name: Metapic
Plugin URI: http://metapic.se/
Description: Metapic image tagging
Version: 0.1.0
Author: Metapic
Author URI: http://metapic.se/
License: Commercial
Text Domain: metapic
*/

use MetaPic\User_Api;
use MetaPic\UserApiClient;

call_user_func( function () {
	$plugin_url = plugins_url() . '/' . basename( __DIR__ );
	$plugin_dir = dirname( __FILE__ );
	$mce_plugin_name = "metapic";
	require($plugin_dir.'/vendor/autoload.php');

	/* @var User_Api $user_api */
	$user_api = require($plugin_dir.'/api/user.php');
	if (!$user_api->is_active()) return;

	$user = $user_api->get_user();
	$token = $user_api->get_user_token();
	$config = $user_api->get_user_config();

	add_action( 'admin_head', function () use ( $plugin_dir, $plugin_url, $mce_plugin_name ) {

		// check if WYSIWYG is enabled
		if ('true' == get_user_option( 'rich_editing' )) {
			// Declare script for new button
			add_filter( 'mce_external_plugins', function ( $plugin_array ) use ( $plugin_url, $mce_plugin_name ) {
				$plugin_array[$mce_plugin_name] = $plugin_url . '/js/metapic.js';
				return $plugin_array;
			} );

			// Register new button in the editor
			add_filter( 'mce_buttons', function ( $buttons ) use ( $mce_plugin_name ) {
				array_push( $buttons, $mce_plugin_name );
				return $buttons;
			} );
		}

	});

	add_action( 'admin_print_footer_scripts', function() use ($user, $config, $token, $plugin_dir) {
		require($plugin_dir."/templates/admin-js.php");
	}, 100);

	add_action( 'admin_enqueue_scripts', function ( $hook ) use ( $plugin_url ) {
		if ($hook != 'post.php' && $hook != 'post-new.php') return;

		wp_enqueue_style( 'metapic-editor-css', $plugin_url . '/js/vendor/metapic/metapic-image-editor.min.css' );
		wp_enqueue_script( 'metapic-jquery-ui', $plugin_url . '/js/vendor/metapic/jquery-ui-1.10.4.custom.min.js', ['jquery'] );
		wp_enqueue_script( 'angular', $plugin_url . '/js/vendor/metapic/angular.min.js', ['jquery'] );
		wp_enqueue_script( 'metapic-image-editor', $plugin_url . '/js/vendor/metapic/metapic-image-editor.min.js', ['angular'] );
		wp_enqueue_script( 'metapic-jsonp-handler', $plugin_url . '/js/vendor/metapic/jsonp-save-handler.min.js', ['metapic-image-editor'] );
		//wp_enqueue_script( 'metapic-init', $plugin_url . '/js/init.js', ['metapic-jsonp-handler'] );
	});

	add_filter( 'mce_css', function ( $styles ) use ( $plugin_url ) {
		$styles .= ',' . $plugin_url . '/css/metapic.css';
		return $styles;
	} );

	add_action("wp_head", function() use ($plugin_url) {
		wp_enqueue_style( 'metapic-frontend-css', '//s3-eu-west-1.amazonaws.com/metapic-cdn/site/css/remote/metapic.min.css' );
	}, 100);


	add_action("wp_footer", function() use ($user, $config, $token, $plugin_dir) {
		require($plugin_dir."/templates/frontend-js.php");
	}, 100);
});