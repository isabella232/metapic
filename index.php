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

class MetaPic {
	private $client_id;
	private $secret_key;
	private $user_api;
	private $user = null;

	public function __construct() {
		$this->client_id = get_option("metapic_user_client_id");
		$this->secret_key = get_option("metapic_user_secret_key");
		$this->user_api = new UserApiClient("http://api.metapic.dev", $this->client_id, $this->secret_key);
	}

	public function get_user() {
		return $this->load_user()->then(function() {
			return $this->user;
		});
	}

	public function get_user_token() {
		return $this->load_user()->then(function() {
			return $this->get_from_store("metapic_user_access_token", function($user, $value) {
				return $this->user_api->getUserAccessToken($user["id"]);
			})["token"];
		});
	}

	public function get_user_config() {
		return $this->load_user()->then(function() {
			return $this->get_from_store("metapic_user_config", function($user, $value) {
				return $this->user_api->getUserConfig($user["id"]);
			});
		});
	}

	private function get_from_store($key, Callable $value_to_store, $use_cached = true) {
		$value = get_option($key);
		if (!$value || !$use_cached) {
			$value = $value_to_store($this->user, $value);
			add_option($key, $value);
		}
		return $value;
	}

	private function load_user() {
		$this->user = $this->get_from_store("metapic_user", function($value) {
			return $this->user_api->getUser();
		});
		return $this;
	}

	private function then(Callable $function) {
		return $function();
	}
}

call_user_func( function () {
	$plugin_url = plugins_url() . '/' . basename( __DIR__ );
	$plugin_dir = dirname( __FILE__ );
	$mce_plugin_name = "metapic";
	require($plugin_dir.'/vendor/autoload.php');

	/* @var User_Api $user_api */
	$user_api = require($plugin_dir.'/api/user.php');
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