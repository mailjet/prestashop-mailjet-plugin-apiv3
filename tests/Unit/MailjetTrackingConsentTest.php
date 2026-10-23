<?php

use PHPUnit\Framework\TestCase;

class MailjetTrackingConsentTest extends TestCase
{
    /** @var InMemoryTrackingConsentStorage */
    private $storage;

    /** @var FakeMailjetTransport */
    private $transport;

    /** @var string */
    private $now;

    protected function setUp(): void
    {
        Configuration::$values = [];
        $this->storage = new InMemoryTrackingConsentStorage();
        $this->storage->customers = [1 => 'john@example.com', 2 => 'jane@example.com'];
        $this->transport = new FakeMailjetTransport();
        $this->now = '2026-10-23 10:00:00';
    }

    private function createService($enabled = true)
    {
        $client = new MailjetTrackingConsentApiClient('key', 'secret', $this->transport);
        $now = &$this->now;

        return new MailjetTrackingConsent($this->storage, $client, $enabled, 'ps_tracking_consent', function () use (&$now) {
            return $now;
        });
    }

    /**
     * @return array[] bodies of the contactdata PUT requests
     */
    private function pushedConsents()
    {
        return array_map(function ($request) {
            return [$request[1], $request[2]['Data'][0]['Value']];
        }, $this->transport->requestsFor('PUT'));
    }

    public function testNoStoredValueMeansNoConsentAndNoTracking()
    {
        $service = $this->createService();

        $this->assertFalse($service->hasConsent(1));
        $this->assertFalse($service->hasConsentForEmail('john@example.com'));
        $this->assertFalse($service->hasConsentForEmail('unknown@example.com'));
        $this->assertTrue($service->shouldDisableTracking(['john@example.com']));
    }

    public function testGivingConsentIsStoredWithDateAndPushedToMailjet()
    {
        $service = $this->createService();

        $this->assertTrue($service->setConsentFromForm(1, 'john@example.com', '1'));

        $status = $service->getStatus(1);
        $this->assertTrue($status['consent']);
        $this->assertSame('2026-10-23 10:00:00', $status['date_upd']);
        $this->assertSame(MailjetTrackingConsent::SOURCE_FRONT, $status['source']);
        $this->assertTrue($status['synced']);
        $this->assertFalse($service->shouldDisableTracking(['john@example.com']));

        $this->assertSame(
            [[MailjetTrackingConsentApiClient::API_URL . 'contactdata/john%40example.com', true]],
            $this->pushedConsents()
        );
        $put = $this->transport->requestsFor('PUT')[0][2];
        $this->assertSame('ps_tracking_consent_date', $put['Data'][1]['Name']);
    }

    public function testWithdrawalIsStoredInHistoryAndPushedToMailjet()
    {
        $service = $this->createService();
        $service->setConsentFromForm(1, 'john@example.com', '1');

        $this->now = '2026-10-24 08:30:00';
        // Unchecked checkbox: the field is not posted
        $this->assertTrue($service->setConsentFromForm(1, 'john@example.com', false));

        $this->assertFalse($service->hasConsent(1));
        $this->assertTrue($service->shouldDisableTracking(['john@example.com']));
        $this->assertSame('2026-10-24 08:30:00', $service->getStatus(1)['date_upd']);

        $history = $service->getHistory(1);
        $this->assertCount(2, $history);
        $this->assertFalse($history[0]['consent']);
        $this->assertSame('2026-10-24 08:30:00', $history[0]['date_add']);
        $this->assertTrue($history[1]['consent']);

        $pushed = $this->pushedConsents();
        $this->assertSame(false, end($pushed)[1]);
    }

    public function testRefusalAtRegistrationIsRecordedForAudit()
    {
        $service = $this->createService();

        $this->assertTrue($service->setConsentFromForm(1, 'john@example.com', null));

        $this->assertFalse($service->getStatus(1)['consent']);
        $this->assertCount(1, $service->getHistory(1));
    }

    public function testUnchangedValueIsNotRecordedTwice()
    {
        $service = $this->createService();
        $service->setConsentFromForm(1, 'john@example.com', '1');

        $this->assertFalse($service->setConsentFromForm(1, 'john@example.com', '1'));
        $this->assertCount(1, $service->getHistory(1));
        $this->assertCount(1, $this->transport->requestsFor('PUT'));
    }

    public function testConsentIsPerCustomerAndDoesNotLeak()
    {
        $service = $this->createService();
        $service->setConsentFromForm(1, 'john@example.com', '1');

        $this->assertFalse($service->hasConsent(2));
        $this->assertTrue($service->shouldDisableTracking(['jane@example.com']));
    }

    public function testDisabledFeatureIgnoresFormAndKeepsTracking()
    {
        $service = $this->createService(false);

        $this->assertFalse($service->setConsentFromForm(1, 'john@example.com', '1'));
        $this->assertNull($service->getStatus(1));
        $this->assertFalse($service->shouldDisableTracking(['john@example.com']));
        $this->assertSame([], $service->getContactProperties(1));
    }

    public function testFailedPushIsRetried()
    {
        $this->transport->responder = function ($method) {
            return $method === 'PUT' ? [500, null] : null;
        };
        $service = $this->createService();
        $service->setConsentFromForm(1, 'john@example.com', '1');

        $this->assertFalse($service->getStatus(1)['synced']);

        $this->transport->responder = null;
        $this->assertSame(1, $service->pushPending());
        $this->assertTrue($service->getStatus(1)['synced']);
    }

