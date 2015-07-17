<?php
use Carbon\Carbon;
use MetaPic\ApiClient;

class WP_MTPC extends stdClass {
	private $api_url = "http://metapic-testapi.herokuapp.com";
	private $plugin_dir;
	private $plugin_url;
	/* @var ApiClient $client */
	private $client;
	private $templateVars = [];
	private $debugMode = false;
	private $accessKey = "metapic_access_token";

	public function __construct($plugin_dir, $plugin_url) {
		$options = get_option('metapic_options');
		$this->debugMode = (defined("MTPC_DEBUG") && MTPC_DEBUG === true);
		$this->plugin_dir = $plugin_dir;
		$this->plugin_url = $plugin_url;
		$this->client = new ApiClient($this->getApiUrl(), get_site_option("mtpc_api_key"), get_site_option("mtpc_secret_key"));

		// echo json_encode($this->client->getUsers());
		$this->setupOptionsPage();
		$this->setupLang();
		$this->setupNetworkOptions();
		$this->setupIframeRoutes();
		if (get_option("mtpc_active_account") && get_option("mtpc_access_token")) {
			$this->setupJsOptions();
			$this->setupDashboardWidget();
		}

		add_filter('wp_kses_allowed_html', function($tags, $context) {
			foreach ($tags as $key => $value) {
				$tags[$key]["data-metapic-id"] = 1;
				$tags[$key]["data-metapic-tags"] = 1;
			}
			return $tags;
		},500, 2);
	}

	private function setupJsOptions() {
		add_filter('tiny_mce_before_init', function($mceInit, $editor_id) {
			$mceInit["mtpc_iframe_url"] = rtrim(get_bloginfo("url"), "/")."/?".$this->accessKey;
			$mceInit["mtpc_plugin_url"] = $this->plugin_url;
			return $mceInit;
		},500,2);

		add_action('admin_head', function () {
			$mce_plugin_name = "metapic";
			$options = get_option('metapic_options');
			// check if WYSIWYG is enabled
			if ('true' == get_user_option('rich_editing')) {

				//wp_enqueue_script( 'iframeScript',  , array());
				//$options['uri_string']="http://metapic-api.localhost";
				wp_enqueue_script('iframeScript', $this->getApiUrl() . '/javascript/iframeScript.js', array(), '1.0.0', true);
				// Declare script for new button
				add_filter('mce_external_plugins', function ($plugin_array) use ($mce_plugin_name) {
					$plugin_array[$mce_plugin_name] = $this->plugin_url . '/js/metapic.js';
					return $plugin_array;
				}
				);

				// Register new button in the editor
				add_filter('mce_buttons', function ($buttons) use ($mce_plugin_name) {
					array_push($buttons, $mce_plugin_name."link");
					array_push($buttons, $mce_plugin_name."img");
					array_push($buttons, $mce_plugin_name."collage");

					return $buttons;
				}
				);
			}
		}
		);

		add_action('admin_enqueue_scripts', function ($styles) {
			wp_enqueue_style('metapic_admin_css', $this->plugin_url . '/css/metapic.css');
		}
		);

		add_filter('mce_css', function ($styles) {
			$styles .= ',' . $this->plugin_url . '/css/metapic.css';
			return $styles;
		}
		);

		add_action("wp_footer", function () {
			require($this->plugin_dir . "/templates/frontend-js.php");
		}, 100
		);

	}

	/**
	 * Sets a status message for the current option page
	 * @param string $message - The message to send
	 * @param string $class - Class for the element. Valid options are updated, error or update-nag
	 */
	private function setStatusMessage($message, $class = "updated") {
		add_settings_error('general', 'settings_updated', $message, $class);
	}

