<?php
namespace MetaPic;

class User_Api {}
return new User_Api();

/*
return call_user_func(function() {
	$user_api = new MetaPic_Object();
	$user_api->user_name = "Marcus";
	$user_api->get_user_name = function() {
		return $this->user_name;
	};

	return $user_api;
});
*/