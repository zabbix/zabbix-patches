# Changes

## 5.0.3-zk (2018-06-01)
- Do some change to make zabbix support keycloak

## 5.0.3 (2017-11-16)
- also support `Bearer` as `token_type` in addition to `bearer` (issue #12)

## 5.0.2 (2017-11-08)
- add support for PHPUnit 6
- various fixes to solve token removal in case of expiry (issue #10)

## 5.0.1 (2017-09-18)
- many small code style improvements
- more complete phpdoc
- fix issues found by [Psalm](https://getpsalm.org/) and 
  [phpstan](https://github.com/phpstan/phpstan)
 
## 5.0.0 (2017-07-06)
- complete rewrite of the OAuth client, everything is different

## 4.0.1 (2017-01-19)
- update `RandomInterface` a bit to allow specifying the length of the secret
- update (C) year
- fix a HTTP client bug where a potential non array could be returned

## 4.0.0 (2017-01-04)
- remove Guzzle dependency

## 3.0.2 (2016-01-03)
- be less restrictive for `paragonie/random_compat` dependency

## 3.0.1 (2016-12-12)
- change license to AGPLv3+

## 3.0.0 (2016-11-25)
- if token endpoint does not return a scope value, the scope from the request
  is assumed to be granted (according to specification)
- code cleanup
- HTTP clients should now return array instead of JSON string
- restore Guzzle client again
- remove cURL client again, too hard to get right

## 2.0.2 (2016-11-15)
- use PSR-4 now

## 2.0.1 (2016-09-29)
- fix `expires_in` response from token endpoint, add test for it

## 2.0.0 (2016-08-30)
- remove Guzzle client
- add simple cURL client

## 1.0.1 (2016-06-04)
- add API documentation
- improve input validation

## 1.0.0 (2016-05-30)
- initial release
