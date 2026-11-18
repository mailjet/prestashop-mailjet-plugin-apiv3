<?php

use PHPUnit\Framework\TestCase;

require_once dirname(__DIR__, 2) . '/classes/hooks/Events.php';

class FakeEventsCustomer
{
    public $id;
    public $newsletter = 1;
    public $updateResult = true;
    public $updated = 0;

    public function update()
    {
        $this->updated++;

        return $this->updateResult;
    }
}

/**
 * Replaces the database access of HooksEvents
 */
class TestableHooksEvents extends HooksEvents
{
    /** @var FakeEventsCustomer[] by id */
    public $customers = array();
    public $deletedSubscriptions = array();
    public $deleteResult = true;

    protected function findCustomerIds($email)
    {
        return array_keys($this->customers);
    }

    protected function loadCustomer($idCustomer)
    {
        return $this->customers[$idCustomer];
    }

    protected function deleteNewsletterSubscription($email)
    {
        $this->deletedSubscriptions[] = $email;

        return $this->deleteResult;
    }
}

/**
 * DE-11535 (audit #04): an unsubscribe from Mailjet must also be applied to a
 * newsletter subscriber who has no customer account.
 */
class EventsUnsubscribeTest extends TestCase
{
    private static function customer($id, $newsletter = 1)
    {
        $customer = new FakeEventsCustomer();
        $customer->id = $id;
        $customer->newsletter = $newsletter;

        return $customer;
    }

    public function testGuestSubscriberWithoutAccountIsUnsubscribed()
    {
        $events = new TestableHooksEvents();

        $this->assertTrue($events->unsubscribe(array('email' => 'guest@example.com')));
        $this->assertSame(array('guest@example.com'), $events->deletedSubscriptions);
    }

    public function testCustomerAndNewsletterRegistrationAreBothUpdated()
    {
        $events = new TestableHooksEvents();
        $events->customers = array(3 => self::customer(3));

        $this->assertTrue($events->unsubscribe(array('email' => 'john@example.com')));
        $this->assertSame(0, $events->customers[3]->newsletter);
        $this->assertSame(1, $events->customers[3]->updated);
        $this->assertSame(array('john@example.com'), $events->deletedSubscriptions);
    }

    public function testEveryAccountWithTheEmailIsUnsubscribed()
    {
        $events = new TestableHooksEvents();
        $events->customers = array(3 => self::customer(3), 4 => self::customer(4), 5 => self::customer(5, 0));

        $this->assertTrue($events->unsubscribe(array('email' => 'john@example.com')));
        $this->assertSame(0, $events->customers[3]->newsletter);
        $this->assertSame(0, $events->customers[4]->newsletter);
        $this->assertSame(0, $events->customers[5]->updated, 'already unsubscribed: not saved again');
    }

    public function testFailedSaveIsReported()
    {
        $events = new TestableHooksEvents();
        $events->customers = array(3 => self::customer(3));
        $events->customers[3]->updateResult = false;

        $this->assertFalse($events->unsubscribe(array('email' => 'john@example.com')));
        $this->assertSame(array('john@example.com'), $events->deletedSubscriptions, 'guest row still removed');
    }

    public function testFailedNewsletterDeletionIsReported()
    {
        $events = new TestableHooksEvents();
        $events->deleteResult = false;

        $this->assertFalse($events->unsubscribe(array('email' => 'guest@example.com')));
    }

    public function testEventWithoutEmailIsRejected()
    {
        $events = new TestableHooksEvents();

        $this->assertFalse($events->unsubscribe(array()));
        $this->assertFalse($events->unsubscribe(array('email' => '')));
        $this->assertSame(array(), $events->deletedSubscriptions);
    }
}
