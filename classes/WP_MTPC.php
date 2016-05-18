<?php

namespace MetaPic;

use \Carbon\Carbon;
use \Exception;

class WP_MTPC {

	private $api_url     = "http://api.metapic.se";
	private $userapi_url = "http://mtpc.se";
	private $cdn_url     = "http://api.metapic.se";
	private $plugin_dir;
	private $plugin_url;

	/**
	 * @var ApiClient $client
	 */
	private $client;

	private $templateVars  = [ ];
	private $debugMode     = false;
	private $accessKey     = "metapic_access_token";
	private $tokenUrl      = "";
	private $autoRegister  = false;
	private $activeAccount = false;

	public function __construct( $plugin_dir, $plugin_url ) {

		$this->debugMode  = ( defined( "MTPC_DEBUG" ) && MTPC_DEBUG === true );
		$this->plugin_dir = $plugin_dir;
		$this->plugin_url = $plugin_url;
		$this->client     = new ApiClient( $this->getApiUrl(), get_site_option( "mtpc_api_key" ), get_site_option( "mtpc_secret_key" ) );

		$this->tokenUrl = rtrim( get_bloginfo( "url" ), "/" ) . "/?" . $this->accessKey;
		$this->setupOptionsPage();
		$this->setupLang();
		$this->setupNetworkOptions();
		$this->setupIframeRoutes();
		$this->setupNetworkDashboardWidget();

		if ( is_multisite() ) {
			$this->autoRegister = (bool) get_site_option( "mtpc_registration_auto" );
		}

		$this->activeAccount = $this->hasActiveAccount();

		if ( $this->activeAccount || $this->autoRegister ) {
			$this->setupJsOptions();
			$this->setupHelpButton();
			$this->setupDashboardWidget();
			$this->setupDeeplinkPublishing();
		}

		add_filter( 'wp_kses_allowed_html', function ( $tags, $context ) {
			foreach ( $tags as $key => $value ) {
				$tags[ $key ]["data-metapic-id"]   = 1;
				$tags[ $key ]["data-metapic-tags"] = 1;
			}
			return $tags;
		}, 500, 2 );
	}

	public function hasActiveAccount() {
		return ( get_option( "mtpc_active_account" ) && get_option( "mtpc_access_token" ) );
	}

	public function activate() {
		if ( is_multisite() ) {
			add_site_option( 'mtpc_deeplink_auto_default', true );
			add_site_option( 'mtpc_registration_auto', false );
		} else {
			add_option( 'mtpc_deeplink_auto_default', true );
		}
	}

	private function getTokenUrl() {
		return rtrim( get_bloginfo( "url" ), "/" ) . "/?" . $this->accessKey;
	}

