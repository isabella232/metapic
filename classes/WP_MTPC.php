<?php
use Carbon\Carbon;
use MetaPic\ApiClient;

class WP_MTPC extends stdClass {
	private $api_url = "http://api.metapic.se";
    private $userapi_url = "http://mtpc.se";
    private $cdn_url = "http://api.metapic.se";
	private $plugin_dir;
	private $plugin_url;
	/* @var ApiClient $client */
	private $client;
	private $templateVars = [];
	private $debugMode = false;
	private $accessKey = "metapic_access_token";
	private $tokenUrl = "";
	private $autoRegister = false;
	private $activeAccount = false;

	public function __construct($plugin_dir, $plugin_url) {
		$options = get_option('metapic_options');
		$this->debugMode = (defined("MTPC_DEBUG") && MTPC_DEBUG === true);
		$this->plugin_dir = $plugin_dir;
		$this->plugin_url = $plugin_url;
		$this->client = new ApiClient($this->getApiUrl(), get_site_option("mtpc_api_key"), get_site_option("mtpc_secret_key"));

		$this->tokenUrl = rtrim(get_bloginfo("url"), "/") . "/?" . $this->accessKey;
		$this->setupOptionsPage();
		$this->setupLang();
		$this->setupNetworkOptions();
		$this->setupIframeRoutes();
		$this->setupNetworkDashboardWidget();

		if (is_multisite()) {
			$this->autoRegister = (bool)get_site_option("mtpc_registration_auto");
		}
		$this->activeAccount = $this->hasActiveAccount();

		if ($this->activeAccount || $this->autoRegister) {
			$this->setupJsOptions();
			$this->setupHelpButton();
			$this->setupDashboardWidget();
			$this->setupDeeplinkPublishing();
		}

		add_filter('wp_kses_allowed_html', function ($tags, $context) {
			foreach ($tags as $key => $value) {
				$tags[$key]["data-metapic-id"] = 1;
				$tags[$key]["data-metapic-tags"] = 1;
			}
			return $tags;
		}, 500, 2);
	}

	public function hasActiveAccount() {
		return (get_option("mtpc_active_account") && get_option("mtpc_access_token"));
	}

	public function activate() {
		if (is_multisite()) {
			add_site_option('mtpc_deeplink_auto_default', true);
			add_site_option('mtpc_registration_auto', false);
		}
		else {
			add_option('mtpc_deeplink_auto_default', true);
		}
	}

	private function setupJsOptions() {
		add_filter('tiny_mce_before_init', function ($mceInit, $editor_id) {
			$mceInit["mtpc_iframe_url"] = $this->tokenUrl;
			$mceInit["mtpc_plugin_url"] = $this->plugin_url;
			return $mceInit;
		}, 500, 2);

		add_action('admin_head', function () {
			$mce_plugin_name = "metapic";
			$options = get_option('metapic_options');
			// check if WYSIWYG is enabled
			if ('true' == get_user_option('rich_editing')) {

				//wp_enqueue_script( 'iframeScript',  , array());
				//$options['uri_string']="http://metapic-api.localhost";
				wp_enqueue_script('iframeScript', $this->getApiUrl() . '/javascript/iframeScript.js', array(), '1.0.0', true);
				wp_enqueue_script('metapicAdmin', $this->plugin_url . '/js/metapic-admin.js', ['jquery'], '1.0.0', true);
				// Declare script for new button
				add_filter('mce_external_plugins', function ($plugin_array) use ($mce_plugin_name) {
					$plugin_array[$mce_plugin_name] = $this->plugin_url . '/js/metapic.js';
					return $plugin_array;
				});

				// Register new button in the editor
				add_filter('mce_buttons', function ($buttons) use ($mce_plugin_name) {
					array_push($buttons, $mce_plugin_name . "link");
					array_push($buttons, $mce_plugin_name . "img");
					array_push($buttons, $mce_plugin_name . "collage");

					return $buttons;
				});
			}
		});

		add_action('admin_enqueue_scripts', function ($styles) {
			wp_enqueue_style('metapic_admin_css', $this->plugin_url . '/css/metapic.css');
		});

		add_filter('mce_css', function ($styles) {
			$styles .= ',' . $this->plugin_url . '/css/metapic.css';
			return $styles;
		});

		$jsHandle = 'mtpc_frontend_js';
		add_action("wp_head", function () use ($jsHandle) {
            if(MTPC_DEBUG){
                wp_enqueue_script($jsHandle,get_option('metapic_options')["cdn_uri_string"] .'/metapic.preLoginNoLogin.min.js', ['jquery'], false, true);
                wp_enqueue_style('mtpc_frontend_css', get_option('metapic_options')["cdn_uri_string"].'/metapic.preLogin.css');

            }else {
                wp_enqueue_script($jsHandle, '//s3-eu-west-1.amazonaws.com/metapic-cdn/dev/metapic.preLoginNoLogin.min.js', ['jquery'], false, true);
                wp_enqueue_style('mtpc_frontend_css', '//s3-eu-west-1.amazonaws.com/metapic-cdn/site/css/remote/metapic.min.css');
            }
        }, 10);

		add_filter('script_loader_tag', function ($tag, $handle, $src) use ($jsHandle) {
			if ($handle == $jsHandle) {
				return str_replace(
					'<script ',
					'<script id="metapic-load" data-metapic-user-id="'.get_option("mtpc_id").'" ',
					$tag );
			}
			return $tag;
		}, 100, 3);
	}

