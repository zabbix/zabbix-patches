<?php
require 'vendor/autoload.php';
require_once '/usr/share/zabbix/include/classes/core/Z.php';
use fkooman\OAuth\Client\Http\CurlHttpClient;
use fkooman\OAuth\Client\OAuthClient4zk;
use fkooman\OAuth\Client\Provider;

Z::getInstance()->run4kc();
$config = select_config();
try{
    if (PHP_SESSION_ACTIVE !== session_status()) {
        session_start();
    }
    if (isset($_SESSION['zbx_oauth2_client_obj'])) {
        $client = $_SESSION['zbx_oauth2_client_obj'];
    }else{
        exit(1);
    }
    
    // do the following actions in handleCallback4kc function
    // 1.Get  TokenData from keycloak,and Store it in DB:access_tokens table
    // 2.Get sessionId based on TokenDtat::session_state variable,and Store the SessionId in DB:sessions table
    // 3.Set Cookie for Browser based on sessionId.
    $client->handleCallback($_GET['code'],$_GET['state']);
    session_write_close();
    http_response_code(302);
    header(sprintf('Location: %s', 'index.php'));
    exit(0);
}catch(Exception $e){
    echo sprintf('ERROR: %s', $e->getMessage());
    exit(1);
}