	private function setupJsOptions() {
		add_filter( 'tiny_mce_before_init', function ( $mceInit, $editor_id ) {
			$mceInit["mtpc_iframe_url"] = $this->getTokenUrl();
			$mceInit["mtpc_plugin_url"] = $this->plugin_url;
			return $mceInit;
		}, 500, 2 );

		add_action( 'admin_head', function () {
			$mce_plugin_name = "metapic";
			// check if WYSIWYG is enabled
			if ( 'true' == get_user_option( 'rich_editing' ) ) {

				wp_enqueue_script( 'iframeScript', $this->getApiUrl() . '/javascript/iframeScript.js', array(), '1.0.0', true );
				wp_enqueue_script( 'metapicAdmin', $this->plugin_url . '/js/metapic-admin.js', [ 'jquery' ], '1.0.0', true );
				// Declare script for new button
				add_filter( 'mce_external_plugins', function ( $plugin_array ) use ( $mce_plugin_name ) {
					$plugin_array[ $mce_plugin_name ] = $this->plugin_url . '/js/metapic.js';
					return $plugin_array;
				} );

				// Register new button in the editor
				add_filter( 'mce_buttons', function ( $buttons ) use ( $mce_plugin_name ) {
					array_push( $buttons, $mce_plugin_name . "link" );
					array_push( $buttons, $mce_plugin_name . "img" );
					array_push( $buttons, $mce_plugin_name . "collage" );

					return $buttons;
				} );
			}
		} );

		add_action( 'admin_enqueue_scripts', function () {
			wp_enqueue_style( 'metapic_admin_css', $this->plugin_url . '/css/metapic.css' );
		} );

		add_filter( 'mce_css', function ( $styles ) {
			$styles .= ',' . $this->plugin_url . '/css/metapic.css';
			return $styles;
		} );

		add_action( "wp_enqueue_scripts", function () {
			if ( defined( 'MTPC_DEBUG' ) && MTPC_DEBUG ) {
				wp_enqueue_script( 'mtpc_frontend_js', get_option( 'metapic_options' )["cdn_uri_string"] . '/metapic.preLoginNoLogin.min.js', [ 'jquery' ], false, true );
				wp_enqueue_style( 'mtpc_frontend_css', get_option( 'metapic_options' )["cdn_uri_string"] . '/metapic.preLogin.css' );
			} else {
				wp_enqueue_script( 'mtpc_frontend_js', '//s3-eu-west-1.amazonaws.com/metapic-cdn/dev/metapic.preLoginNoLogin.min.js', [ 'jquery' ], false, true );
				wp_enqueue_style( 'mtpc_frontend_css', '//s3-eu-west-1.amazonaws.com/metapic-cdn/site/css/remote/metapic.min.css' );
			}
		}, 10 );

		add_filter( 'script_loader_tag', function ( $tag, $handle, $src ) {
			if ( $handle == 'mtpc_frontend_js' ) {
				return str_replace(
					'<script ',
					'<script id="metapic-load" data-metapic-user-id="' . esc_attr( get_option( "mtpc_id" ) ) . '" ',
					$tag );
			}
			return $tag;
		}, 100, 3 );
	}

	/**
	 * Sets a status message for the current option page
	 *
	 * @param string $message - The message to send
	 * @param string $class   - Class for the element. Valid options are updated, error or update-nag
	 */
	private function setStatusMessage( $message, $class = "updated" ) {
		add_settings_error( 'general', 'settings_updated', $message, $class );
	}

	private function setupHelpButton() {
		add_action( 'media_buttons', function () {
			$this->getTemplate( "help-button" );
		} );
	}

	private function setupOptionsPage() {
		add_action( 'admin_init', function () {
			register_setting( 'metapic_options', 'metapic_options', function ( $input ) {
				$options       = get_option( 'metapic_options' );
				$updateActions = array_flip( [ "submit", "login" ] );
				$inArray       = ( count( array_intersect_key( $_POST, $updateActions ) ) > 0 );
				$action        = ( $inArray ) ? "update" : "deactivate";
				switch ( $action ) {
					case "deactivate":
						$this->deactivateAccount();
						$this->setStatusMessage( __( "Account deactivated", "metapic" ) );
						break;
					default:
						$options = $this->updateOptions( $options, $input );
						break;
				}
				return $options;
			} );

			register_setting( 'metapic_register_options', 'metapic_register_options', function ( $input ) {
				$user = $this->client->register( $input["email_string"], $input["password_string"] );
				if ( $user ) {
					$this->activateAccount( $user["id"], $user["email"], $user["access_token"]["access_token"] );
					$this->setStatusMessage( __( "Account created", "metapic" ) );
					wp_safe_redirect( admin_url( 'options-general.php?page=metapic_settings' ) );
					exit;
				} else {
					$this->setStatusMessage( __( "Account already exists", "metapic" ), "error" );
				}
				return $input;
			} );
		} );

		add_action( 'admin_menu', function () {
			$isValidClient = get_site_option( "mtpc_valid_client" );
			if ( ! is_multisite() || ( is_multisite() && $isValidClient ) ) {
				add_options_page( 'Metapic', 'Metapic', 'manage_options', 'metapic_settings', function () {
					if ( is_multisite() ) {
						$this->getTemplate( "metapic-options-ms" );
					} else {
						$this->getTemplate( "metapic-options" );
					}
				}
				);
				if ( ! is_multisite() ) {
					add_submenu_page( null, __( 'Register', 'metapic' ), "Register", "manage_options", "metapic_register", function () {
						$this->getTemplate( "register" );
					} );
				}
			}
		} );
	}

