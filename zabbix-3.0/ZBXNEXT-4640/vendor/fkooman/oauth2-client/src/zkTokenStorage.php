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

class zkTokenStorage implements TokenStorageInterface
{
    public function __construct()
    {
    }

    /**
     * @param string $userId
     *
     * @return array
     */
    public function getAccessTokenList($sessionid)
    {
        $TokenArray = DBfetch(DBselect('SELECT kc_session_state session_state, user_id, provider_id, issued_at, id_token, access_token, refresh_token, expires_in, refresh_expires_in, token_type, scope
            from access_tokens
            where zbx_session_id='.zbx_dbstr($sessionid)));
        if (empty($TokenArray)) {
            $TokenObj = false;
        }else{
            $TokenArray['expires_in'] = null !== $TokenArray['expires_in'] ? (int) $TokenArray['expires_in'] : null;
            $TokenArray['refresh_expires_in'] = null !== $TokenArray['refresh_expires_in'] ? (int) $TokenArray['refresh_expires_in'] : null;
            $TokenObj = new AccessToken($TokenArray);
        }
        return $TokenObj;
    }

    /**
     * @param string      $userId
     * @param AccessToken $accessToken
     *
     * @return void
     */
    public function storeAccessToken($sessionid, AccessToken $accessToken)
    {
        //store raw-token into zabbix-DB 
        $current_token = $accessToken;
        $sql_1 = 'INSERT INTO access_tokens (zbx_session_id, kc_session_state, user_id, provider_id, issued_at, id_token, access_token, refresh_token, expires_in, refresh_expires_in, token_type, scope) VALUES (';
        $sql_2 = '\''.$sessionid.'\''.',' ;
        $sql_3 = '\''.$current_token->getKCSessionState().'\''.',' ;
        $sql_4 = '\''.'zbx'.'\''.',' ;
        $sql_5 = '\''.$current_token->getProviderId().'\''.',' ;
        $sql_6 = '\''.$current_token->getIssuedAt()->format('Y-m-d H:i:s').'\''.',' ;
        $sql_7 = '\''.$current_token->getTokenId().'\''.',' ;
        $sql_8 = '\''.$current_token->getToken().'\''.',' ;
        $sql_9 = '\''.$current_token->getRefreshToken().'\''.',' ;
        $sql_10= '\''.$current_token->getExpiresIn().'\''.',' ;
        $sql_11= '\''.$current_token->getRefreshExpiresIn().'\''.',' ;
        $sql_12= '\''.$current_token->getTokenType().'\''.',' ;
        $sql_13= '\''.$current_token->getScope().'\''.')' ;
        $sql = $sql_1.$sql_2.$sql_3.$sql_4.$sql_5.$sql_6.$sql_7.$sql_8.$sql_9.$sql_10.$sql_11.$sql_12.$sql_13;
        DBexecute($sql);
    }

    /**
     * @param string      $userId
     * @param AccessToken $accessToken
     *
     * @return void
     */
    public function deleteAccessToken($sessionid, AccessToken $accessToken)
    {
        DBexecute('DELETE FROM access_tokens where zbx_session_id='.zbx_dbstr($sessionid));
    }
}
