<?php
$options = get_option('metapic_options');
$url=$options['uri_string']."/generateIframeRandomCode";

$response=wp_remote_post("http://".$url,array('body'=>array("email"=>$options['email_string'],"password"=>$options['password_string'])));

$responseObj=json_decode( wp_remote_retrieve_body($response));


$responseObj->metapicApi =$options['uri_string'];

echo json_encode($responseObj);