	private function updateOptions( $options, $input ) {

		$options['uri_string']          = untrailingslashit( esc_url( $input['uri_string'] ) );
		$options['cdn_uri_string']      = untrailingslashit( esc_url( $input['cdn_uri_string'] ) );
		$options['user_api_uri_string'] = untrailingslashit( esc_url( $input['user_api_uri_string'] ) );

		$options['mtpc_deeplink_auto_default'] = (bool) $input['mtpc_deeplink_auto_default'];
		update_option( 'mtpc_deeplink_auto_default', $options['mtpc_deeplink_auto_default'] );

		if ( ! get_option( "mtpc_active_account" ) && ! get_option( "mtpc_access_token" ) ) {

			if ( ! is_multisite() ) {
				$options['email_string'] = sanitize_email( $input['email_string'] );
				$password                = sanitize_text_field( trim( $input['password_string'] ) );
				try {
					$user = $this->client->login( $options['email_string'], $password );
					if ( $user ) {
						$this->activateAccount( $user["id"], $options['email_string'], $user["access_token"]["access_token"] );
						$this->setStatusMessage( __( "Login successful", "metapic" ) );
					} else {
						throw new Exception;
					}
				} catch ( Exception $e ) {
					$this->deactivateAccount();
					$this->setStatusMessage( __( "Invalid username or password", "metapic" ), "error" );
				}
			} else {
				$user_email = filter_input( INPUT_POST, 'mtpc_email', FILTER_SANITIZE_EMAIL ) ?: wp_get_current_user()->user_email;
				$wp_user    = get_user_by( "email", $user_email );
				if ( $wp_user ) {
					$user = $this->client->activateUser( $wp_user->user_email );

					if ( $user["access_token"] == null ) {
						$this->client->createUser( array( "email" => $wp_user->user_email, "username" => $wp_user->user_login ) );
						$user = $this->client->activateUser( $wp_user->user_email );
						$this->setStatusMessage( __( "Account created", "metapic" ) );
					} else {
						$this->setStatusMessage( __( "Account activated", "metapic" ) );
					}
					$this->activateAccount( $user["id"], $wp_user->user_email, $user["access_token"]["access_token"] );
					add_option( 'mtpc_deeplink_auto_default', get_site_option( 'mtpc_deeplink_auto_default' ) );

				} else {
					$this->setStatusMessage( __( "User not found", "metapic" ), "error" );
				}
			}
		}
		if ( isset( $options["password_string"] ) ) {
			unset( $options["password_string"] );
		}
		return $options;
	}

	private function setupLang() {
		add_action( 'plugins_loaded', function () {
			$langPath = basename( $this->plugin_dir ) . '/lang/';
			load_plugin_textdomain( 'metapic', false, $langPath );
		} );

		add_filter( 'mce_external_languages', function ( $locales ) {
			$ds                  = DIRECTORY_SEPARATOR;
			$path                = $this->plugin_dir . $ds . 'tinymce-lang' . $ds . 'metapic-langs.php';
			$locales ['metapic'] = $path;
			return $locales;
		} );
	}

	private function setupNetworkOptions() {
		add_action( 'network_admin_menu', function () {
			add_submenu_page( 'settings.php', 'Metapic', 'Metapic', 'manage_network', 'metapic', function () {

				if ( $_POST ) {
					$_GET['updated'] = true;

					$api_key        = sanitize_text_field( $_POST["api_key"] );
					$api_secret_key = sanitize_text_field( $_POST["secret_key"] );

					update_site_option( "mtpc_api_key", $api_key );
					update_site_option( "mtpc_secret_key", $api_secret_key );
					update_site_option( "mtpc_deeplink_auto_default", (bool) $_POST["mtpc_deeplink_auto_default"] );
					update_site_option( "mtpc_registration_auto", (bool) $_POST["mtpc_registration_auto"] );

					if ( isset( $_POST["API_url"] ) ) {
						$api_url = esc_url( $_POST["API_url"] );
					} else {
						$api_url = $this->api_url;
					}

					update_site_option( "mtpc_api_url", $api_url );

					$this->client = new ApiClient( $this->getApiUrl(), $api_key, $api_secret_key );
					$isValid      = $this->client->checkClient( $api_key );

					update_site_option( "mtpc_valid_client", ( $isValid["status"] == 200 ) );

					if ( $isValid["status"] == 200 ) {
						update_site_option( "mtpc_client_name", $isValid["name"] );
						$this->setStatusMessage( __( "Account activated. You can now activate individual blogs in the network.", "metapic" ) );
					} else {
						$this->setStatusMessage( __( "Account not found. Please check your credentials. If the problem persists please contact support.", "metapic" ), "error" );
					}
				}
				$this->getTemplate( "metapic-site-options", array( "debugMode" => $this->debugMode ) );
			} );
		} );
	}