	/**
	 * Sets a status message for the current option page
	 * @param string $message - The message to send
	 * @param string $class - Class for the element. Valid options are updated, error or update-nag
	 */
	private function setStatusMessage($message, $class = "updated") {
		add_settings_error('general', 'settings_updated', $message, $class);
	}

	private function setupHelpButton() {
		add_action('media_buttons', function () {
			$this->getTemplate("help-button");
		});
	}

	private function setupOptionsPage() {
		add_action('admin_init', function () {
			$options = get_option('metapic_options');
			register_setting('metapic_options', 'metapic_options', function ($input) {
				$options = get_option('metapic_options');
				$updateActions = array_flip(["submit", "login"]);
				$inArray = (count(array_intersect_key($_POST, $updateActions)) > 0);
				$action = ($inArray) ? "update" : "deactivate";
				switch ($action) {
					case "deactivate":
						$this->deactivateAccount();
						$this->setStatusMessage(__("Account deactivated", "metapic"));
						break;
					default:
						$options = $this->updateOptions($options, $input);
						break;
				}
				return $options;
			});

			register_setting('metapic_register_options', 'metapic_register_options', function ($input) {
				$options = get_option('metapic_register_options');
				$user = $this->client->register($input["email_string"], $input["password_string"]);
				if ($user) {
					$this->activateAccount($user["id"], $user["email"], $user["access_token"]["access_token"]);
					$this->setStatusMessage(__("Account created", "metapic"));
					wp_redirect(admin_url('options-general.php?page=metapic_settings'));
					die();
				}
				else {
					$this->setStatusMessage(__("Account already exists", "metapic"), "error");

				}
				return $options;
			});
		});

		add_action('admin_init', function () {
			register_setting('metapic_register_options', 'metapic_register_options', function ($input) {
				return $input;
			});
		});

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
				if (!is_multisite())
					add_submenu_page(null, __('Register', 'metapic'), "Register", "manage_options", "metapic_register", function () {
						$this->getTemplate("register");
					});
			}
		}
		);
	}

	private function updateOptions($options, $input) {
		$options['uri_string'] = trim($input['uri_string'], "/");
        $options['cdn_uri_string'] = trim($input['cdn_uri_string'], "/");
        $options['user_api_uri_string'] = trim($input['user_api_uri_string'], "/");


		$options['mtpc_deeplink_auto_default'] = (bool)$input['mtpc_deeplink_auto_default'];
		update_option('mtpc_deeplink_auto_default', $options['mtpc_deeplink_auto_default']);

		if (!get_option("mtpc_active_account") && !get_option("mtpc_access_token")) {

			if (!is_multisite()) {
				$options['email_string'] = trim($input['email_string']);
				$password = trim($input['password_string']);
				try {
					$user = $this->client->login($options['email_string'], $password);
					if ($user) {
						$this->activateAccount($user["id"], $options['email_string'], $user["access_token"]["access_token"]);
						$this->setStatusMessage(__("Login successful", "metapic"));
					}
					else {
						throw new Exception;
					}
				} catch (Exception $e) {
					$this->deactivateAccount();
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
					$this->activateAccount($user["id"], $wp_user->user_email, $user["access_token"]["access_token"]);
					add_option('mtpc_deeplink_auto_default', get_site_option('mtpc_deeplink_auto_default'));

				}
				else {
					$this->setStatusMessage(__("User not found", "metapic"), "error");
				}
			}
		}
		if (isset($options["password_string"]))
			unset($options["password_string"]);
		return $options;
	}

	private function setupLang() {
		add_action('plugins_loaded', function () {
			$langPath = basename($this->plugin_dir) . '/lang/';
			load_plugin_textdomain('metapic', false, $langPath);
		});

		add_filter('mce_external_languages', function ($locales) {
			$ds = DIRECTORY_SEPARATOR;
			$path = $this->plugin_dir . $ds . 'tinymce-lang' . $ds . 'metapic-langs.php';
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
					update_site_option("mtpc_deeplink_auto_default", (bool)$_POST["mtpc_deeplink_auto_default"]);
					update_site_option("mtpc_registration_auto", (bool)$_POST["mtpc_registration_auto"]);
					//echo json_encode($_POST);
					if (isset($_POST["API_url"])) {
						$apiUrl = $_POST["API_url"];
					}
					else {
						$apiUrl = $this->api_url;
					}
					update_site_option("mtpc_api_url", $apiUrl);
					$this->client = new ApiClient($this->getApiUrl(), $_POST["api_key"], $_POST["secret_key"]);
					$isValid = $this->client->checkClient($_POST["api_key"]);
					update_site_option("mtpc_valid_client", ($isValid["status"] == 200));

					if ($isValid["status"] == 200) {
						update_site_option("mtpc_client_name", $isValid["name"]);
						$this->setStatusMessage(__("Account activated. You can now activate individual blogs in the network.", "metapic"));
					}
					else {
						$this->setStatusMessage(__("Account not found. Please check your credentials. If the problem persists please contact support.", "metapic"), "error");
					}
				}
				$this->getTemplate("metapic-site-options", array("debugMode" => $this->debugMode));
			});
		});
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
			add_rewrite_rule('hello.php$', 'index.php?' . $this->accessKey, 'top');
		}
		);

		add_filter('query_vars', function ($query_vars) {
			$query_vars[] = $this->accessKey;
			return $query_vars;
		}
		);

		add_action('parse_request', function ($wp) {
			if (array_key_exists($this->accessKey, $wp->query_vars)) {
				$accessToken = get_option("mtpc_access_token");
				if ($this->autoRegister && !$this->activeAccount) {
					$user = $this->registerCurrentUser();
					$accessToken = $user["access_token"]["access_token"];
				}
				wp_send_json([
					"access_token" => ["access_token" => $accessToken],
					"metapicApi" => $this->client->getBaseUrl()
				]);
			}
			return;
		});
	}

	private function registerCurrentUser() {
		$wp_user = wp_get_current_user();
		$user = $this->client->activateUser($wp_user->user_email);
		if ($user["access_token"] == null) {
			$this->client->createUser(array("email" => $wp_user->user_email, "username" => $wp_user->user_login));
			$user = $this->client->activateUser($wp_user->user_email);
		}
		$this->activateAccount($user["id"], $wp_user->user_email, $user["access_token"]["access_token"]);
		return $user;
	}

	private function setupDashboardWidget() {
		$this->updateClicks();
		add_action('wp_dashboard_setup', function () {
			wp_add_dashboard_widget(
				'metapic-dashboard-widget',         // Widget slug.
				__("Metapic", 'metapic'),         // Title.
				function () {
					$this->getTemplate('widgets/dashboard', [
						"clicks" => get_option("mtpc_clicks_by_date"),
						"month" => get_option("mtpc_clicks_by_month"),
						"total" => get_option("mtpc_clicks_total")
					]);
				}
			);
		});
	}

	private function updateClicksForSingleSite() {
		$lastUpdate = get_option("mtpc_last_click_update");
		if (($lastUpdate && Carbon::parse($lastUpdate)->diffInMinutes(Carbon::now()) >= 10) || !$lastUpdate) {
			update_site_option("mtpc_last_click_update", Carbon::now()->toDateTimeString());
			try {
				$wpClicks = $this->client->getClientClicksByDate(get_option("mtpc_id"), ["from" => date('Y-m-d', strtotime('-10 days')), "to" => date("Y-m-d"), "user_access_token" => get_option("mtpc_access_token")]);

				$mtpcEmail = get_option("mtpc_email");
				if ($mtpcEmail && isset($wpClicks[$mtpcEmail])) {
					$clicksToInsert = $this->insertMissingDates($wpClicks[$mtpcEmail]["day"]);
					update_option("mtpc_clicks_by_date", $clicksToInsert);
					update_option("mtpc_clicks_by_month", isset($wpClicks[$mtpcEmail]["month"]) ? $wpClicks[$mtpcEmail]["month"] : 0);
					update_option("mtpc_clicks_total", isset($wpClicks[$mtpcEmail]["total"]) ? $wpClicks[$mtpcEmail]["total"] : 0);
				}
				update_option("mtpc_clicks", $wpClicks);
			} catch (Exception $e) {
			}
		}
	}

	private function updateClicksForMultiSite() {
		$lastUpdate = get_site_option("mtpc_last_click_update");
		if (($lastUpdate && Carbon::parse($lastUpdate)->diffInMinutes(Carbon::now()) >= 10) || !$lastUpdate) {
			update_site_option("mtpc_last_click_update", Carbon::now()->toDateTimeString());
			try {
				$wpClicks = $this->client->getClientClicksByDate(null, ["from" => date('Y-m-d', strtotime('-10 days')), "to" => date("Y-m-d")]);
				$sites = wp_get_sites();
				$orgBlog = get_current_blog_id();
				foreach ($sites as $site) {
					switch_to_blog($site["blog_id"]);
					$mtpcEmail = get_option("mtpc_email");
					if ($mtpcEmail && isset($wpClicks[$mtpcEmail])) {
						$clicksToInsert = $this->insertMissingDates($wpClicks[$mtpcEmail]["day"]);
						update_option("mtpc_clicks_by_date", $clicksToInsert);
						update_option("mtpc_clicks_by_month", isset($wpClicks[$mtpcEmail]["month"]) ? $wpClicks[$mtpcEmail]["month"] : 0);
						update_option("mtpc_clicks_total", isset($wpClicks[$mtpcEmail]["total"]) ? $wpClicks[$mtpcEmail]["total"] : 0);
					}
				}
				switch_to_blog($orgBlog);
				update_site_option("mtpc_clicks", $wpClicks);
			} catch (Exception $e) {
			}
		}
	}

	private function setupNetworkDashboardWidget() {
		$this->updateClicks();
		add_action('wp_network_dashboard_setup', function () {
			wp_add_dashboard_widget(
				'metapic-network-dashboard-widget',         // Widget slug.
				__("Metapic", 'metapic'),         // Title.
				function () {
					$this->getTemplate('widgets/dashboard-network', ["clicks" => get_site_option("mtpc_clicks_by_date")]);
				}
			);
		});
	}

	private function updateClicks() {
		if (is_multisite()) {
			$this->updateClicksForMultiSite();
		}
		else {
			$this->updateClicksForSingleSite();
		}
	}

	private function insertMissingDates($clicks) {
		$today = Carbon::parse(date("Y-m-d"));
		$tenDaysAgo = Carbon::parse(date("Y-m-d"))->subDays(9);
		if (!is_array($clicks)) {
			$clicks = [["date" => $today->format("Y-m-d"), "tag_clicks" => 0,
				"link_clicks" => 0], ["date" => $tenDaysAgo->format("Y-m-d"), "tag_clicks" => 0,
				"link_clicks" => 0]];
		}
		$firstClick = $clicks[0];
		$lastClick = end($clicks);
		reset($clicks);
		$firstClickDate = Carbon::parse($firstClick["date"]);
		$lastClickDate = Carbon::parse($lastClick["date"]);

		$fillIn = [];
		$fillStart = Carbon::parse($firstClick["date"]);

		foreach ($clicks as $click) {
			while ($fillStart->diffInDays(Carbon::parse($click["date"]), false) < 0) {
				$fillIn[] = [
					"date" => $fillStart->format("Y-m-d"),
					"tag_clicks" => 0,
					"link_clicks" => 0
				];
				$fillStart = $fillStart->subDay();
			}
			$fillIn[] = $click;
			$fillStart = Carbon::parse($click["date"])->subDay();
		}
		$clicks = $fillIn;

		$tempClicks = [];
		while ($today->diffInDays($firstClickDate, false) < 0) {
			$tempClicks[] = array_merge($firstClick, [
				"date" => $today->format("Y-m-d"),
				"tag_clicks" => 0,
				"link_clicks" => 0
			]);
			$today = $today->subDay();
		}
		$clicks = array_merge($tempClicks, $clicks);

		$tempClicks = [];
		while ($tenDaysAgo->diffInDays($lastClickDate, false) > 0) {
			$tempClicks[] = array_merge($firstClick, [
				"date" => $tenDaysAgo->format("Y-m-d"),
				"tag_clicks" => 0,
				"link_clicks" => 0
			]);
			$tenDaysAgo = $tenDaysAgo->addDay();
		}
		$clicks = array_merge($clicks, array_reverse($tempClicks));

		return $clicks;
	}

	private function activateAccount($id, $email, $token) {
		update_option("mtpc_active_account", true);
		update_option("mtpc_id", $id);
		update_option("mtpc_email", $email);
		update_option("mtpc_access_token", $token);

		if (is_multisite()) {
			update_option('mtpc_deeplink_auto_default', get_site_option('mtpc_deeplink_auto_default'));
		}
		else {
			add_option('mtpc_deeplink_auto_default', true);
		}
	}


	private function deactivateAccount() {
		delete_option("mtpc_active_account");
		delete_option("mtpc_access_token");
		delete_option("mtpc_email");
		delete_option("mtpc_id");
		delete_option('mtpc_deeplink_auto_default');
	}

	private function setupDeeplinkPublishing() {
		add_action('post_submitbox_misc_actions', function () {
			$this->getTemplate('deeplink-publish');
		});

		add_action('save_post', function ($postId) {
			update_post_meta($postId, "mtpc_deeplink_auto", (int)$_POST["mtpc_deeplink_auto"]);
		});

		add_filter('wp_insert_post_data', function ($filtered_data, $raw_data) {
			$deepLinkContent = (bool)$raw_data["mtpc_deeplink_auto"];
			if ($deepLinkContent) {
				if (!$this->hasActiveAccount() && $this->autoRegister) {
					$this->registerCurrentUser();
				}
				$userId = get_option("mtpc_id");
				$accessToken = (is_multisite()) ? null : get_option("mtpc_access_token");
				$newContent = $this->client->deepLinkBlogPost($userId, $filtered_data['post_content'], $accessToken);

				if (is_array($newContent) && isset($newContent["newHtml"]) && $newContent["isUpdated"]) {
					$filtered_data['post_content'] = $newContent["newHtml"];
				}
			}
			return $filtered_data;
		}, 10, 2);
	}

	private function optionExists($optionName) {
		return (get_option($optionName) === false);
	}

	private function siteOptionExists($optionName) {
		return (get_site_option($optionName) === false);
	}

	private function isEditPage($new_edit = null) {
		global $pagenow;
		//make sure we are on the backend
		if (!is_admin()) return false;


		if ($new_edit == "edit")
			return in_array($pagenow, array('post.php',));
		elseif ($new_edit == "new") //check for new post page
			return in_array($pagenow, array('post-new.php'));
		else //check for either new or edit
			return in_array($pagenow, array('post.php', 'post-new.php'));
	}
}