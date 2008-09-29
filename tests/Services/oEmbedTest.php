<?php

/**
 * The main test for {@link Services_oEmbed}
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

require_once 'PHPUnit/Framework/TestCase.php';
require_once 'Services/oEmbed.php';

/**
 * Test for {@link Services_oEmbed}
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
class Services_oEmbedTest extends PHPUnit_Framework_TestCase
{
    /**
     * Array of test objects to fetch and validate
     *
     * @var array $objects An array of test objects
     */
    protected $objects = array(
        'photo' => array(
            'url' => 'http://flickr.com/photos/joestump/2848795611/',
            'api' => 'http://www.flickr.com/services/oembed/',
            'expected' => array(
                'title' => 'Foo',
                'version' => '1.0',
                'type' => 'photo',
                'title' => 'Smile, Jesus Hates You!',
                'author_name' => 'joestump',
                'author_url' => 'http://www.flickr.com/photos/joestump/',
                'cache_age' => '3600',
                'provider_name' => 'Flickr',
                'provider_url' => 'http://www.flickr.com/',
                'width' => '500',
                'height' => '375',
                'url' => 'http://farm4.static.flickr.com/3066/2848795611_23ce1e6a20.jpg'
            )
        ),
        'video' => array(
            'url' => 'http://www.viddler.com/explore/cdevroe/videos/424/',
            'api' => 'http://lab.viddler.com/services/oembed/',
            'expected' => array(
                'version' => '1.0',
                'type' => 'video',
                'width' => '437',
                'height' => '288',
                'title' => 'iPhone macro lens demonstration',
                'url' => 'http://www.viddler.com/explore/cdevroe/videos/424/',
                'author_name' => 'cdevroe',
                'author_url' => 'http://www.viddler.com/explore/cdevroe/',
                'provider' => 'Viddler',
                'provider_url' => 'http://www.viddler.com/',
                'html' => '<object classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" width="437" height="288" id="viddlerplayer-1646c55"><param name="movie" value="http://www.viddler.com/player/1646c55/" /><param name="allowScriptAccess" value="always" /><param name="wmode" value="transparent" /><param name="allowFullScreen" value="true" /><embed src="http://www.viddler.com/player/1646c55/" width="437" height="288" type="application/x-shockwave-flash" wmode="transparent" allowScriptAccess="always" allowFullScreen="true" name="viddlerplayer-1646c55" ></embed></object>',
            )
        )
    );

    /**
     * An invalid object to test
     *
     * @var array $error An invalid object
     */
    protected $error = array(
        'url' => 'http://flickr.com/photos/joestump/28-1148795611/',
        'api' => 'http://www.flickr.com/services/oembed/'
    );

    /**
     * Test fetching all of the objects
     *
     * @return void
     * @see Services_oEmbedTest::$objects
     */
    public function testGetObjects()
    {
        foreach ($this->objects as $type => $test) {
            $object = $this->getObject($test);

            $expectedObject = 'Services_oEmbed_Object_' . ucfirst($type);
            $this->assertEquals($expectedObject, get_class($object));

            foreach ($test['expected'] as $key => $val) { 
                $this->assertEquals($val, $object->$key, 'Unexpected ' . $key . ' value for object type ' . $type);
            }
        }
    }

    /**
     * Test the error
     *
     * @return void
     */
    public function testError()
    {
        try {
            $object = $this->getObject($this->error);
        } catch (Services_oEmbed_Exception $e) {
            return;
        }

        $this->fail('An expected exception has not been raised.');
    }

    /**
     * Get the oEmbed object
     *
     * @param array $test The test object to fetch
     *
     * @return object Instance of {@link Services_oEmbed_Object}
     */
    protected function getObject(array $test)
    {
        $oEmbed = new Services_oEmbed($test['url'], array(
            Services_oEmbed::OPTION_API => $test['api']
        ));

        return $oEmbed->getObject();
    }
}

?>