    public function testMissingMailjetContactIsCreatedBeforePush()
    {
        $puts = 0;
        $this->transport->responder = function ($method) use (&$puts) {
            if ($method === 'PUT') {
                return ++$puts === 1 ? [404, null] : [200, null];
            }

            return null;
        };
        $service = $this->createService();
        $service->setConsentFromForm(1, 'john@example.com', '1');

        $contactCreation = array_values(array_filter($this->transport->requestsFor('POST'), function ($request) {
            return substr($request[1], -strlen('/contact')) === '/contact';
        }));
        $this->assertSame(['Email' => 'john@example.com'], $contactCreation[0][2]);
        $this->assertTrue($service->getStatus(1)['synced']);
    }

    public function testTrackingHeadersAddedToSwiftMessageWithoutConsent()
    {
        $service = $this->createService();
        $message = new FakeSwiftMessage(['john@example.com' => 'John']);

        $this->assertTrue($service->applyToMessage($message));
        $this->assertSame(
            ['X-Mailjet-TrackOpen' => '0', 'X-Mailjet-TrackClick' => '0'],
            $message->getHeaders()->headers
        );
    }

    public function testNoTrackingHeadersWhenRecipientConsented()
    {
        $service = $this->createService();
        $service->setConsentFromForm(1, 'john@example.com', '1');
        $message = new FakeSwiftMessage(['john@example.com' => 'John']);

        $this->assertFalse($service->applyToMessage($message));
        $this->assertSame([], $message->getHeaders()->headers);
    }

    public function testTrackingDisabledWhenAnyRecipientDidNotConsent()
    {
        $service = $this->createService();
        $service->setConsentFromForm(1, 'john@example.com', '1');
        $message = new FakeSwiftMessage(['john@example.com' => 'John'], ['jane@example.com' => null]);

        $this->assertTrue($service->applyToMessage($message));
    }

    public function testTrackingHeadersAddedToSymfonyEmailAfterWithdrawal()
    {
        $service = $this->createService();
        $service->setConsentFromForm(1, 'john@example.com', '1');
        $service->setConsentFromForm(1, 'john@example.com', false);
        $message = new FakeSymfonyEmail(['john@example.com']);

        $this->assertTrue($service->applyToMessage($message));
        $this->assertSame('0', $message->getHeaders()->headers['X-Mailjet-TrackClick']);
    }

    public function testConsentGivenInMailjetIsImported()
    {
        $service = $this->createService();

        $this->assertSame(1, $service->applyRemoteConsent('john@example.com', true));

        $status = $service->getStatus(1);
        $this->assertTrue($status['consent']);
        $this->assertSame(MailjetTrackingConsent::SOURCE_MAILJET, $status['source']);
        $this->assertTrue($status['synced']);
        // Value came from Mailjet, it is not pushed back
        $this->assertSame([], $this->transport->requestsFor('PUT'));
    }

    public function testWithdrawalInMailjetIsImported()
    {
        $service = $this->createService();
        $service->setConsentFromForm(1, 'john@example.com', '1');

        $this->assertSame(1, $service->applyRemoteConsent('john@example.com', false));
        $this->assertFalse($service->hasConsent(1));
        $this->assertSame(MailjetTrackingConsent::SOURCE_MAILJET, $service->getHistory(1)[0]['source']);
    }

    public function testRemoteNoConsentWithoutLocalValueCreatesNothing()
    {
        $service = $this->createService();

        $this->assertSame(0, $service->applyRemoteConsent('john@example.com', false));
        $this->assertNull($service->getStatus(1));
    }

    public function testLocalChangeNotYetPushedWinsOverMailjet()
    {
        $this->transport->responder = function ($method) {
            return $method === 'PUT' ? [500, null] : null;
        };
        $service = $this->createService();
        $service->setConsentFromForm(1, 'john@example.com', false);

        $this->assertSame(0, $service->applyRemoteConsent('john@example.com', true));
        $this->assertFalse($service->hasConsent(1));
    }

    public function testSynchronizeFromMailjetPullsRemoteChanges()
    {
        $this->transport->responder = function ($method, $url) {
            if ($method === 'GET' && strpos($url, 'contactdata?') !== false && strpos($url, 'Offset=0') !== false) {
                return [200, json_decode('{"Count":2,"Data":[
                    {"ContactID":11,"Data":[{"Name":"ps_tracking_consent","Value":"true"}]},
                    {"ContactID":12,"Data":[{"Name":"firstname","Value":"Jane"}]}
                ]}')];
            }
            if ($method === 'GET' && strpos($url, 'contact?') !== false && strpos($url, 'Offset=0') !== false) {
                return [200, json_decode('{"Count":2,"Data":[
                    {"ID":11,"Email":"John@Example.com"},
                    {"ID":12,"Email":"jane@example.com"}
                ]}')];
            }

            return null;
        };
        $service = $this->createService();

        $result = $service->synchronizeFromMailjet();

        $this->assertSame(1, $result['updated']);
        $this->assertTrue($service->hasConsent(1));
        $this->assertFalse($service->hasConsent(2));
    }

    public function testBulkSyncPropertiesOnlyForCustomersWithStoredValue()
    {
        $service = $this->createService();
        $service->setConsentFromForm(1, 'john@example.com', '1');

        $this->assertSame([], $service->getContactProperties(2));
        $this->assertSame(
            ['ps_tracking_consent' => true, 'ps_tracking_consent_date' => gmdate('Y-m-d\TH:i:s\Z', strtotime($this->now))],
            $service->getContactProperties(1)
        );
    }

    public function testEmailChangeMovesConsent()
    {
        $service = $this->createService();
        $service->setConsentFromForm(1, 'john@example.com', '1');

        $this->assertTrue($service->updateEmail(1, 'john.new@example.com'));
        $this->assertTrue($service->hasConsentForEmail('john.new@example.com'));
        $this->assertFalse($service->updateEmail(1, 'john.new@example.com'));
    }
}
