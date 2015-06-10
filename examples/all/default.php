<?php
	// Start a session (files are loaded later).
	session_start();
	
	// Get the current network from PATH_INFO.
	$server_url = ltrim($_SERVER["PATH_INFO"], "/");
	
	$servers = new stdClass();
	//$servers->{$url}		= Array("id" => $client_id, "secret" => $client_secret, "scope" => Array($a_permission), "class" => $oauth_class, "file" => $oauth_file);
	$servers->facebook		= Array("id" => "0000000000000000", "secret" => "0000000000000000000000000000000000000000", "scope" => Array("email", "user_friends"), "class" => "OAuthFacebook", "file" => "facebook.class.php");
	$servers->google		= Array("id" => "0000000000000000", "secret" => "0000000000000000000000000000000000000000", "class" => "OAuthGoogle", "file" => "google.class.php");
	$servers->microsoft		= Array("id" => "0000000000000000", "secret" => "0000000000000000000000000000000000000000", "class" => "OAuthMicrosoft", "file" => "microsoft.class.php");
	$servers->yahoo			= Array("id" => "0000000000000000", "secret" => "0000000000000000000000000000000000000000", "class" => "OAuthYahoo", "file" => "yahoo.class.php");
	
	if(!isset($servers->{$server_url})) {
		echo "Server was not found.<br />\n\n";
		goto session;
	}
	
	$server = (object)$servers->{$server_url};
	$server->url = $server_url;
	if(!isset($server->name)) $server->name = ucfirst(strtolower($server->url));
	if(!isset($server->scope)) $server->scope = Array();
	require_once __DIR__ . '/oauth-client/' . ltrim($server->file, "/");
	$oauth = new $server->class($server->id, $server->secret, Array("errors" => Array("throw" => false)));
	
	// Delete the access token if needed.
	if(isset($_GET["del_token"])) $oauth->accessToken(false);
	
	// Output a Login Button.
	echo $oauth->loginButton("Login with {$server->name}", "https://example.com/code.php/{$server->url}", $server->scope);
	echo "\n<br />\n\n";
	
	// Try fetching the user's data. If an error is thrown, show a link to the login dialog.
	if($oauth->accessToken() != null) {
		// The user is logged in, you can do whatever you like here.
		// In this example we just print the profile data.
		try {
			$profile = $oauth->userProfile();
			echo "<pre>" . print_r($profile, true) . "</pre><br /><br />\n\n";
		} catch(Exception $error) {
			// Errors are added to $oauth->error.
			echo "<pre>Error: " . print_r($error, true) . "</pre><br /><br />\n\n";
		}
	} else {
		echo "You have not granted access to {$server->name}. Click the link above.<br />\n\n";
	}
	
	session:
	echo "<pre>Session: " . print_r($_SESSION, true) . "</pre>\n";
	