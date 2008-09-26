<?php

/**
 * An interface for oEmbed consumption
 *
 * PHP version 5.1.0+
 *
 * Copyright (c) 2008, Digg.com, Inc.
 * 
 * All rights reserved.
 * 
 * Redistribution and use in source and binary forms, with or without 
 * modification, are permitted provided that the following conditions are met:
 *
 *  - Redistributions of source code must retain the above copyright notice,
 *    this list of conditions and the following disclaimer.
 *  - Redistributions in binary form must reproduce the above copyright notice,
 *    this list of conditions and the following disclaimer in the documentation
 *    and/or other materials provided with the distribution.
 *  - Neither the name of Digg.com, Inc. nor the names of its contributors 
 *    may be used to endorse or promote products derived from this software 
 *    without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" 
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE 
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE 
 * ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE 
 * LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR 
 * CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF 
 * SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS 
 * INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN 
 * CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) 
 * ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE 
 * POSSIBILITY OF SUCH DAMAGE.
 *
 * @category  Services
 * @package   Services_oEmbed
 * @author    Joe Stump <joe@joestump.net> 
 * @copyright 2008 Digg.com, Inc.
 * @license   http://tinyurl.com/42zef New BSD License
 * @version   SVN: @version@
 * @link      http://code.google.com/p/digg
 * @link      http://oembed.com
 */

require_once 'Validate.php';
require_once 'Net/URL2.php';
require_once 'HTTP/Request.php';
require_once 'Services/oEmbed/Exception.php';
require_once 'Services/oEmbed/Exception/NoSupport.php';
require_once 'Services/oEmbed/Object.php';

/**
 * Base class for consuming oEmbed objects
 *
 * <code>
 * <?php
 * 
 * require_once 'Services/oEmbed.php';
 *
 * // The URL that we'd like to find out more information about.
 * $url = 'http://flickr.com/photos/joestump/2848795611/';
 *
 * // The oEmbed API URI. Not all providers support discovery yet so we're
 * // explicitly providing one here. If one is not provided Services_oEmbed
 * // attempts to discover it. If none is found an exception is thrown.
 * $api = 'http://www.flickr.com/services/oembed/';
 *
 * $oEmbed = new Services_oEmbed($url, $api);
 * $object = $oEmbed->getObject();
 *
 * // All of the objects have somewhat sane __toString() methods that allow
 * // you to output them directly.
 * echo (string)$object;
 * 
 * ?>
 * </code> 
 * 
 * @category  Services
 * @package   Services_oEmbed
 * @author    Joe Stump <joe@joestump.net> 
 * @copyright 2008 Digg.com, Inc.
 * @license   http://tinyurl.com/42zef New BSD License
 * @version   Release: @version@
 * @link      http://code.google.com/p/digg
 * @link      http://oembed.com
 */
class Services_oEmbed
{
    /**
     * URL of object to get embed information for
     *
     * @var object $url {@link Net_URL2} instance of URL of object
     */
    protected $url = null;

    /**
     * oEmbed API endpoint
     *
     * @var string $api API URI 
     */
    protected $api = null;

    /**
     * Constructor
     *
     * @param string $url The URL to fetch an oEmbed for
     * @param string $api The oEmbed API URL for the $url provided
     *
     * @throws {@link Services_oEmbed_Exception} if the $url is invalid
     * @throws {@link Services_oEmbed_Exception} when no valid API is found
     * @return void
     */
    public function __construct($url, $api = null)
    {
        if (Validate::uri($url)) {
            $this->url = new Net_URL2($url);
        } else {
            throw new Services_oEmbed_Exception('URL is invalid');
        }

        if ($api === null) {
            $this->api = $this->discover();
        } elseif ($api !== null && Validate::uri($api)) {
            $this->api = $api;
        } else {
            throw new Services_oEmbed_Exception_NoSupport(
                'No valid API found/provided'
            );
        }
    }

    /**
     * Get the oEmbed response
     *
     * @param array $params Optional parameters for 
     * 
     * @throws {@link Services_oEmbed_Exception} on cURL errors
     * @throws {@link Services_oEmbed_Exception} on HTTP errors
     * @throws {@link Services_oEmbed_Exception} when result is not parsable 
     * @return object The oEmbed response as an object
     */
    public function getObject(array $params = array())
    {
        $params['url'] = $this->url->getURL();
        if (!isset($params['format'])) {
            $params['format'] = 'json';
        }

        $sets = array();
        foreach ($params as $var => $val) {
            $sets[] = $var . '=' . urlencode($val);
        }

        $url = $this->api . '?' . implode('&', $sets);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        $result = curl_exec($ch);

        if (curl_errno($ch)) {
            throw new Services_oEmbed_Exception(
                curl_error($ch), curl_errno($ch)
            );
        }

        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if (substr($code, 0, 1) != '2') {
            throw new Services_oEmbed_Exception('Non-200 code returned');
        }

        curl_close($ch);

        switch ($params['format']) {
        case 'json':
            $res = json_decode($result);
            if (!is_object($res)) {
                throw new Services_oEmbed_Exception(
                    'Could not parse JSON response'
                );
            }
            break;
        case 'xml':
            libxml_use_internal_errors(true);
            $res = simplexml_load_string($result);
            if (!$res instanceof SimpleXMLElement) {
                $errors = libxml_get_errors();
                $err    = array_shift($errors);
                libxml_clear_errors();
                libxml_use_internal_errors(false);
                throw new Services_oEmbed_Exception(
                    $err->message, $error->code
                );
            }
            break;
        }

        return Services_oEmbed_Object::factory($res);
    }

    /**
     * Discover an oEmbed API 
     *
     * @param string $url The URL to attempt to discover oEmbed for
     *
     * @throws {@link Services_oEmbed_Exception} if the $url is invalid
     * @return string The oEmbed API endpoint discovered
     */
    protected function discover($url)
    {
        $req = new HTTP_Request($url, array(
            'method' => 'GET',
            'timeout' => 2,
            'maxRedirects' => 100,
            'readTimeout' => 5
        ));

        $req->addHeader('User-Agent', 'Services_oEmbed @package-version@');

        $check = $req->sendRequest();
        if (PEAR::isError($check)) {
            throw new Services_oEmbed_Exception($check->getMessage());
        }

        $body = $req->getResponseBody();

        // Find all <link /> tags that have a valid oembed type set. We then
        // extract the href attribute for each type.
        $regexp = '#<link([^>]*)type="' . 
                  '(application/json|text/xml)\+oembed"([^>]*)>#i';

        $m = $ret = array();
        if (!preg_match_all($regexp, $body, $m)) {
            throw new Services_oEmbed_Exception_NoSupport(
                'No valid oEmbed links found on page'
            );
        }

        foreach ($m[0] as $i => $link) {
            $h = array();
            if (preg_match('/href="([^"]+)"/i', $link, $h)) {
                $ret[$m[2][$i]] = $h[1];
            }
        } 

        return (isset($ret['json']) ? $ret['json'] : array_pop($ret));
    }
}

?>
