<?php

use PHPUnit\Framework\TestCase;

require_once dirname(__DIR__, 2) . '/libraries/Mailjet.Overlay.class.php';
require_once dirname(__DIR__, 2) . '/classes/hooks/synchronization/SynchronizationAbstract.php';
require_once dirname(__DIR__, 2) . '/classes/hooks/synchronization/SingleUser.php';
require_once dirname(__DIR__) . '/Support/FakeUnsubscribeApiOverlay.php';

/**
 * DE-11574 (audit #09): a newsletter withdrawal unsubscribes the contact from
 * every managed list tied to the consent, not only from the master list.
 */
class WithdrawTest extends TestCase
{
    /** @var FakeUnsubscribeApiOverlay */
    private $overlay;

    /** @var HooksSynchronizationSingleUser */
    private $sync;

    protected function setUp(): void
    {
        Configuration::$values = array('contact_list' => 10);
        $this->overlay = new FakeUnsubscribeApiOverlay();
        $this->overlay->accountLists = array(
            (object) array('ID' => 10, 'Name' => 'PrestaShop Customers Master List'),
            (object) array('ID' => 11, 'Name' => '1idfloyalcustomers'),
            (object) array('ID' => 12, 'Name' => '2idfnewcustomers'),
            (object) array('ID' => 20, 'Name' => 'Blog readers'),
        );
        $this->sync = new HooksSynchronizationSingleUser($this->overlay);
    }

    protected function tearDown(): void
    {
        Configuration::$values = array();
    }

    public function testWithdrawalUnsubscribesFromMasterAndSegmentLists()
    {
        $this->overlay->customerLists = (object) array('Count' => 3, 'Data' => array(
            (object) array('ListID' => 10, 'IsUnsub' => false),
            (object) array('ListID' => 11, 'IsUnsub' => false),
            (object) array('ListID' => 20, 'IsUnsub' => false),
        ));

        $this->assertTrue($this->sync->withdraw('john@example.com'));
        $this->assertSame(array(
            array('unsub', 'john@example.com', 10),
            array('unsub', 'john@example.com', 11),
        ), $this->overlay->calls);
    }

    public function testMasterListIsAlwaysCoveredForGuests()
    {
        $this->overlay->customerLists = false;

        $this->assertTrue($this->sync->withdraw('guest@example.com'));
        $this->assertSame(array(array('unsub', 'guest@example.com', 10)), $this->overlay->calls);
    }

    public function testMasterListIsNotUnsubscribedTwice()
    {
        $this->overlay->customerLists = (object) array('Count' => 1, 'Data' => array(
            (object) array('ListID' => 10, 'IsUnsub' => false),
        ));

        $this->sync->withdraw('john@example.com');
        $this->assertCount(1, $this->overlay->calls);
    }

    public function testEmptyEmailIsRejected()
    {
        $this->assertFalse($this->sync->withdraw(''));
        $this->assertSame(array(), $this->overlay->calls);
    }
}
