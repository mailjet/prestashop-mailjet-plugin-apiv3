<?php

use PHPUnit\Framework\TestCase;

require_once dirname(__DIR__, 2) . '/libraries/Mailjet.Overlay.class.php';
require_once dirname(__DIR__, 2) . '/classes/hooks/synchronization/SynchronizationAbstract.php';
require_once dirname(__DIR__, 2) . '/classes/hooks/synchronization/SingleUser.php';
require_once dirname(__DIR__) . '/Support/FakeUnsubscribeApiOverlay.php';

/**
 * DE-11736: a customer saved with newsletter = 0 must only be unsubscribed from
 * the lists he is subscribed to, never from every list of the Mailjet account.
 */
class SingleUserUnsubscribeTest extends TestCase
{
    /** @var FakeUnsubscribeApiOverlay */
    private $overlay;

    /** @var HooksSynchronizationSingleUser */
    private $sync;

    protected function setUp(): void
    {
        $this->overlay = new FakeUnsubscribeApiOverlay();
        $this->overlay->accountLists = array((object) array('ID' => 1), (object) array('ID' => 2), (object) array('ID' => 3));
        $this->sync = new HooksSynchronizationSingleUser($this->overlay);
    }

    private static function contactLists(array $data)
    {
        return (object) array('Count' => count($data), 'Data' => $data);
    }

    public function testUnsubscribeWithoutListIdDoesNothing()
    {
        $this->assertFalse($this->sync->unsubscribe('john@example.com'));
        $this->assertFalse($this->sync->unsubscribe('john@example.com', 0));
        $this->assertSame(array(), $this->overlay->calls);
    }

    public function testUnsubscribeTargetsOnlyTheGivenList()
    {
        $this->assertTrue($this->sync->unsubscribe('john@example.com', 2));
        $this->assertSame(array(array('unsub', 'john@example.com', 2)), $this->overlay->calls);
    }

    public function testSubscribedListsReadListIdAndSkipUnsubscribed()
    {
        $this->overlay->customerLists = self::contactLists(array(
            (object) array('ListID' => 10, 'IsUnsub' => false, 'IsActive' => true),
            (object) array('ListID' => 11, 'IsUnsub' => true, 'IsActive' => true),
            (object) array('ListID' => '12', 'IsUnsub' => 'false'),
            (object) array('IsUnsub' => false),
        ));

        $this->assertSame(array(10, 12), $this->sync->getSubscribedSegmentLists('john@example.com'));
    }

    public function testSubscribedListsAcceptLegacyFieldNames()
    {
        $this->overlay->customerLists = self::contactLists(array(
            (object) array('ID' => 20, 'IsUnsubscribed' => false),
            (object) array('ID' => 21, 'IsUnsubscribed' => true),
        ));

        $this->assertSame(array(20), $this->sync->getSubscribedSegmentLists('john@example.com'));
    }

    public function testSubscribedListsIsEmptyArrayWhenContactHasNoList()
    {
        $this->overlay->customerLists = false;

        $this->assertSame(array(), $this->sync->getSubscribedSegmentLists('john@example.com'));
    }

    public function testUnsubscribeFromSubscribedListsTouchesOnlyThoseLists()
    {
        $this->overlay->customerLists = self::contactLists(array(
            (object) array('ListID' => 2, 'IsUnsub' => false),
            (object) array('ListID' => 3, 'IsUnsub' => true),
        ));

        $this->assertTrue($this->sync->unsubscribeFromSubscribedLists('john@example.com'));
        $this->assertSame(array(array('unsub', 'john@example.com', 2)), $this->overlay->calls);
    }

    public function testUnsubscribeFromSubscribedListsWithoutListsSendsNothing()
    {
        $this->assertTrue($this->sync->unsubscribeFromSubscribedLists('john@example.com'));
        $this->assertSame(array(), $this->overlay->calls);
    }
}
