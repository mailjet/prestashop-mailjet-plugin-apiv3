<?php

use PHPUnit\Framework\TestCase;

require_once dirname(__DIR__, 2) . '/classes/hooks/WebhookProcessor.php';

/**
 * DE-11722 (audit #13): the webhook answers 2xx only when every event was
 * applied, so Mailjet retries the call when saving or unsubscribing fails.
 */
class WebhookProcessorTest extends TestCase
{
    public $stored = array();
    public $unsubscribed = array();
    public $storeResult = true;
    public $unsubscribeResult = true;
    public $managedLists = array(10);

    private function processor()
    {
        $test = $this;

        return new HooksWebhookProcessor(
            function (array $event) use ($test) {
                $test->stored[] = $event['event'];

                return $test->storeResult;
            },
            function (array $event) use ($test) {
                $test->unsubscribed[] = $event['email'];

                return $test->unsubscribeResult;
            },
            function ($listId) use ($test) {
                return in_array((int) $listId, $test->managedLists, true);
            }
        );
    }

    public function testAppliedUnsubscribeIsAcknowledged()
    {
        $this->assertSame(array(200, 'Ok'), $this->processor()->process(
            array('event' => 'unsub', 'email' => 'john@example.com', 'mj_list_id' => 10)
        ));
        $this->assertSame(array('john@example.com'), $this->unsubscribed);
    }

    public function testFailedUnsubscribeIsNotAcknowledged()
    {
        $this->unsubscribeResult = false;

        $this->assertSame(500, $this->processor()->process(
            array('event' => 'unsub', 'email' => 'john@example.com')
        )[0]);
    }

    public function testFailedEventSaveIsNotAcknowledged()
    {
        $this->storeResult = false;

        $this->assertSame(500, $this->processor()->process(array(
            array('event' => 'sent', 'email' => 'a@example.com'),
            array('event' => 'bounce', 'email' => 'b@example.com'),
        ))[0]);
    }

    public function testExceptionIsNotAcknowledged()
    {
        $processor = new HooksWebhookProcessor(
            function () {
                throw new Exception('database is gone');
            },
            function () {
                return true;
            },
            function () {
                return true;
            }
        );

        $this->assertSame(500, $processor->process(array('event' => 'spam', 'email' => 'x@example.com'))[0]);
    }

    public function testUnsubscribeFromUnmanagedListIsAcknowledgedAndIgnored()
    {
        $this->assertSame(200, $this->processor()->process(
            array('event' => 'unsub', 'email' => 'john@example.com', 'mj_list_id' => 99)
        )[0]);
        $this->assertSame(array(), $this->unsubscribed);
    }

    public function testBatchIsAcknowledgedWhenEveryEventIsApplied()
    {
        $this->assertSame(array(200, 'Ok'), $this->processor()->process(array(
            array('event' => 'open', 'email' => 'a@example.com'),
            array('event' => 'blocked', 'email' => 'b@example.com'),
            array('event' => 'unsub', 'email' => 'c@example.com'),
        )));
        $this->assertSame(array('blocked'), $this->stored);
        $this->assertSame(array('c@example.com'), $this->unsubscribed);
    }

    public function testMalformedEventIsRejected()
    {
        $this->assertSame(422, $this->processor()->process(array(array('email' => 'a@example.com')))[0]);
    }

    public function testUnknownEventTypeHasNoHandler()
    {
        $this->assertSame(423, $this->processor()->process(array('event' => 'something_new'))[0]);
    }
}
