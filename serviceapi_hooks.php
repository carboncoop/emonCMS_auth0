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

function serviceapi_on_logout($args) {

    require __DIR__ . '/vendor/autoload.php';
    require __DIR__ . '/dotenv-loader.php';
    use Auth0\SDK\Auth0;

    global $AUTH0_CLIENT_ID, $AUTH0_DOMAIN, $AUTH0_CLIENT_SECRET, $AUTH0_CALLBACK_URL, $AUTH0_AUDIENCE;

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

    $auth0->logout();
}

?>
