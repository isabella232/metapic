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
	new WP_MTPC($plugin_dir);

	add_action('init', function() {
		add_rewrite_rule('hello.php$', 'index.php?metapic_randomNummber', 'top');
	});

	add_filter('query_vars', function ($query_vars) {
		$query_vars[] = 'metapic_randomNummber';
		return $query_vars;
	});

	add_action('parse_request', function ($wp) {
		if (array_key_exists('metapic_randomNummber', $wp->query_vars)) {
			include 'randomNummber.php';
			exit();
		}
		return;
	});

	add_action('admin_head', function () use ($plugin_dir, $plugin_url, $mce_plugin_name) {
		$options = get_option('metapic_options');

		// check if WYSIWYG is enabled
		if ('true' == get_user_option('rich_editing')) {

			//wp_enqueue_script( 'iframeScript',  , array());
			wp_enqueue_script('iframeScript', $options['uri_string'] . '/javascript/iframeScript.js', array(), '1.0.0', true);
			// Declare script for new button
			add_filter('mce_external_plugins', function ($plugin_array) use ($plugin_url, $mce_plugin_name) {
				$plugin_array[$mce_plugin_name] = $plugin_url . '/js/metapic.js';
				return $plugin_array;
			}
			);

			// Register new button in the editor
			add_filter('mce_buttons', function ($buttons) use ($mce_plugin_name) {
				array_push($buttons, $mce_plugin_name);
				array_push($buttons, "metapicimg");
				array_push($buttons, "metapicCollage");

				return $buttons;
			});
		}
	});

	add_action('admin_enqueue_scripts', function ($styles) use ($plugin_url) {
		wp_enqueue_style('metapic_admin_css', $plugin_url . '/css/metapic.css');
	});


	add_action('admin_enqueue_scripts', function ($hook) use ($plugin_url) {
		if ($hook != 'post.php' && $hook != 'post-new.php') return;

		// Enqueue your scripts here!
//		wp_enqueue_script( 'metapic-jquery-ui', $plugin_url . '/js/vendor/metapic/old/jquery-ui-1.10.4.custom.min.js', ['jquery'] );
//		wp_enqueue_script( 'metapic-image-editor', $plugin_url . '/js/vendor/metapic/old/metapic-image-editor.min.js', ['metapic-angular'] );
//		wp_enqueue_script( 'metapic-jsonp', $plugin_url . '/js/vendor/metapic/old/jsonp-save-handler.min.js', ['metapic-image-editor'] );
	});

	add_filter('mce_css', function ($styles) use ($plugin_url) {
		$styles .= ',' . $plugin_url . '/css/metapic.css';
		return $styles;
	});

	add_action("wp_head", function () use ($plugin_url) {
		// Enqueue frontend scripts here!
		//wp_enqueue_style( 'metapic-frontend-css', $plugin_url . '/js/vendor/metapic/metapic.preLogin.css' );
		//wp_enqueue_style( 'metapic-frontend-css', $plugin_url . '/js/vendor/metapic/metapic.postLogin.css' );
	}, 100);

	add_action("wp_footer", function () use ($plugin_dir, $plugin_url) {
		require($plugin_dir . "/templates/frontend-js.php");
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