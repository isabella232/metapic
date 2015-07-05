<?php
use MetaPic\ApiClient;

class WP_MTPC extends stdClass {
	private $plugin_dir;
	private $plugin_url;
	/* @var ApiClient $client */
	private $client;
	private $templateVars = [];
	private $debugMode = false;

	public function __construct($plugin_dir) {
		$options = get_option('metapic_options');
		$this->debugMode = (defined("MTPC_DEBUG") && MTPC_DEBUG === true);
        $this->debugMode= true; //overwrite line above
		$this->plugin_dir = $plugin_dir;
		$this->plugin_url = plugins_url() . '/' . basename(__DIR__);
        $this->client = new ApiClient( get_site_option("mtpc_api_url"), get_site_option("mtpc_api_key"), get_site_option("mtpc_secret_key"));
       // echo json_encode($this->client->getUsers());
        $this->setupOptionsPage();
		$this->setupLang();
		$this->setupNetworkOptions();
		$this->setupIframeRoutes();
	}

	/**
	 * Sets a status message for the current option page
	 * @param string $message   - The message to send
	 * @param string $class     - Class for the element. Valid options are updated, error or update-nag
	 */
	private function setStatusMessage($message, $class = "updated") {
		add_settings_error('general', 'settings_updated', __($message, "metapic"), $class);
		/*
		$statusMessage = function() use ($message, $class) {
			$message = __($message, "metapic");
			echo "<div class=\"$class notice is-dismissible\"><p><strong>$message</strong></p></div>";
		};
		add_action( 'admin_notices', $statusMessage, 3 );
		add_action( 'network_admin_notices', $statusMessage, 3 );
		*/
	}

	private function setupOptionsPage() {
      	add_action('admin_init', function () {
            $options = get_option('metapic_options');
			register_setting('metapic_options', 'metapic_options', function ($input) {
			    $options = get_option('metapic_options');
				switch ($_POST["mtpc_action"]) {
					case "reactivate":
						$adminEmail = get_bloginfo("admin_email");
						$user = $this->client->activateUser($adminEmail);
						if (isset($user["id"])) {
            				update_option("mtpc_active_account", true);
							update_option("mtpc_access_token", $user["access_token"]["access_token"]);
						}
						break;
					default:
						$options = $this->updateOptions($options, $input);
						break;
				}
				return $options;
			});

			$activeAccount = get_option("mtpc_active_account");
            if (!$activeAccount) {
				add_settings_section('plugin_main', 'Login', function () {
					echo '<p>Please login to your Metapic account</p>';
				}, 'plugin');
				add_settings_field('email_field', 'Email', function () use ($options) {
					echo "<input id='plugin_text_string' name='metapic_options[email_string]' size='40' type='text' value='{$options['email_string']}' />";
				}, 'plugin', 'plugin_main');

				add_settings_field('password_field', 'Password', function () use ($options) {
					echo "<input id='plugin_text_string' type='password' name='metapic_options[password_string]' size='40' type='text' value='{$options['password_string']}' />";
				}, 'plugin', 'plugin_main');
			}
			else {
				add_settings_section('plugin_main', 'Your account', function () {
					echo '<p>You are currently logged in</p>';
				}, 'plugin');
			}

			if ($this->debugMode) {
				add_settings_section('plugin_advanced', 'Advanced', function () {
					echo '<p>Advanced settings</p>';
				}, 'plugin');

				add_settings_field('uri_field', 'Address to the server', function () use ($options) {
					echo "<input id='plugin_text_string' name='metapic_options[uri_string]' size='40' type='text' value='{$options['uri_string']}' />";
				}, 'plugin', 'plugin_advanced');
			}
		});

		add_action('admin_menu', function () {
			$isValidClient = get_site_option("mtpc_valid_client");
			if (!is_multisite() || (is_multisite() && $isValidClient)) {
				add_options_page('Metapic', 'Metapic', 'manage_options', 'metapic_settings', function () {
					if (is_multisite())
						$this->getTemplate("metapic-options-ms");
					else
						$this->getTemplate("metapic-options");
				});
			}
		});
	}

	private function updateOptions($options, $input) {
     	$options['uri_string'] = trim($input['uri_string'], "/");

		if (!get_option("mtpc_active_account")) {

            if (! is_multisite() ) {
                $options['email_string'] = trim($input['email_string']);
                $options['password_string'] = trim($input['password_string']);
                try {
                    $user = $this->client->login($options['email_string'], $options['password_string']);
                    if ($user) {
                        update_option("mtpc_active_account", true);
                        update_option("mtpc_access_token", $user["access_token"]["access_token"]);
                        $this->setStatusMessage("Login successful");
                    } else {
                        throw new Exception;
                    }
                } catch (Exception $e) {
                    delete_option("mtpc_active_account");
                    delete_option("mtpc_access_token");
                    $this->setStatusMessage("Invalid username or password", "error");
                }
            }else{
                $adminEmail = get_bloginfo("admin_email");
                $user = $this->client->activateUser($adminEmail);

                if($user["access_token"]==null){
                    $this->client->createUser(array("email"=>$adminEmail));
                    $user = $this->client->activateUser($adminEmail);
                }
                update_option("mtpc_active_account", true);
                update_option("mtpc_access_token",$user["access_token"]["access_token"] );
                $this->setStatusMessage("Login successful");
            }
		}
		return $options;
	}

	private function setupLang() {
		add_action('plugins_loaded', function() {
			load_plugin_textdomain( 'metapic', false, dirname( plugin_basename(__FILE__) ) . '/lang/' );
		});
	}

	private function setupNetworkOptions() {
		add_action( 'network_admin_menu', function() {
			add_submenu_page('settings.php', 'Metapic', 'Metapic', 'manage_network', 'metapic', function() {
				if ( $_POST ) {
                  	$_GET['updated'] = true;
    				update_site_option("mtpc_api_key", $_POST["api_key"]);
					update_site_option("mtpc_secret_key", $_POST["secret_key"]);
                    //echo json_encode($_POST);
                    if(isset($_POST["API_url"])) {
                        $apiUrl=$_POST["API_url"];
                    }else{
                        $apiUrl="http://metapic-testapi.herokuapp.com";
                    }
                    update_site_option("mtpc_api_url",$apiUrl);
                    $this->client = new ApiClient($apiUrl, $_POST["api_key"], $_POST["secret_key"]);
					$isValid = $this->client->checkClient($_POST["api_key"]);
					update_site_option("mtpc_valid_client", ($isValid["status"] == 200));
				}
				$this->getTemplate("metapic-site-options",array("debugMode"=>$this->debugMode));
			});
		});
	}

	private function getTemplate($templateName, array $templateVars = []) {
		global $posts, $post, $wp_did_header, $wp_query, $wp_rewrite, $wpdb, $wp_version, $wp, $id, $comment, $user_ID;

		if ( is_array( $wp_query->query_vars ) )
			extract( $wp_query->query_vars, EXTR_SKIP );
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
		add_action('init', function() {
			add_rewrite_rule('hello.php$', 'index.php?metapic_randomNummber', 'top');
		});

		add_filter('query_vars', function ($query_vars) {
			$query_vars[] = 'metapic_randomNummber';
			return $query_vars;
		});

		add_action('parse_request', function ($wp) {
			if (array_key_exists('metapic_randomNummber', $wp->query_vars)) {

               wp_send_json([
                    "access_token" => ["access_token" => get_option("mtpc_access_token")],
                    "metapicApi" => $this->client->getBaseUrl()
                ]);
			}
			return;
		});
	}
}