	private function setupOptionsPage() {
		add_action('admin_init', function () {
			$options = get_option('metapic_options');
			register_setting('metapic_options', 'metapic_options', function ($input) {
				$options = get_option('metapic_options');
				switch ($_POST["mtpc_action"]) {
					case "deactivate":
						delete_option("mtpc_active_account");
						delete_option("mtpc_access_token");
						$this->setStatusMessage(__("Account deactivated", "metapic"));
						break;
					default:
						$options = $this->updateOptions($options, $input);
						break;
				}
				return $options;
			});

			$activeAccount = get_option("mtpc_active_account");
			if (!$activeAccount) {
				add_settings_section('plugin_main', __('Login', "metapic"), function () {
					echo '<p>'.__('Please login to your Metapic account', 'metapic').'</p>';
				}, 'plugin'
				);
				add_settings_field('email_field', __('Email', "metapic"), function () use ($options) {
					echo "<input id='plugin_text_string' name='metapic_options[email_string]' size='40' type='text' value='{$options['email_string']}' />";
				}, 'plugin', 'plugin_main'
				);

				add_settings_field('password_field', __('Password', "metapic"), function () use ($options) {
					echo "<input id='plugin_text_string' type='password' name='metapic_options[password_string]' size='40' type='text' value='{$options['password_string']}' />";
				}, 'plugin', 'plugin_main'
				);
			}
			else {
				add_settings_section('plugin_main', __('Your account', "metapic"), function () {
					echo '<p>'.__('You are currently logged in', 'metapic').'</p>';
				}, 'plugin'
				);
			}

			if ($this->debugMode) {
				add_settings_section('plugin_advanced', __('Advanced', 'metapic'), function () {
					echo '<p>'.__('Advanced settings', 'metapic').'</p>';
				}, 'plugin'
				);

				add_settings_field('uri_field', __('Server address', 'metapic'), function () use ($options) {
					echo "<input id='plugin_text_string' name='metapic_options[uri_string]' size='40' type='text' value='{$options['uri_string']}' />";
				}, 'plugin', 'plugin_advanced'
				);
			}
		}
		);

		add_action('admin_menu', function () {
			$isValidClient = get_site_option("mtpc_valid_client");
			if (!is_multisite() || (is_multisite() && $isValidClient)) {
				add_options_page('Metapic', 'Metapic', 'manage_options', 'metapic_settings', function () {
					if (is_multisite())
						$this->getTemplate("metapic-options-ms");
					else
						$this->getTemplate("metapic-options");
				}
				);
			}
		}
		);
	}

	private function updateOptions($options, $input) {
		$options['uri_string'] = trim($input['uri_string'], "/");

		if (!get_option("mtpc_active_account") && !get_option("mtpc_access_token")) {

			if (!is_multisite()) {
				$options['email_string'] = trim($input['email_string']);
				$options['password_string'] = trim($input['password_string']);
				try {
					$user = $this->client->login($options['email_string'], $options['password_string']);
					if ($user) {
						update_option("mtpc_active_account", true);
						update_option("mtpc_email", $options['email_string']);
						update_option("mtpc_id", $user["id"]);
						update_option("mtpc_access_token", $user["access_token"]["access_token"]);
						$this->setStatusMessage(__("Login successful", "metapic"));
					}
					else {
						throw new Exception;
					}
				} catch (Exception $e) {
					delete_option("mtpc_active_account");
					delete_option("mtpc_access_token");
					delete_option("mtpc_email");
					delete_option("mtpc_id");
					$this->setStatusMessage(__("Invalid username or password", "metapic"), "error");
				}
			}
			else {
				$user_email = (isset($_POST["mtpc_email"])) ? $_POST["mtpc_email"] : wp_get_current_user()->user_email;
				$wp_user = get_user_by("email", $user_email);
				if ($wp_user) {
					$user = $this->client->activateUser($wp_user->user_email);
					if ($user["access_token"] == null) {
						$this->client->createUser(array("email" => $wp_user->user_email, "username" => $wp_user->user_login));
						$user = $this->client->activateUser($wp_user->user_email);
						$this->setStatusMessage(__("Account created", "metapic"));
					}
					else {
						$this->setStatusMessage(__("Account activated", "metapic"));
					}
					update_option("mtpc_active_account", true);
					update_option("mtpc_email", $wp_user->user_email);
					update_option("mtpc_id", $user["id"]);
					update_option("mtpc_access_token", $user["access_token"]["access_token"]);
				}
				else {
					$this->setStatusMessage(__("User not found", "metapic"), "error");
				}
			}
		}
		return $options;
	}

	private function setupLang() {
		add_action('plugins_loaded', function () {
			$langPath = basename($this->plugin_dir) . '/lang/';
			load_plugin_textdomain('metapic', false, $langPath);
		});

		add_filter('mce_external_languages', function($locales) {
			$ds = DIRECTORY_SEPARATOR;
			$path = $this->plugin_dir . $ds . 'tinymce-lang' . $ds. 'metapic-langs.php';
			$locales ['metapic'] = $path;
			return $locales;
		});
	}

