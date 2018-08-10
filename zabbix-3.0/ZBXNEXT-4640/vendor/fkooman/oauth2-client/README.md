# Introduction
This zk503-Branch is for zabbix integrate with keycloak .
This zk503-branch based on fkooman/oauth2-client 5.0.3 version [fkooman/oauth2-client-5.0.3](https://github.com/fkooman/php-oauth2-client/tree/5.0.3).
Be default, fkooman/oauth2-client support PHP $SESSION and PHP PDO to store raw-token got from OAuth server.
While Zabbix have its own DB methods, and Use KEYCLOAK as OAuth server
So,Do some changes on fkooman/oauth2-client library, See the Features section below. 

**NOTE**: If you are not bound to PHP 5.4, you are probably better of using the 
OAuth 2.0 client of the League of Extraordinary Packages! It can be found 
[here](http://oauth2-client.thephpleague.com/).

# Solution 
Sunny scenario to describe this solution. 
1. Browser 1st-request the zabbix server. 
2. ZABBIX server find there is no cookie with the 1st-request 
3. ZABBIX server redirect browser to keycloak authentication page. 
4. User input username and password on authentication page.
5. KEYCLOAK login OK .
6. KEYCLOAK redirect browser to ZABBIX server with code and state parameters 
7. ZABBIX server exchange raw-token from KEYCLOAK server with code and state parameters. 
8. KEYCLOAK verify the code and state , issue a raw-token to ZABBIX. 
9. ZABBIX prase the access-token in raw-token to get the user information.
10. Based on the user informationi,ZABBIX decide to server the user or deny.

In general, for this solution, the 
1. Authentication part  is on KEYCLOAK side 
2. Authorization  part  is on ZABBIX   side 

If you also want this solution, you can use this library, else, skip this. 

# Features

* Add parse token function in AccessToken.php
* Add logouturl in Provider.php 
* Add functions in OAuthClient4zk.php to handle with ZABBIX.
* Simplicity
* Works with PHP >= 5.4
* Minimal dependencies;
* Supports OAuth refresh tokens.
* Easy integration with your own application and/or framework;
* Does not enforce a framework on you;
* Only "authorization code" profile support, will not implement anything else;
* Only conforming OAuth 2.0 servers will work, this library will not get out of 
  its way to deal with services that violate the OAuth 2.0 RFC;
* Supports Proof Key for Code Exchange for public clients where no secret is
  used;
* There will be no toggles to shoot yourself in the foot;
* Uses `paragonie/constant_time_encoding` for constant time encoding;
* Uses `paragonie/random_compat` polyfill for CSPRNG;
* Uses `symfony/polyfill-php56` polyfill for `hash_equals`;

You **MUST** configure PHP in such a way that it enforces secure cookies! 
See 
[this](https://paragonie.com/blog/2015/04/fast-track-safe-and-secure-php-sessions) 
resource for more information.

# API

The API is very simple to use. See the `example/` folder for a working example!

# Security

As always, make sure you understand what you are doing! Some resources:

* [The Fast Track to Safe and Secure PHP Sessions](https://paragonie.com/blog/2015/04/fast-track-safe-and-secure-php-sessions)
* [The OAuth 2.0 Authorization Framework](https://tools.ietf.org/html/rfc6749)
* [The OAuth 2.0 Authorization Framework: Bearer Token Usage](https://tools.ietf.org/html/rfc6750)
* [OAuth 2.0 Threat Model and Security Considerations](https://tools.ietf.org/html/rfc6819)
* [securityheaders.io](https://securityheaders.io/)
* [Proof Key for Code Exchange by OAuth Public Clients](https://tools.ietf.org/html/rfc7636)

# License

[MIT](LICENSE).
