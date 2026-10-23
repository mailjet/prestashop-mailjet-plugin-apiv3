<?php

use PHPUnit\Framework\TestCase;

class MailjetTrackingConsentApiClientTest extends TestCase
{
    public function testPropertiesCreationToleratesExistingProperties()
    {
        $transport = new FakeMailjetTransport();
        $transport->responder = function () {
            return [400, null];
        };
        $client = new MailjetTrackingConsentApiClient('key', 'secret', $transport);

        $this->assertTrue($client->ensureProperties('ps_tracking_consent'));
        $this->assertSame(
            [
                ['Datatype' => 'bool', 'Name' => 'ps_tracking_consent', 'NameSpace' => 'static'],
                ['Datatype' => 'datetime', 'Name' => 'ps_tracking_consent_date', 'NameSpace' => 'static'],
            ],
            array_column($transport->requests, 2)
        );
    }

    public function testPropertiesCreationFailsOnServerError()
    {
        $transport = new FakeMailjetTransport();
        $transport->responder = function () {
            return [401, null];
        };
        $client = new MailjetTrackingConsentApiClient('key', 'secret', $transport);

        $this->assertFalse($client->ensureProperties('ps_tracking_consent'));
    }

    public function testUpdateContactConsentPayload()
    {
        $transport = new FakeMailjetTransport();
        $client = new MailjetTrackingConsentApiClient('key', 'secret', $transport);

        $this->assertTrue($client->updateContactConsent('ps_tracking_consent', 'a+b@example.com', false, '2026-10-23 10:00:00'));

        list($method, $url, $body) = $transport->requests[0];
        $this->assertSame('PUT', $method);
        $this->assertSame(MailjetTrackingConsentApiClient::API_URL . 'contactdata/a%2Bb%40example.com', $url);
        $this->assertSame(['Name' => 'ps_tracking_consent', 'Value' => false], $body['Data'][0]);
        $this->assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}Z$/', $body['Data'][1]['Value']);
    }

    /**
     * @dataProvider boolValues
     */
    public function testToBool($value, $expected)
    {
        $this->assertSame($expected, MailjetTrackingConsentApiClient::toBool($value));
    }

    public function boolValues()
    {
        return [
            [true, true],
            [false, false],
            ['true', true],
            ['True', true],
            ['1', true],
            ['false', false],
            ['', false],
            ['0', false],
        ];
    }

    public function testFetchConsentsSkipsContactLookupWhenNoConsentIsSet()
    {
        $transport = new FakeMailjetTransport();
        $client = new MailjetTrackingConsentApiClient('key', 'secret', $transport);

        $this->assertSame([], $client->fetchConsents('ps_tracking_consent'));
        $this->assertCount(1, $transport->requests);
    }
}
