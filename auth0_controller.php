<?php

/*
  All Emoncms code is released under the GNU Affero General Public License.
  See COPYRIGHT.txt and LICENSE.txt.

  ---------------------------------------------------------------------
  Emoncms - open source energy visualisation
  Part of the OpenEnergyMonitor project:
  http://openenergymonitor.org
 
  Auth0 module has been developed by Carbon Co-op
  https://carbon.coop/

 */

// no direct access
defined('EMONCMS_EXEC') or die('Restricted access');

require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/dotenv-loader.php';

use Auth0\SDK\Auth0;

function auth0_controller() {

    global $mysqli, $redis, $user, $path, $session, $route, $enable_multi_user, $email_verification;
    global $AUTH0_CLIENT_ID, $AUTH0_DOMAIN, $AUTH0_CLIENT_SECRET, $AUTH0_CALLBACK_URL, $AUTH0_AUDIENCE, $AUTH0_STARTING_PAGE;


    if ($AUTH0_AUDIENCE == '') {
        $AUTH0_AUDIENCE = 'https://' . $AUTH0_DOMAIN . '/userinfo';
    }

    $auth0 = new Auth0([
        'domain' => $AUTH0_DOMAIN,
        'client_id' => $AUTH0_CLIENT_ID,
        'client_secret' => $AUTH0_CLIENT_SECRET,
        'redirect_uri' => $AUTH0_CALLBACK_URL . "auth0/callback",
        'audience' => $AUTH0_AUDIENCE,
        'scope' => 'openid email profile',
        'persist_id_token' => true,
        'persist_access_token' => true,
        'persist_refresh_token' => true,
    ]);

    $result = false;

    if ($route->format == 'html') {

        if ($route->action == 'login' && !$session['read']) {
            $auth0->login();
        }

        if ($route->action == 'callback' && !$session['read']) {
            $userInfo = $auth0->getUser();

            if (!$userInfo) { // User is not authenticated, Forward user to MHEP login page
                $allowusersregister = true;
                if ($enable_multi_user === false && $user->get_number_of_users() > 0) {
                    $allowusersregister = false;
                }
                $result = view("Modules/user/login_block.php", array('allowusersregister' => $allowusersregister, 'verify' => array()));
            }
            else {
                // User is authenticated
                $email = $userInfo['email'];
                if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    http_response_code(400);
                    return "400 Bad Request";
                }
                else {
                    $result = $mysqli->query("SELECT id, username, apikey_write, admin, timezone, language FROM users WHERE email='$email'");
                    if ($result->num_rows === 0) { // User is not registered in MHEP, we create it
                        $username = str_replace('.', 'dot', str_replace('@', 'at', strtolower($userInfo['name'])));
                        $password = bin2hex(openssl_random_pseudo_bytes(4));
                        $data = $user->register($username, $password, $email);
                        $userid = $data['userid'];
                        $starting_page = $mysqli->real_escape_string($AUTH0_STARTING_PAGE);
                        $mysqli->query("UPDATE users SET startingpage = '$starting_page' WHERE id = '$userid'");
                    }
                    else { // User already registered in MHEP, we fetch the user
                        $data = $result->fetch_array();
                    }

                    // Login
                    session_regenerate_id();
                    $_SESSION['userid'] = isset($data['userid']) ? $data['userid'] : $data['id'];
                    $_SESSION['username'] = isset($data['username']) ? $data['username'] : $username;
                    $_SESSION['read'] = 1;
                    $_SESSION['write'] = 1;
                    $_SESSION['admin'] = isset($data['admin']) ? $data['admin'] : 0;
                    $_SESSION['lang'] = isset($data['languaje']) ? $data['languaje'] : 'en_EN';
                    $_SESSION['timezone'] = isset($data['timezone']) ? $data['timezone'] : 'UTC';
                    $_SESSION['startingpage'] = $AUTH0_STARTING_PAGE;

                    if ($redis) {
                        $userid = !is_null($data['userid']) ? $data['userid'] : $data['id'];
                        $redis->hmset("user:" . $userid, array('apikey_write' => $data['apikey_write']));
                    }

                    header('Location: ' . $path . $AUTH0_STARTING_PAGE);
                }
            }
        }

        if ($route->action == 'logout' && $session['read']) {
            $auth0->logout();
            $user->logout();
            header('Location: ' . $path);
        }
    }

    return array('content' => $result, 'fullwidth' => true);
}
