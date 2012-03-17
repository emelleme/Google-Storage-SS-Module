<?php

 /* Oauth Callback Controller */
class OAuthCallback_Controller extends ContentController {
	 //Google Verify Assertion API
    private static $SERVER_URL =
    "";
    
    public function init(){
    	parent::init();
		
		//Check 
		
    }
    
    public function index($arguments)
	{
		//Oauth callback for the Google.
		// https://accounts.google.com/o/oauth2/auth?response_type=code&scope=https://www.googleapis.com/auth/devstorage.full_control https://www.googleapis.com/auth/fusiontables&access_type=offline&redirect_uri=http://fatapp.emelle.me/canvas/oauth2callback&client_id=1021901972772.apps.googleusercontent.com&hl=en-US
		$client = new apiClient();
		$client->discover('oauth2');
		$client->setScopes(array('https://www.googleapis.com/auth/devstorage.full_control'));
		
		if (isset($_GET['code'])) {
			$client->authenticate();
			//Write tokens to database
			$token = json_decode($client->getAccessToken());
			$t = DataObject::get_one('SiteConfig',false);
			$currenttime = time();
			$t->expiry  = $currenttime + $token->expires_in;
			$t->refresh_token = $token->refresh_token;
			$t->access_token = $token->access_token;
			$t->token_type = $token->token_type;
			$t->write();
			$_SESSION['token'] = $client->getAccessToken();
			header('Location: http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF']);
		}
		
		if (isset($_SESSION['token'])) {
  			$client->setAccessToken($_SESSION['token']);
  			echo 'OAuth succeeded!';
  			var_dump($_SESSION['token']);
		}else {
			$client->authenticate();
		}
	}
	
	public function refreshToken($arguments)
	{
		//Oauth callback for the Google.
		// https://accounts.google.com/o/oauth2/auth?response_type=code&scope=https://www.googleapis.com/auth/devstorage.full_control https://www.googleapis.com/auth/fusiontables&access_type=offline&redirect_uri=http://fatapp.emelle.me/canvas/oauth2callback&client_id=1021901972772.apps.googleusercontent.com&hl=en-US
		$client = new apiClient();
		$client->discover('oauth2');
		$client->setScopes(array('https://www.googleapis.com/auth/devstorage.full_control'));
		$config = DataObject::get_one('SiteConfig',false);
		var_dump($config);
		$currenttime = time();
		$isExpired = ($config->expiry < $currenttime) ? TRUE : FALSE;
			
		if ($isExpired)
		{
			# Token Expired. Refresh that bad boy.
			$client = new apiClient();
			$client->refreshToken($config->refresh_token);
			$client->authenticate();
			$newtoken = json_decode($client->getAccessToken());
			$config->expiry  = $currenttime + $newtoken->expires_in;
			$config->access_token = $newtoken->access_token;
			$config->write();
			echo 'OAuth token refreshed!';
  			var_dump($_SESSION['token']);
		}else{
			echo 'Token expires in '.(int)$config->expiry - (int)$currenttime.'seconds';
  			var_dump($_SESSION['token']);
		}
	}
}