	private function getApiUrl() {
		$url = false;
		if ( $this->debugMode ) {
			$url = ( is_multisite() ) ? get_site_option( "mtpc_api_url" ) : @get_option( 'metapic_options' )["uri_string"];
		}
		return ( $url ) ? $url : $this->api_url;
	}

	private function getTemplate( $templateName, array $templateVars = [ ] ) {
		global $posts, $post, $wp_did_header, $wp_query, $wp_rewrite, $wpdb, $wp_version, $wp, $id, $comment, $user_ID;

		if ( is_array( $wp_query->query_vars ) ) {
			extract( $wp_query->query_vars, EXTR_SKIP );
		}
		extract( $this->templateVars );
		extract( $templateVars );
		require( $this->plugin_dir . "/templates/{$templateName}.php" );
	}

	public function __get( $var ) {
		return $this->templateVars[ $var ];
	}

	public function __set( $var, $value ) {
		$this->templateVars[ $var ] = $value;
	}

	private function setupIframeRoutes() {
		add_action( 'init', function () {
			add_rewrite_rule( 'hello.php$', 'index.php?' . $this->accessKey, 'top' );
		} );

		add_filter( 'query_vars', function ( $query_vars ) {
			$query_vars[] = $this->accessKey;
			return $query_vars;
		} );

		add_action( 'parse_request', function ( $wp ) {
			if ( array_key_exists( $this->accessKey, $wp->query_vars ) ) {
				$accessToken = get_option( "mtpc_access_token" );
				if ( $this->autoRegister && ! $this->activeAccount ) {
					$user        = $this->registerCurrentUser();
					$accessToken = $user["access_token"]["access_token"];
				}
				wp_send_json( [
					"access_token" => [ "access_token" => $accessToken ],
					"metapicApi"   => $this->client->getBaseUrl(),
				] );
			}
			return;
		} );
	}

	private function registerCurrentUser() {
		$wp_user = wp_get_current_user();
		$user    = $this->client->activateUser( $wp_user->user_email );
		if ( $user["access_token"] == null ) {
			$this->client->createUser( array( "email" => $wp_user->user_email, "username" => $wp_user->user_login ) );
			$user = $this->client->activateUser( $wp_user->user_email );
		}
		$this->activateAccount( $user["id"], $wp_user->user_email, $user["access_token"]["access_token"] );
		return $user;
	}

	private function setupDashboardWidget() {
		$this->updateClicks();
		add_action( 'wp_dashboard_setup', function () {
			wp_add_dashboard_widget(
				'metapic-dashboard-widget',         // Widget slug.
				__( "Metapic", 'metapic' ),         // Title.
				function () {
					$this->getTemplate( 'widgets/dashboard', [
						"clicks" => get_option( "mtpc_clicks_by_date" ),
						"month"  => get_option( "mtpc_clicks_by_month" ),
						"total"  => get_option( "mtpc_clicks_total" ),
					] );
				}
			);
		} );
	}

