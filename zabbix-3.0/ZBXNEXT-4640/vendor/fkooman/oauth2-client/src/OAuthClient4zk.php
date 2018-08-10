<?php

/**
 * Copyright (c) 2016, 2017 François Kooman <fkooman@tuxed.net>.
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

namespace fkooman\OAuth\Client;

use DateTime;
use fkooman\OAuth\Client\Exception\OAuthException;
use fkooman\OAuth\Client\Exception\OAuthServerException;
use fkooman\OAuth\Client\Http\HttpClientInterface;
use fkooman\OAuth\Client\Http\Request;
use fkooman\OAuth\Client\Http\Response;
use ParagonIE\ConstantTime\Base64;
use ParagonIE\ConstantTime\Base64UrlSafe;

class OAuthClient4zk
{
    /** @var TokenStorageInterface */
    private $tokenStorage;

    /** @var \fkooman\OAuth\Client\Http\HttpClientInterface */
    private $httpClient;

    /** @var SessionInterface */
    private $session;

    /** @var RandomInterface */
    private $random;

    /** @var \DateTime */
    private $dateTime;

    /** @var Provider */
    private $provider = null;

    /** @var string */
    private $userId = null;

    /**
     * @param TokenStorageInterface    $tokenStorage
     * @param Http\HttpClientInterface $httpClient
     * @param SessionInterface|null    $session
     * @param RandomInterface|null     $random
     * @param \DateTime|null           $dateTime
     */
    public function __construct(
        TokenStorageInterface $tokenStorage,
        HttpClientInterface $httpClient,
        SessionInterface $session = null,
        RandomInterface $random = null,
        DateTime $dateTime = null
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->httpClient = $httpClient;
        if (null === $session) {
            $session = new Session();
        }
        $this->session = $session;
        if (null === $random) {
            $random = new Random();
        }
        $this->random = $random;
        if (null === $dateTime) {
            $dateTime = new DateTime();
        }
        $this->dateTime = $dateTime;
    }

    /**
     * @param \DateTime $dateTime
     *
     * @return void
     */
    public function setDateTime(DateTime $dateTime)
    {
        $this->dateTime = $dateTime;
    }

    /**
     * @param Provider $provider
     *
     * @return void
     */
    public function setProvider(Provider $provider)
    {
        $this->provider = $provider;
    }

    /**
     * @param string $userId
     *
     * @return void
     */
    public function setUserId($userId)
    {
        $this->userId = $userId;
    }

    /**
     * Obtain an authorization request URL to start the authorization process
     * at the OAuth provider.
     *
     * @param string $scope       the space separated scope tokens
     * @param string $redirectUri the URL registered at the OAuth provider, to
     *                            be redirected back to
     *
     * @return string the authorization request URL
     *
     * @see https://tools.ietf.org/html/rfc6749#section-3.3
     * @see https://tools.ietf.org/html/rfc6749#section-3.1.2
     */
    public function getAuthorizeUri($scope, $redirectUri)
    {
        if (PHP_SESSION_ACTIVE !== session_status()) {
            session_start();
        }
        if (null === $this->userId) {
            throw new OAuthException('userId not set');
        }

        $codeVerifier = $this->generateCodeVerifier();

        $queryParameters = [
            'client_id' => $this->provider->getClientId(),
            'redirect_uri' => $redirectUri,
            'scope' => $scope,
            'state' => $this->random->get(16),
            'response_type' => 'code',
            'code_challenge_method' => 'S256',
            'code_challenge' => self::hashCodeVerifier($codeVerifier),
        ];

        $authorizeUri = sprintf(
            '%s%s%s',
            $this->provider->getAuthorizationEndpoint(),
            false === strpos($this->provider->getAuthorizationEndpoint(), '?') ? '?' : '&',
            http_build_query($queryParameters, '&')
        );
        $this->session->set(
            '_oauth2_session',
            array_merge(
                $queryParameters,
                [
                    'code_verifier' => $codeVerifier,
                    'user_id' => $this->userId,
                    'provider_id' => $this->provider->getProviderId(),
                ]
            )
        );
        return $authorizeUri;
    }

    /**
     * @param string $codeVerifier
     *
     * @return string
     */
    private static function hashCodeVerifier($codeVerifier)
    {
        return rtrim(
            Base64UrlSafe::encode(
                hash(
                    'sha256',
                    $codeVerifier,
                    true
                )
            ),
            '='
        );
    }

    /**
     * @return string
     */
    private function generateCodeVerifier()
    {
        return rtrim(
            Base64UrlSafe::encode(
                $this->random->get(32, true)
            ),
            '='
        );
    }

    // -------------------------------------
    // The blow functions coded for  zabbix
    // -------------------------------------
    
    //-------------------------------------------------
    // 1.The following to TALK with keycloak-server.
    //-------------------------------------------------
    public function logout($sessionid)
    {
        $refresh_token = $this->getAccessToken($sessionid)->getRefreshToken();
        $RequestData = [
            'refresh_token' => $refresh_token,
        ];
        $RequestHeaders = [
            'Authorization' => sprintf('Basic %s',Base64::encode(sprintf('%s:%s', $this->provider->getClientId(), $this->provider->getSecret()))),
            ];
        $request = Request::post($this->provider->getLogoutEndpoint(), $RequestData, $RequestHeaders);
        $response = $this->httpClient->send($request);
    }

    public function checkKeycloakServer(){
        //Check the keycloak_server--realm--client can be connected.....
        $verifyData = ['grant_type' => 'client_credentials'];
        $verifyHeader = [
            'Authorization' => sprintf('Basic %s',Base64::encode(sprintf('%s:%s',$this->provider->getClientId(),$this->provider->getSecret()))),
            "Content-Type"  => 'application/x-www-form-urlencoded'
        ];
        $verifyReq = Request::post($this->provider->getTokenEndpoint(),$verifyData, $verifyHeader);
        $verifyRes = $this->httpClient->send($verifyReq);
        if (400 === $verifyRes->getStatusCode()) {
            // check for "invalid_grant"
            $responseData = $verifyRes->json();
            if (!array_key_exists('error', $responseData) || 'invalid_grant' !== $responseData['error']) {
                // not an "invalid_grant", we can't deal with this here...
                // throw new OAuthServerException($verifyRes);
                return false;
            }
            // throw new OAuthException('authorization_code was not accepted by the server');
            return false;
        }
        if (!$verifyRes->isOkay()) {
            // if there is any other error, we can't deal with this here...
            // throw new OAuthServerException($response);
            return false; 
        }
        # Acquire the Token Raw data....
        $this->dateTime = new DateTime(); //obsolete the dateTime when client-obj initialized,use the current time as the issue time.
        $verify_token = AccessToken::fromCodeResponse($this->provider,$this->dateTime,$verifyRes->json(),'full');//construct the full token date

        //Check the keycloak_server--realm--client can be connected.....
        $clearData = ['refresh_token' => $verify_token->getRefreshToken()];
        $clearHeader = [
            'Authorization' => sprintf('Basic %s',Base64::encode(sprintf('%s:%s',$this->provider->getClientId(),$this->provider->getSecret()))),
            "Content-Type"  => 'application/x-www-form-urlencoded'
        ];
        $clearReq = Request::post($this->provider->getLogoutEndpoint(),$clearData, $clearHeader);
        $clearRes = $this->httpClient->send($clearReq);

        if (!$clearRes->isOkay()) {
            // if there is any other error, we can't deal with this here...
            // throw new OAuthServerException($response);
            return false;
        }
        return true;
    }

    public function DAGloginKeycloakServer($user){
        $loginHeader = [
            'Authorization' => sprintf('Basic %s',Base64::encode(sprintf('%s:%s',$this->provider->getClientId(),$this->provider->getSecret()))),
            "Content-Type"  => 'application/x-www-form-urlencoded'
        ];
        $loginData = [
            'grant_type' => 'password',
            'username' => $user['user'],
            'password' => $user['password'] 
        ];
        $loginReq = Request::post($this->provider->getTokenEndpoint(),$loginData, $loginHeader);
        $loginRes = $this->httpClient->send($loginReq);
        if (400 === $loginRes->getStatusCode()) {
            return false;
        }
        if (!$loginRes->isOkay()) {
            // if there is any other error, we can't deal with this here...
            return false; 
        }
        $this->dateTime = new DateTime();
        $current_token = AccessToken::fromCodeResponse($this->provider,$this->dateTime,$loginRes->json(),'full');
        $this->mapuser_kc2zbx($current_token);
        $userid = DBfetch(DBselect('select userid from users where alias='.zbx_dbstr($current_token->getName())))['userid'];
        $sessionid = md5(microtime().$current_token->getKCSessionState().mt_rand());
        $this->tokenStorage->storeAccessToken($sessionid,$current_token);
        DBexecute('INSERT INTO sessions (sessionid,userid,lastaccess,status)'.
                  ' VALUES ('.zbx_dbstr($sessionid).','.zbx_dbstr($userid).','.time().','.ZBX_SESSION_ACTIVE.')');
        return $sessionid;
    }
    
    //-------------------------------------------------
    // 2.@The following to DELETE|GET|STORE|UPDATE Token DATA from keycloak via ZBX default DataBase
    //-------------------------------------------------
    public function hasAccessToken($sessionid)
    {
        if (false === $accessToken = $this->getAccessToken($sessionid)) {
            return false;
        }

        // is it expired? but do we have a refresh_token?
        $this->dateTime = new DateTime(); //obsolete the dateTime when client-obj initialized,use the current time as the issue time.
        if ($accessToken->isExpired($this->dateTime)) {
            // access_token is expired
            if (null !== $accessToken->getRefreshToken()) {
                // but we have a refresh_token
                return true;
            }

            // no refresh_token
            return false;
        }

        // not expired
        return true;
    }

    public function getAccessToken($sessionid)
    {
        $accessToken = $this->tokenStorage->getAccessTokenList($sessionid);                                                                                                               
        if (false == $accessToken) {
            return false;  
        }
        return $accessToken;                                                                                                                                                                 
    }

    public function refreshAccessToken(AccessToken $accessToken ,$sessionid)
    {
        // prepare access_token request
        $tokenRequestData = [
            'grant_type' => 'refresh_token',
            'refresh_token' => $accessToken->getRefreshToken(),
            'scope' => $accessToken->getScope(),
        ];

        $requestHeaders = [];
        // if we have a secret registered for the client, use it
        if (null !== $this->provider->getSecret()) {
            $requestHeaders = [
                'Authorization' => sprintf(
                    'Basic %s',
                    Base64::encode(
                        sprintf('%s:%s', $this->provider->getClientId(), $this->provider->getSecret())
                    )
                ),
            ];
        }

        $response = $this->httpClient->send(
            Request::post(
                $this->provider->getTokenEndpoint(),
                $tokenRequestData,
                $requestHeaders
            )
        );

        if (400 === $response->getStatusCode()) {
            // check for "invalid_grant"
            $responseData = $response->json();
            if (!array_key_exists('error', $responseData) || 'invalid_grant' !== $responseData['error']) {
                // not an "invalid_grant", we can't deal with this here...
                throw new OAuthServerException($response);
            }

            // delete the access_token, we assume the user revoked it
            $this->tokenStorage->deleteAccessToken($sessionid,$accessToken);

            return false;
        }

        if (!$response->isOkay()) {
            // if there is any other error, we can't deal with this here...
            throw new OAuthServerException($response);
        }
        $this->dateTime = new DateTime(); //obsolete the dateTime when client-obj initiaaized  use the current time as the issue time.
        $new_token = AccessToken::fromRefreshResponse($this->provider,$this->dateTime,$response->json(),$accessToken);//construct the full token date
        $this->tokenStorage->deleteAccessToken($sessionid);
        $this->tokenStorage->storeAccessToken($sessionid,$new_token);
        
        return $new_token;
    }

    public function mapuser_kc2zbx (AccessToken $accessToken){
        $config=select_config();
        $token = $accessToken;
        //get the define group of a keycloak user
        $user_group_kc = $token->getClientRole($config['keycloak_client_id']);
        //get all  group in zabbix
        $user_group_zbx = DBfetchArray(DBselect('select * from usrgrp'));
        //resort the zabbix group info
        $tmp = [];
        foreach ($user_group_zbx as $key => $value) {
            $tmp[$value['usrgrpid']] = $value['name'];
        }
        //assign the zabbix group id to keycloak user
        $kc2zbx_groupid = null;
        foreach ($tmp as $key => $value) {
            if ($user_group_kc[0] == $value) {
                $kc2zbx_groupid = $key;
            }else{
                continue;
            }
        }
        //assign the zabbix usertype to keycloak user
        $kc2zbx_usertype = null;
        for ($i = 0; $i < count($token->getRealmRole()); $i++) {
            preg_match('/zbx_.*/i',$token->getRealmRole()[$i],$matches);
            if (empty($matches)) {
                continue;
            }else{
                $kc2zbx_usertype =  $matches[0];
                break;
            }
        }
        if ($kc2zbx_usertype == "zbx_user") {
            $kc2zbx_usertype = 1;
        }elseif($kc2zbx_usertype == "zbx_admin"){
            $kc2zbx_usertype = 2;
        }elseif($kc2zbx_usertype == "zbx_super_admin"){
            $kc2zbx_usertype = 3;
        }else{
            $kc2zbx_usertype = 0;
        }
        //create the mapping user profiles
        $user_kc = [];
        $user_kc['alias']           = $token->getName();
        $user_kc['name']            = "from";
        $user_kc['surname']         = "keycloak";
        $user_kc['passwd']          = md5(mt_rand().$user_kc['alias'].time()); //we don't know the password...
        $user_kc['url']             = "";
        $user_kc['autologin']       = 0;
        $user_kc['autologout']      = 0;
        $user_kc['lang']            = "en_GB";
        $user_kc['refresh']         = "30";
        $user_kc['type']            = $kc2zbx_usertype;
        $user_kc['theme']           = "default";
        $user_kc['attempt_failed']  = 0;
        $user_kc['attempt_ip']      =  $_SERVER['REMOTE_ADDR'];
        $user_kc['attempt_clock']   = time();
        $user_kc['rows_per_page']   = "50";
        $user_kc['user_medias']     =[] ;
        $user_kc['usrgrps']         = [["usrgrpid" => $kc2zbx_groupid]];
        //turn the user info to array type
        $user_kc_s = zbx_toArray($user_kc);
        //insert the user into zabbix
        $flag = DBfetch(DBselect('select *  from users where alias='.zbx_dbstr($user_kc_s[0]['alias'])));
        if (false ==  $flag) {
            //INSERT the user-info for current keycloak-user.
            //Due to in fkooman namespace, So shooul add the reverse slash to refer to the global DB Class.
            $userids = \DB::insert('users', $user_kc_s);

            foreach ($user_kc_s as $unum => $user_kc) {
                //INSERT the users_groups-info for current keycloak-user.
                $userid = $userids[$unum];
                $usrgrps = zbx_objectValues($user_kc['usrgrps'], 'usrgrpid');
                foreach ($usrgrps as $groupid) {
                    $usersGroupdId = get_dbid('users_groups', 'id');
                    $sql = 'INSERT INTO users_groups (id,usrgrpid,userid) VALUES ('.zbx_dbstr($usersGroupdId).','.zbx_dbstr($groupid).','.zbx_dbstr($userid).')';
                    if (!DBexecute($sql)) {
                        self::exception(ZBX_API_ERROR_PARAMETERS, 'DBerror');
                    }
                }
                //inser the users_media-info for current keycloak-user.
                foreach ($user_kc['user_medias'] as $mediaData) {
                    $mediaid = get_dbid('media', 'mediaid');
                    $sql = 'INSERT INTO media (mediaid,userid,mediatypeid,sendto,active,severity,period)'.
                        ' VALUES ('.zbx_dbstr($mediaid).','.zbx_dbstr($userid).','.zbx_dbstr($mediaData['mediatypeid']).','.
                        zbx_dbstr($mediaData['sendto']).','.zbx_dbstr($mediaData['active']).','.zbx_dbstr($mediaData['severity']).','.
                        zbx_dbstr($mediaData['period']).')';
                    if (!DBexecute($sql)) {
                        self::exception(ZBX_API_ERROR_PARAMETERS, 'DBerror');
                    }
                }
            }
        }
        return true;
    }

    //-------------------------------------------------
    // 2.@The following to DELETE|GET|STORE|UPDATE Token DATA from keycloak via ZBX default DataBase
    //-------------------------------------------------
    public function handleCallback($responseCode, $responseState)
    {
        if (null === $this->userId) {
            throw new OAuthException('userId not set');
        }

        // get and delete the OAuth session information
        $sessionData = $this->session->take('_oauth2_session');

        if (!hash_equals($sessionData['state'], $responseState)) {
            // the OAuth state from the initial request MUST be the same as the
            // state used by the response
            throw new OAuthException('invalid session (state)');
        }

        // session providerId MUST match current set Provider
        if ($sessionData['provider_id'] !== $this->provider->getProviderId()) {
            throw new OAuthException('invalid session (provider_id)');
        }

        // session userId MUST match current set userId
        if ($sessionData['user_id'] !== $this->userId) {
            throw new OAuthException('invalid session (user_id)');
        }

        // prepare access_token request
        $tokenRequestData = [
            'client_id' => $this->provider->getClientId(),
            'grant_type' => 'authorization_code',
            'code' => $responseCode,
            'redirect_uri' => $sessionData['redirect_uri'],
            'code_verifier' => $sessionData['code_verifier'],
        ];

        $requestHeaders = [];
        // if we have a secret registered for the client, use it
        if (null !== $this->provider->getSecret()) {
            $requestHeaders = [
                'Authorization' => sprintf(
                    'Basic %s',
                    Base64::encode(
                        sprintf('%s:%s', $this->provider->getClientId(), $this->provider->getSecret())
                    )
                ),
            ];
        }

        $response = $this->httpClient->send(
            Request::post(
                $this->provider->getTokenEndpoint(),
                $tokenRequestData,
                $requestHeaders
            )
        );

        if (400 === $response->getStatusCode()) {
            // check for "invalid_grant"
            $responseData = $response->json();
            if (!array_key_exists('error', $responseData) || 'invalid_grant' !== $responseData['error']) {
                // not an "invalid_grant", we can't deal with this here...
                throw new OAuthServerException($response);
            }

            throw new OAuthException('authorization_code was not accepted by the server');
        }

        if (!$response->isOkay()) {
            // if there is any other error, we can't deal with this here...
            throw new OAuthServerException($response);
        }
        # Acquire the Token Raw data....
        $this->dateTime = new DateTime(); //obsolete the dateTime when client-obj initialized,use the current time as the issue time.
        $current_token = AccessToken::fromCodeResponse($this->provider,$this->dateTime,$response->json(),$sessionData['scope']);

        # Store the keycloak user to zabbix DB....
        $this->mapuser_kc2zbx($current_token);

        # Get the userID for the keycloak user;
        $userid = DBfetch(DBselect('select userid from users where alias='.zbx_dbstr($current_token->getName())))['userid'];

        # Use keycloak-session-state as the sessionid value....
        $sessionid = md5(microtime().$current_token->getKCSessionState().mt_rand());
        
        # Insert the token data to access_tokens-table....
        $this->tokenStorage->storeAccessToken($sessionid,$current_token);
        
        # Insert the Sessionid to sessions-table....
        DBexecute('INSERT INTO sessions (sessionid,userid,lastaccess,status)'.
                  ' VALUES ('.zbx_dbstr($sessionid).','.zbx_dbstr($userid).','.time().','.ZBX_SESSION_ACTIVE.')');
        
        # Set cookie value for Browser....
        setcookie('zbx_sessionid',$sessionid, strtotime('+1 month'));
    }

}
