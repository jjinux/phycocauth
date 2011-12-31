<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * This is a sample controller that combines the following:
 * PHP, YouTube, OAuth2, CodeIgniter, the CodeIgniter Youtube API Library, and
 * the Google APIs Client Library for PHP.
 *
 * At the risk of boring you, I'm going to show you how to get this working
 * from the ground up.
 *
 * Setup PHP:
 *   This assumes you're running OS X 10.6.
 *   See: http://stackoverflow.com/questions/1293484/easiest-way-to-activate-php-and-mysql-on-mac-os-10-6-snow-leopard-or-10-7-lio
 *   Edit /etc/apache2/httpd.conf:
 *     Uncomment "#LoadModule php5_module libexec/apache2/libphp5.so".
 *   System Preferences >> Sharing:
 *     Turn on Web sharing.
 *   You can edit files in ~/Sites:
 *     They'll be visible on: http://localhost/~yourusername/
 *
 * Setup CodeIgniter:
 *   See: http://codeigniter.com/user_guide/installation/index.html
 *   Grab it from: http://codeigniter.com/user_guide/installation/downloads.html
 *   Uncompress it into ~/Sites, and remove the top-level directory.
 *   Edit ~/Sites/application/config/config.php:
 *     Set the encryption_key to something long and random.
 *
 * Setup the CodeIgniter Youtube API Library:
 *   Follow the instructions here:
 *     http://code.google.com/apis/youtube/articles/codeigniter_library.html
 *   I'm guessing the only thing that really matters is:
 *     application/libraries/youtube.php
 *
 * Setup the Google APIs Client Library for PHP:
 *   Follow the instructions here:
 *     http://code.google.com/p/google-api-php-client/
 *   This should result in:
 *     application/libraries/google-api-php-client
 *
 * Setup Google API access:
 *   See: https://code.google.com/apis/console
 *   Generate a client id, client secret, etc.:
 *     Edit the defines below.
 *   Register your redirect URI:
 *     I used: http://localhost/~yourusername/index.php/example
 *
 * Setup a YouTube developer key:
 *   See: http://code.google.com/apis/youtube/dashboard
 *   I used localhost for the website.
 *   Edit the define below.
 *
 * Try loading: http://localhost/~yourusername/index.php/example
 *
 * Notes:
 *
 *   * This is just a proof-of-concept.  It is not polished code.
 *
 *   * It'd be really helpful to integrate OAuth2 handling into
 *     the CodeIgniter Youtube API Library directly.  That way,
 *     it could handle refreshing the access token, and it could
 *     handle passing the access token as a header.  See:
 *     http://code.google.com/apis/youtube/2.0/developers_guide_protocol_oauth2.html
 *
 *  * If there are problems, it can be difficult to get debugging information.
 *    Keep an eye on Apache's error logs.  I also find it helpful to hack the
 *    library source code directly to add echo statements.  For instance,
 *    it's helpful to edit application/libraries/youtube.php:_response_request
 *    to print the request and the response.
 *
 *  * The CodeIgniter Youtube API Library doesn't use HTTPS.
 */

require_once 'application/libraries/google-api-php-client/src/apiClient.php';
require_once 'application/libraries/google-api-php-client/src/contrib/apiPlusService.php';

define('CLIENT_ID', '296496462368.apps.googleusercontent.com');
define('CLIENT_SECRET', 'DXm2w1n3iuW5zlPsJH7vEv5u');
define('DEVELOPER_KEY', 'AIzaSyBdPZB-suhI6C1QJ9x2RcgIFfZesbWgqMI');
define('YOUTUBE_API_KEY', 'AI39si78MsgHDFDeYiy3ffsi6firJrvTcjYV0_2yQoaD6Fz96saV04drjIzxcYfuSrnrw-5tUFFuCmA8G84H-CKh55b7vKYRZw');

class Example extends CI_Controller
{
	public function index()
	{
		session_start();
		$client = new apiClient();
		$redirectUri = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'];
		$client->setApplicationName('PHP, YouTube, OAuth2, and CodeIgniter Example');
		$client->setClientId(CLIENT_ID);
		$client->setClientSecret(CLIENT_SECRET);
		$client->setRedirectUri($redirectUri);
		$client->setDeveloperKey(DEVELOPER_KEY);
		new apiPlusService($client);  // Sets the OAuth2 scope.

		$this->load->library('youtube',
			array('apikey' => YOUTUBE_API_KEY));

		// This example doesn't require authentication:
		// header("Content-type: text/plain");
		// echo "Here is the output:\n";
		// echo $this->youtube->getKeywordVideoFeed('pac man');

		if (isset($_GET['code']))
		{
			$client->authenticate();
			$_SESSION['token'] = $client->getAccessToken();
			header("Location: $redirectUri");
		}
		if (isset($_SESSION['token']))
		{
			$client->setAccessToken($_SESSION['token']);
		}
		if ( ! $client->getAccessToken())
		{
			$authUrl = $client->createAuthUrl();
			echo "<a class='login' href='$authUrl'>Connect Me!</a>";
		}
		else
		{
			// The access token may have been updated lazily.
			$_SESSION['token'] = $client->getAccessToken();

			header("Content-type: text/plain");
			$accessToken = json_decode($_SESSION['token'])->access_token;
			echo "Here is the output:\n";
			echo $this->youtube->getUserUploads('default', array(
				'access_token' => $accessToken,
				'prettyprint' => 'true'));
		}
	}
}
