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
*/

use MetaPic\User_Api;
use MetaPic\UserApiClient;

call_user_func(function () {
	$plugin_url = plugins_url() . '/' . basename(__DIR__);
	$plugin_dir = dirname(__FILE__);
	$mce_plugin_name = "metapic";
	require($plugin_dir . '/vendor/autoload.php');

	// echo "hej";
	/* @var User_Api $user_api */
	/*
	$user_api = require($plugin_dir.'/api/user.php');
	if (!$user_api->is_active()) return;

	$user = $user_api->get_user();
	$token = $user_api->get_user_token();
	$config = $user_api->get_user_config();
*/
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

	/* add_action('wp_footer', function () use ( $plugin_dir, $plugin_url, $mce_plugin_name ) {
		 //wp_enqueue_script( 'iframeScript',"http://".$options['uri_string'].'/javascript/iframeScript.js', array(), '1.0.0', true );

		 //http://metapic.se/javascript/metapic/classes/Load.js" id="metapic_load" metapic_userid="2"

		 //js/vendor/metapic/loading.js" id="metapic_load" metapic_userid="<?= $user["id"] ?>" metapic_no_login="true" metapic_async_load="false"></script>
		 ///js/vendor/metapic/metapic.preLoginNoLogin.js" async></script>

	 });
 */


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

	/*
	add_action( 'admin_print_footer_scripts', function() use ($user, $config, $token, $plugin_dir) {
		require($plugin_dir."/templates/admin-js.php");
	}, 100);
*/

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

	add_action('admin_init', function () {
		$options = get_option('metapic_options');
		register_setting('metapic_options', 'metapic_options', function ($input) {
			$options = get_option('metapic_options');
			$options['email_string'] = trim($input['email_string']);
			$options['password_string'] = trim($input['password_string']);
			$options['uri_string'] = trim($input['uri_string']);

			return $options;
		});

		add_settings_section('plugin_main', 'Login', function () {
			echo '<p>Please login to your Metapic account</p>';
		}, 'plugin');

		add_settings_field('email_field', 'Email', function () use ($options) {
			echo "<input id='plugin_text_string' name='metapic_options[email_string]' size='40' type='text' value='{$options['email_string']}' />";
		}, 'plugin', 'plugin_main');

		add_settings_field('password_field', 'Password', function () use ($options) {
			echo "<input id='plugin_text_string' type='password' name='metapic_options[password_string]' size='40' type='text' value='{$options['password_string']}' />";
		}, 'plugin', 'plugin_main');

		add_settings_section('plugin_advanced', 'Advanced', function () {
			echo '<p>Advanced settings</p>';
		}, 'plugin');

		add_settings_field('uri_field', 'Address to the server', function () use ($options) {
			echo "<input id='plugin_text_string' name='metapic_options[uri_string]' size='40' type='text' value='{$options['uri_string']}' />";
		}, 'plugin', 'plugin_advanced');
	});

	add_action('admin_menu', function () use ($plugin_dir, $plugin_url) {
		add_options_page('Metapic', 'Metapic', 'manage_options', 'metapic_settings', function () use ($plugin_dir, $plugin_url) {
			require($plugin_dir . "/templates/metapic-options.php");
		});
	});
});
/*
add_filter('tiny_mce_before_init', 'vipx_filter_tiny_mce_before_init');
function vipx_filter_tiny_mce_before_init( $options ) {
    if ( ! isset( $options['extended_valid_elements'] ) ) {
        $options['extended_valid_elements'] = '';
    } else {
        $options['extended_valid_elements'] .= ',';
    }

    if ( ! isset( $options['custom_elements'] ) ) {
        $options['custom_elements'] = '';
    } else {
        $options['custom_elements'] .= ',';
    }

    $options['extended_valid_elements'] .= 'img[class|src|border=0|alt|title|hspace|vspace|width|height|align|onmouseover|onmouseout|name|data-metapic-id|data-metapic-tags]';
    $options['custom_elements']         .= 'div[contenteditable|class|id|style]';
    return $options;
}
*/
//options parts