	public function updateClicksForSingleSite() {

		try {

			$wpClicks = (array) $this->client->getClientClicksByDate( get_option( "mtpc_id" ), [
				"from"              => date( 'Y-m-d', strtotime( '-10 days' ) ),
				"to"                => date( "Y-m-d" ),
				"user_access_token" => get_option( "mtpc_access_token" ),
			] );

			$mtpcEmail = get_option( "mtpc_email" );

			if ( $mtpcEmail && isset( $wpClicks[ $mtpcEmail ] ) ) {
				$clicksToInsert = $this->insertMissingDates( $wpClicks[ $mtpcEmail ]["day"] );
				update_option( "mtpc_clicks_by_date", (int) $clicksToInsert );
				update_option( "mtpc_clicks_by_month", isset( $wpClicks[ $mtpcEmail ]["month"] ) ? (int) $wpClicks[ $mtpcEmail ]["month"] : 0 );
				update_option( "mtpc_clicks_total", isset( $wpClicks[ $mtpcEmail ]["total"] ) ? (int) $wpClicks[ $mtpcEmail ]["total"] : 0 );
			}

			update_option( "mtpc_clicks", $wpClicks );

			return $wpClicks;

		} catch ( Exception $e ) {
			return [ ];
		}

	}

	public function updateClicksForMultiSite( $blog_id ) {

		try {

			$wpClicks = (array) $this->client->getClientClicksByDate( null, [
				"from" => date( 'Y-m-d', strtotime( '-10 days' ) ),
				"to"   => date( "Y-m-d" ),
			] );

			$sites = wp_get_sites();

			foreach ( $sites as $site ) {
				switch_to_blog( $site["blog_id"] );
				$mtpcEmail = get_option( "mtpc_email" );
				if ( $mtpcEmail && isset( $wpClicks[ $mtpcEmail ] ) ) {
					$clicksToInsert = $this->insertMissingDates( $wpClicks[ $mtpcEmail ]["day"] );
					update_option( "mtpc_clicks_by_date", (int) $clicksToInsert );
					update_option( "mtpc_clicks_by_month", isset( $wpClicks[ $mtpcEmail ]["month"] ) ? (int) $wpClicks[ $mtpcEmail ]["month"] : 0 );
					update_option( "mtpc_clicks_total", isset( $wpClicks[ $mtpcEmail ]["total"] ) ? (int) $wpClicks[ $mtpcEmail ]["total"] : 0 );
				}
			}

			switch_to_blog( $blog_id );
			update_site_option( "mtpc_clicks", $wpClicks );

			return $wpClicks;

		} catch ( Exception $e ) {
			return [ ];
		}

	}

	private function setupNetworkDashboardWidget() {
		$this->updateClicks();
		add_action( 'wp_network_dashboard_setup', function () {
			wp_add_dashboard_widget(
				'metapic-network-dashboard-widget',         // Widget slug.
				__( "Metapic", 'metapic' ),         // Title.
				function () {
					$this->getTemplate( 'widgets/dashboard-network', [ "clicks" => get_site_option( "mtpc_clicks_by_date" ) ] );
				}
			);
		} );
	}

	private function updateClicks() {
		if ( is_multisite() ) {
			tlc_transient( 'mtpc_client_clicks' )
				->updates_with( array( $this, 'updateClicksForMultiSite' ), [ get_current_blog_id() ] )
				->background_only()
				->expires_in( 600 )
				->get();
		} else {
			tlc_transient( 'mtpc_clicks' )
				->updates_with( array( $this, 'updateClicksForSingleSite' ) )
				->background_only()
				->expires_in( 600 )
				->get();
		}
	}

