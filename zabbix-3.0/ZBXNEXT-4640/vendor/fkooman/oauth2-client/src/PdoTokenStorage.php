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

use PDO;

class PdoTokenStorage implements TokenStorageInterface
{
    /** @var \PDO */
    private $db;

    public function __construct(PDO $db)
    {
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        if ('sqlite' === $db->getAttribute(PDO::ATTR_DRIVER_NAME)) {
            $db->query('PRAGMA foreign_keys = ON');
        }

        $this->db = $db;
    }

    /**
     * @return void
     */
    public function init()
    {
        $this->db->query(
            'CREATE TABLE IF NOT EXISTS access_tokens (
                user_id TEXT NOT NULL,
                provider_id TEXT NOT NULL,
                issued_at DATETIME NOT NULL,
                access_token TEXT NOT NULL,
                token_type TEXT NOT NULL,
                expires_in INT,
                refresh_token TEXT,
                scope TEXT NOT NULL
            )'
        );
    }

    /**
     * @param string $userId
     *
     * @return array
     */
    public function getAccessTokenList($userId)
    {
        $stmt = $this->db->prepare(
            'SELECT
                provider_id, issued_at, access_token, token_type, expires_in, refresh_token, scope
             FROM access_tokens
             WHERE
                user_id = :user_id'
        );

        $stmt->bindValue(':user_id', $userId, PDO::PARAM_STR);
        $stmt->execute();

        $accessTokenList = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            // convert expires_in to int if it is not NULL
            $row['expires_in'] = null !== $row['expires_in'] ? (int) $row['expires_in'] : null;
            $accessTokenList[] = new AccessToken($row);
        }

        return $accessTokenList;
    }

    /**
     * @param string      $userId
     * @param AccessToken $accessToken
     *
     * @return void
     */
    public function storeAccessToken($userId, AccessToken $accessToken)
    {
        $stmt = $this->db->prepare(
            'INSERT INTO access_tokens (
                user_id, provider_id, issued_at, access_token, token_type, expires_in, refresh_token, scope
             ) 
             VALUES(
                :user_id,
                :provider_id, :issued_at, :access_token, :token_type, :expires_in, :refresh_token, :scope
             )'
        );

        $stmt->bindValue(':user_id', $userId, PDO::PARAM_STR);
        $stmt->bindValue(':provider_id', $accessToken->getProviderId(), PDO::PARAM_STR);
        $stmt->bindValue(':issued_at', $accessToken->getIssuedAt()->format('Y-m-d H:i:s'), PDO::PARAM_STR);
        $stmt->bindValue(':access_token', $accessToken->getToken(), PDO::PARAM_STR);
        $stmt->bindValue(':token_type', $accessToken->getTokenType(), PDO::PARAM_STR);
        $stmt->bindValue(':expires_in', $accessToken->getExpiresIn(), PDO::PARAM_INT);
        $stmt->bindValue(':refresh_token', $accessToken->getRefreshToken(), PDO::PARAM_STR);
        $stmt->bindValue(':scope', $accessToken->getScope(), PDO::PARAM_STR);
        $stmt->execute();
    }

    /**
     * @param string      $userId
     * @param AccessToken $accessToken
     *
     * @return void
     */
    public function deleteAccessToken($userId, AccessToken $accessToken)
    {
        $stmt = $this->db->prepare(
            'DELETE FROM
                access_tokens
            WHERE
                user_id = :user_id
            AND
                provider_id = :provider_id
            AND
                access_token = :access_token'
        );

        $stmt->bindValue(':user_id', $userId, PDO::PARAM_STR);
        $stmt->bindValue(':provider_id', $accessToken->getProviderId(), PDO::PARAM_STR);
        $stmt->bindValue(':access_token', $accessToken->getToken(), PDO::PARAM_STR);
        $stmt->execute();
    }
}
