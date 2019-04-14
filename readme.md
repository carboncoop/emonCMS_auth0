Emoncms is an open-source web application for processing, logging and visualising energy, 
temperature and other environmental data and is part of the [OpenEnergyMonitor project](
http://openenergymonitor.org).

Auth0 module has been developed by Carbon Co-op
  https://carbon.coop/

# Auth0 Module
Log into emonCMS using Auth0 (single sign platform)

## License
This module is released under the GNU Affero General Public License

## Installation
Clone repository
```
git clone https://github.com/carboncoop/emonCMS_auth0 auth0
```

Install dependencies `composer install`

in settings.php add configure your Auth0 credentials:
```$AUTH0_CLIENT_ID="the_client_id";
$AUTH0_DOMAIN="your_auth_domain";
$AUTH0_CLIENT_SECRET="your_client_secret";
$AUTH0_CALLBACK_URL="http://your_URL/"; // the rest of the URL is added by the Auth0 controller
$AUTH0_AUDIENCE="";
$AUTH0_STARTING_PAGE=""; // Page to redirect users after login
```