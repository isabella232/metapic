<?php
namespace MetaPic;

class User_Api {
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

	public function is_active() {
		return (bool)$this->client_id;
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
return new User_Api();