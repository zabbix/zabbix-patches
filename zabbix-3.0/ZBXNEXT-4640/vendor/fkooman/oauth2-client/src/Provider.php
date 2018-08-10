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

class Provider
{
    /** @var string */
    private $clientId;

    /** @var string|null */
    private $clientSecret;

    /** @var string */
    private $authorizationEndpoint;

    /** @var string */
    private $tokenEndpoint;
    
    /** @var string ,Add logout URI*/
    private $logoutEndpoint;

    /**
     * @param string      $clientId
     * @param string|null $clientSecret
     * @param string      $authorizationEndpoint
     * @param string      $tokenEndpoint
     * @param string      $logoutEndpoint 
     */
    public function __construct($clientId, $clientSecret, $authorizationEndpoint, $tokenEndpoint, $logoutEndpoint)
    {
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
        $this->authorizationEndpoint = $authorizationEndpoint;
        $this->tokenEndpoint = $tokenEndpoint;
        $this->logoutEndpoint = $logoutEndpoint;
    }

    /**
     * @return string
     */
    public function getProviderId()
    {
        return sprintf('%s|%s', $this->getAuthorizationEndpoint(), $this->getClientId());
    }

    /**
     * @return string
     *
     * @see https://tools.ietf.org/html/rfc6749#section-2.2
     */
    public function getClientId()
    {
        return $this->clientId;
    }

    /**
     * @return string|null
     *
     * @see https://tools.ietf.org/html/rfc6749#section-2.3.1
     */
    public function getSecret()
    {
        return $this->clientSecret;
    }

    /**
     * @return string
     *
     * @see https://tools.ietf.org/html/rfc6749#section-3.1
     */
    public function getAuthorizationEndpoint()
    {
        return $this->authorizationEndpoint;
    }

    /**
     * @return string
     *
     * @see https://tools.ietf.org/html/rfc6749#section-3.2
     */
    public function getTokenEndpoint()
    {
        return $this->tokenEndpoint;
    }

    /**
     * @return string, Add this function to get logoutEndpoint.
     *
     * @see https://tools.ietf.org/html/rfc6749#section-3.2
     */
    public function getLogoutEndpoint()
    {
        return $this->logoutEndpoint;
    }
}
