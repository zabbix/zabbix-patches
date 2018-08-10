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

namespace fkooman\OAuth\Client\Tests;

use fkooman\OAuth\Client\Http\HttpClientInterface;
use fkooman\OAuth\Client\Http\Request;
use fkooman\OAuth\Client\Http\Response;

class TestHttpClient implements HttpClientInterface
{
    public function send(Request $request)
    {
        if ('GET' === $request->getMethod()) {
            return $this->get($request->getUri(), $request->getHeaders());
        }

        if ('POST' === $request->getMethod()) {
            parse_str($request->getBody(), $postData);

            return $this->post($request->getUri(), $postData, $request->getHeaders());
        }

        return new Response(
            405,
            'METHOD NOT ALLOWED'
        );
    }

    private function get($requestUri, array $requestHeaders = [])
    {
        if ('https://example.org/unprotected_resource' === $requestUri) {
            return new Response(
                200,
                json_encode(['has_bearer_token' => array_key_exists('Authorization', $requestHeaders)]),
                ['Content-Type' => 'application/json']
            );
        }

        if ('https://example.org/resource' === $requestUri) {
            if (array_key_exists('Authorization', $requestHeaders)) {
                if ('Bearer AT:xyz' === $requestHeaders['Authorization']) {
                    return new Response(
                        200,
                        json_encode(['ok' => true]),
                        ['Content-Type' => 'application/json']
                    );
                }
                if ('Bearer AT:refreshed' === $requestHeaders['Authorization']) {
                    return new Response(
                        200,
                        json_encode(['refreshed' => true]),
                        ['Content-Type' => 'application/json']
                    );
                }

                return new Response(
                    401,
                    json_encode(['error' => 'invalid_token']),
                    [
                        'Content-Type' => 'application/json',
                        'WWW-Authentication' => 'Bearer realm="foo",error="invalid_token"',
                    ]
                );
            }

            return new Response(
                401,
                json_encode(['error' => 'no_token']),
                [
                    'Content-Type' => 'application/json',
                    'WWW-Authentication' => 'Bearer realm="foo"',
                ]
            );
        }

        return new Response(
            404,
            'NOT FOUND',
            [
                'Content-Type' => 'text/plain',
            ]
        );
    }

    private function post($requestUri, array $postData = [], array $requestHeaders = [])
    {
        if ('http://localhost/token' === $requestUri) {
            // interacting with token endpoint
            if ('refresh_token' === $postData['grant_type']) {
                if ('RT:abc' === $postData['refresh_token']) {
                    return new Response(
                        200,
                        json_encode(
                            [
                                'access_token' => 'AT:refreshed',
                                'token_type' => 'bearer',
                                'expires_in' => 3600,
                            ]
                        ),
                        ['Content-Type' => 'application/json']
                    );
                }

                return new Response(
                    400,
                    json_encode(['error' => 'invalid_grant', 'error_description' => 'invalid refresh_token']),
                    ['Content-Type' => 'application/json']
                );
            }

            if ('authorization_code' === $postData['grant_type']) {
                if ('AC:fail' === $postData['code']) {
                    // emulate wrong credentials
                    return new Response(
                        401,
                        json_encode(['error' => 'foo', 'error_description' => 'bar']),
                        [
                            'Content-Type' => 'application/json',
                            'Cache-Control' => 'no-store',
                            'Pragma' => 'no-cache',
                            'WWW-Authenticate' => 'Basic realm="OAuth"',
                        ]
                    );
                }

                if ('AC:broken' === $postData['code']) {
                    return new Response(
                        200,
                        json_encode(
                            [
                                'access_token' => 'AT:code12345',
                                'token_type' => 'bearer',
                                'refresh_token' => 'refresh:x:y:z',
                                'expires_in' => '12345',    // expires_in MUST be int
                            ]
                        ),
                        ['Content-Type' => 'application/json']
                    );
                }

                if ('AC:abc' === $postData['code']) {
                    return new Response(
                        200,
                        json_encode(
                            [
                                'access_token' => 'AT:code12345',
                                'token_type' => 'bearer',
                                'refresh_token' => 'refresh:x:y:z',
                            ]
                        ),
                        ['Content-Type' => 'application/json']
                    );
                }

                return new Response(
                    400,
                    json_encode(['error' => 'invalid_grant', 'error_description' => 'invalid authorization_code']),
                    ['Content-Type' => 'application/json']
                );
            }

            return new Response(
                400,
                json_encode(['error' => 'unsupported_grant_type']),
                ['Content-Type' => 'application/json']
            );
        }

        return new Response(
            404,
            'NOT FOUND',
            [
                'Content-Type' => 'text/plain',
            ]
        );
    }
}
