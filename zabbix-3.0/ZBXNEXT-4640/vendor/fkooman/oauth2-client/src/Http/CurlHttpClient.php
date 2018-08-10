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

namespace fkooman\OAuth\Client\Http;

use fkooman\OAuth\Client\Http\Exception\CurlException;

class CurlHttpClient implements HttpClientInterface
{
    /** @var resource */
    private $curlChannel;

    /** @var bool */
    private $allowHttp = false;

    /** @var array */
    private $responseHeaderList = [];

    /**
     * @param array $configData
     */
    public function __construct(array $configData = [])
    {
        if (array_key_exists('allowHttp', $configData)) {
            $this->allowHttp = (bool) $configData['allowHttp'];
        }
        $this->curlInit();
    }

    public function __destruct()
    {
        if ($this->curlChannel != false)
            curl_close($this->curlChannel);
    }

    /**
     * @param Request $request
     *
     * @return Response
     */
    public function send(Request $request)
    {
        $curlOptions = [
            CURLOPT_CUSTOMREQUEST => $request->getMethod(),
            CURLOPT_URL => $request->getUri(),
        ];
        $curlOptions[CURLOPT_SSL_VERIFYPEER] = False;
        if (in_array($request->getMethod(), ['POST', 'PUT', 'PATCH'], true)) {
            $curlOptions[CURLOPT_POSTFIELDS] = $request->getBody();
        }

        return $this->exec($curlOptions, $request->getHeaders());
    }

    /**
     * @return void
     */
    private function curlInit()
    {
        if (false === $curlChannel = curl_init()) {
            throw new CurlException('unable to create cURL channel');
        }
        $this->curlChannel = $curlChannel;
    }

    /**
     * @return void
     */
    private function curlReset()
    {
        if (function_exists('curl_reset')) {
            curl_reset($this->curlChannel);
        } else {
            if ($this->curlChannel != false)
                curl_close($this->curlChannel);
            $this->curlInit();
        }
        $this->responseHeaderList = [];
    }

    /**
     * @param array $curlOptions
     * @param array $requestHeaders
     *
     * @return Response
     */
    private function exec(array $curlOptions, array $requestHeaders)
    {
        $this->curlReset();

        $defaultCurlOptions = [
            CURLOPT_HEADER => false,
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [],
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_PROTOCOLS => $this->allowHttp ? CURLPROTO_HTTPS | CURLPROTO_HTTP : CURLPROTO_HTTPS,
            CURLOPT_HEADERFUNCTION => [$this, 'responseHeaderFunction'],
        ];

        if (0 !== count($requestHeaders)) {
            $curlRequestHeaders = [];
            foreach ($requestHeaders as $k => $v) {
                $curlRequestHeaders[] = sprintf('%s: %s', $k, $v);
            }
            $defaultCurlOptions[CURLOPT_HTTPHEADER] = $curlRequestHeaders;
        }

        if (false === curl_setopt_array($this->curlChannel, $curlOptions + $defaultCurlOptions)) {
            throw new CurlException('unable to set cURL options');
        }

        if (false === $responseData = curl_exec($this->curlChannel)) {
            throw new CurlException(
                sprintf(
                    '[%d] %s',
                    curl_errno($this->curlChannel),
                    curl_error($this->curlChannel)
                )
            );
        }

        return new Response(
            curl_getinfo($this->curlChannel, CURLINFO_HTTP_CODE),
            // Psalm false positive (bool) for $responseData as we use
            // CURLOPT_RETURNTRANSFER
            $responseData,
            $this->responseHeaderList
        );
    }

    /**
     * @param resource $curlChannel
     * @param string   $headerData
     *
     * @return int
     */
    private function responseHeaderFunction($curlChannel, $headerData)
    {
        if (false !== strpos($headerData, ':')) {
            list($key, $value) = explode(':', $headerData, 2);
            $this->responseHeaderList[trim($key)] = trim($value);
        }

        return strlen($headerData);
    }
}