	private function insertMissingDates( $clicks ) {
		$today      = Carbon::parse( date( "Y-m-d" ) );
		$tenDaysAgo = Carbon::parse( date( "Y-m-d" ) )->subDays( 9 );
		if ( ! is_array( $clicks ) ) {
			$clicks = [ [ "date"        => $today->format( "Y-m-d" ), "tag_clicks" => 0,
			              "link_clicks" => 0 ], [ "date"        => $tenDaysAgo->format( "Y-m-d" ), "tag_clicks" => 0,
			                                      "link_clicks" => 0 ] ];
		}
		$firstClick = $clicks[0];
		$lastClick  = end( $clicks );
		reset( $clicks );
		$firstClickDate = Carbon::parse( $firstClick["date"] );
		$lastClickDate  = Carbon::parse( $lastClick["date"] );

		$fillIn    = [ ];
		$fillStart = Carbon::parse( $firstClick["date"] );

		foreach ( $clicks as $click ) {
			while ( $fillStart->diffInDays( Carbon::parse( $click["date"] ), false ) < 0 ) {
				$fillIn[]  = [
					"date"        => $fillStart->format( "Y-m-d" ),
					"tag_clicks"  => 0,
					"link_clicks" => 0,
				];
				$fillStart = $fillStart->subDay();
			}
			$fillIn[]  = $click;
			$fillStart = Carbon::parse( $click["date"] )->subDay();
		}
		$clicks = $fillIn;

		$tempClicks = [ ];
		while ( $today->diffInDays( $firstClickDate, false ) < 0 ) {
			$tempClicks[] = array_merge( $firstClick, [
				"date"        => $today->format( "Y-m-d" ),
				"tag_clicks"  => 0,
				"link_clicks" => 0,
			] );
			$today        = $today->subDay();
		}
		$clicks = array_merge( $tempClicks, $clicks );

		$tempClicks = [ ];
		while ( $tenDaysAgo->diffInDays( $lastClickDate, false ) > 0 ) {
			$tempClicks[] = array_merge( $firstClick, [
				"date"        => $tenDaysAgo->format( "Y-m-d" ),
				"tag_clicks"  => 0,
				"link_clicks" => 0,
			] );
			$tenDaysAgo   = $tenDaysAgo->addDay();
		}
		$clicks = array_merge( $clicks, array_reverse( $tempClicks ) );

		return $clicks;
	}

	private function activateAccount( $id, $email, $token ) {
		update_option( "mtpc_active_account", true );
		update_option( "mtpc_id", sanitize_text_field( $id ) );
		update_option( "mtpc_email", sanitize_email( $email ) );
		update_option( "mtpc_access_token", sanitize_text_field( $token ) );

		if ( is_multisite() ) {
			update_option( 'mtpc_deeplink_auto_default', (bool) get_site_option( 'mtpc_deeplink_auto_default' ) );
		} else {
			add_option( 'mtpc_deeplink_auto_default', true );
		}
	}


	private function deactivateAccount() {
		delete_option( "mtpc_active_account" );
		delete_option( "mtpc_access_token" );
		delete_option( "mtpc_email" );
		delete_option( "mtpc_id" );
		delete_option( 'mtpc_deeplink_auto_default' );
	}

	private function setupDeeplinkPublishing() {
		add_action( 'post_submitbox_misc_actions', function () {
			$this->getTemplate( 'deeplink-publish' );
		} );

		add_action( 'save_post', function ( $post_id ) {
			if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
				return;
			}

			if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
				return;
			}

			if ( isset( $_POST['mtpc_deeplink_auto'] ) ) {
				update_post_meta( $post_id, "mtpc_deeplink_auto", (int) $_POST["mtpc_deeplink_auto"] );
			}
		} );

		add_filter( 'wp_insert_post_data', function ( $filtered_data, $raw_data ) {
			$deepLinkContent = isset( $raw_data['mtpc_deeplink_auto'] ) ? (bool) $raw_data["mtpc_deeplink_auto"] : false;
			if ( $deepLinkContent ) {

				if ( ! $this->hasActiveAccount() && $this->autoRegister ) {
					$this->registerCurrentUser();
				}

				$userId      = get_option( "mtpc_id" );
				$accessToken = ( is_multisite() ) ? null : get_option( "mtpc_access_token" );
				$newContent  = $this->client->deepLinkBlogPost( $userId, $filtered_data['post_content'], $accessToken );

				if ( is_array( $newContent ) && isset( $newContent["newHtml"] ) && $newContent["isUpdated"] ) {
					$filtered_data['post_content'] = $newContent["newHtml"];
				}
			}
			return $filtered_data;
		}, 10, 2 );
	}

	private function optionExists( $optionName ) {
		return ( get_option( $optionName ) === false );
	}

	private function siteOptionExists( $optionName ) {
		return ( get_site_option( $optionName ) === false );
	}

	private function isEditPage( $new_edit = null ) {
		global $pagenow;

		if ( ! is_admin() ) {
			return false;
		}

		if ( $new_edit == "edit" ) {
			return in_array( $pagenow, array( 'post.php', ) );
		} elseif ( $new_edit == "new" ) {
			return in_array( $pagenow, array( 'post-new.php' ) );
		} else {
			return in_array( $pagenow, array( 'post.php', 'post-new.php' ) );
		}
	}
}