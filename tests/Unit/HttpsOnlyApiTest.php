<?php

use PHPUnit\Framework\TestCase;

require_once dirname(__DIR__, 2) . '/libraries/Mailjet.Exception.class.php';
require_once dirname(__DIR__, 2) . '/libraries/Mailjet.Api.class.php';
require_once dirname(__DIR__, 2) . '/libraries/MailJet.Data.Api.class.php';
require_once dirname(__DIR__, 2) . '/libraries/php-mailjet-v3-simple.class.php';

/**
 * The Mailjet API is HTTPS-only: the bundled API clients must never build a plain HTTP URL.
 */
class HttpsOnlyApiTest extends TestCase
{
    private static function readProperty($object, $name)
    {
        $property = new ReflectionProperty($object, $name);
        $property->setAccessible(true);

        return $property->getValue($object);
    }

    public function testApiUsesHttpsByDefault()
    {
        $api = new Mailjet_Api('key', 'secret');

        $this->assertStringStartsWith('https://api.mailjet.com/', self::readProperty($api, '_apiUrl'));
    }

    public function testApiIgnoresInsecureFlag()
    {
        $api = new Mailjet_Api('key', 'secret');
        $api->secure(false);

        $this->assertStringStartsWith('https://', self::readProperty($api, '_apiUrl'));
        $this->assertTrue(self::readProperty($api, '_secure'));

        $api->resetRequest();
        $this->assertStringStartsWith('https://', self::readProperty($api, '_apiUrl'));
    }

    public function testDataApiUsesHttpsEvenWhenInsecureRequested()
    {
        $data = new mailjetdata('key', 'secret', false);

        $this->assertStringStartsWith('https://api.mailjet.com/', self::readProperty($data, '_api_url'));

        $data->secure(false);
        $this->assertStringStartsWith('https://', self::readProperty($data, '_api_url'));
    }

    public function testPhpWrapperUsesHttps()
    {
        $wrapper = new MailjetPHPWrapper('key', 'secret');
        $wrapper->secure = false;

        $method = new ReflectionMethod($wrapper, 'getApiUrl');
        $method->setAccessible(true);

        $this->assertSame('https://api.mailjet.com/v3', $method->invoke($wrapper, false));
        $this->assertStringStartsWith('https://', $method->invoke($wrapper, true));
    }
}