	private function setupNetworkOptions() {
		add_action('network_admin_menu', function () {
			add_submenu_page('settings.php', 'Metapic', 'Metapic', 'manage_network', 'metapic', function () {

				if ($_POST) {
					$_GET['updated'] = true;
					update_site_option("mtpc_api_key", $_POST["api_key"]);
					update_site_option("mtpc_secret_key", $_POST["secret_key"]);
					//echo json_encode($_POST);
					if (isset($_POST["API_url"])) {
						$apiUrl = $_POST["API_url"];
					}
					else {
						$apiUrl = $this->api_url;
					}
					update_site_option("mtpc_api_url", $apiUrl);
					$this->client = new ApiClient($apiUrl, $_POST["api_key"], $_POST["secret_key"]);
					$isValid = $this->client->checkClient($_POST["api_key"]);
					update_site_option("mtpc_valid_client", ($isValid["status"] == 200));
					if ($isValid["status"] == 200) {
						$this->setStatusMessage(__("Account activated. You can now activate individual blogs in the network.", "metapic"));
					}
					else {
						$this->setStatusMessage(__("Account not found. Please check your credentials. If the problem persists please contact support.", "metapic"), "error");
					}
				}
				$this->getTemplate("metapic-site-options", array("debugMode" => $this->debugMode));
			}
			);
		}
		);
	}

	private function getApiUrl() {
		$url = false;
		if ($this->debugMode)
			$url = (is_multisite()) ? get_site_option("mtpc_api_url") : @get_option('metapic_options')["uri_string"];
		return ($url) ? $url : $this->api_url;
	}

	private function getTemplate($templateName, array $templateVars = []) {
		global $posts, $post, $wp_did_header, $wp_query, $wp_rewrite, $wpdb, $wp_version, $wp, $id, $comment, $user_ID;

		if (is_array($wp_query->query_vars))
			extract($wp_query->query_vars, EXTR_SKIP);
		extract($this->templateVars);
		extract($templateVars);
		require($this->plugin_dir . "/templates/{$templateName}.php");
	}

	public function __get($var) {
		return $this->templateVars[$var];
	}

	public function __set($var, $value) {
		$this->templateVars[$var] = $value;
	}

	private function setupIframeRoutes() {
		add_action('init', function () {
			add_rewrite_rule('hello.php$', 'index.php?'.$this->accessKey, 'top');
		}
		);

		add_filter('query_vars', function ($query_vars) {
			$query_vars[] = $this->accessKey;
			return $query_vars;
		}
		);

		add_action('parse_request', function ($wp) {
			if (array_key_exists($this->accessKey, $wp->query_vars)) {

				wp_send_json([
					"access_token" => ["access_token" => get_option("mtpc_access_token")],
					"metapicApi" => $this->client->getBaseUrl()
				]
				);
			}
			return;
		}
		);
	}

	private function setupDashboardWidget() {
		if (is_multisite()) {
			$this->updateClicksForMultiSite();
		}
		else {
		}
		add_action( 'wp_dashboard_setup', function() {
			wp_add_dashboard_widget(
				'metapic-dashboard-widget',         // Widget slug.
				__("Total clicks per day", 'metapic'),         // Title.
				function() {
					$this->getTemplate('widgets/dashboard', ["clicks" => get_option("mtpc_clicks_by_date")]);
				}
			);
		} );
	}

	private function updateClicksForMultiSite() {
		global $wpdb;
		$lastUpdate = get_site_option("mtpc_last_click_update");
		if (($lastUpdate && Carbon::parse($lastUpdate)->diffInMinutes(Carbon::now()) > 0) || !$lastUpdate) {
			$allClicks = $this->client->getClientClicksByDate(null, ["from" => date('Y-m-d', strtotime('-10 days')), "to" => date("Y-m-d")]);
			$wpClicks = [];
			foreach ($allClicks as $click) {
				if (isset($wpClicks[$click["email"]]))
					$wpClicks[$click["email"]][] = $click;
				else
					$wpClicks[$click["email"]] = [$click];
			}
			$sites = wp_get_sites();
			$orgBlog = get_current_blog_id();
			foreach ($sites as $site) {
				switch_to_blog($site["blog_id"]);
				$mtpcEmail = get_option("mtpc_email");
				if ($mtpcEmail && isset($wpClicks[$mtpcEmail])) {
					update_option("mtpc_clicks_by_date", $wpClicks[$mtpcEmail]);
				}
			}
			switch_to_blog($orgBlog);
			update_site_option("mtpc_last_click_update", Carbon::now()->toDateTimeString());
		}
	}
}