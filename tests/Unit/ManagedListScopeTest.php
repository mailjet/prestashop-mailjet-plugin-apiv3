<?php

use PHPUnit\Framework\TestCase;

require_once dirname(__DIR__, 2) . '/libraries/Mailjet.Overlay.class.php';
require_once dirname(__DIR__, 2) . '/classes/hooks/synchronization/SynchronizationAbstract.php';
require_once dirname(__DIR__, 2) . '/classes/hooks/synchronization/SingleUser.php';
require_once dirname(__DIR__) . '/Support/FakeUnsubscribeApiOverlay.php';

/**
 * DE-11542 (audit #05): unsubscribes and removals only touch the lists the
 * shop manages, never other lists on the same Mailjet API key.
 */
class ManagedListScopeTest extends TestCase
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
            (object) array('ID' => 12, 'Name' => '27idfnewcustomers'),
            (object) array('ID' => 20, 'Name' => 'Blog readers'),
            (object) array('ID' => 21, 'Name' => 'idf without filter id'),
        );
        $this->sync = new HooksSynchronizationSingleUser($this->overlay);
    }

    protected function tearDown(): void
    {
        Configuration::$values = array();
    }

    private function listIdsCalled()
    {
        return array_map(function ($call) {
            return $call[2];
        }, $this->overlay->calls);
    }

    public function testManagedListsAreMasterAndSegmentLists()
    {
        $this->assertSame(array(10, 11, 12), $this->sync->getManagedListIds());
        $this->assertSame(array(11, 12), $this->sync->getManagedListIds(false));
        $this->assertTrue($this->sync->isManagedList('11'));
        $this->assertFalse($this->sync->isManagedList(20));
        $this->assertFalse($this->sync->isManagedList(null));
    }

    public function testRemoveFromAllListsKeepsUnrelatedLists()
    {
        $this->assertTrue($this->sync->removeFromAllLists('john@example.com'));
        $this->assertSame(array(10, 11, 12), $this->listIdsCalled());
    }

    public function testRemoveWithoutListIdOnlyTouchesManagedSegmentLists()
    {
        $this->assertTrue($this->sync->remove('john@example.com'));
        $this->assertSame(array(11, 12), $this->listIdsCalled());
    }

    public function testUnsubscribeListsExceptMasterOnlyTouchesManagedSegmentLists()
    {
        $this->assertTrue($this->sync->unsubscribeListsExceptMaster('john@example.com'));
        $this->assertSame(array(11, 12), $this->listIdsCalled());
    }

    public function testWithdrawalSkipsSubscribedListsNotManagedByTheShop()
    {
        $this->overlay->customerLists = (object) array('Count' => 3, 'Data' => array(
            (object) array('ListID' => 10, 'IsUnsub' => false),
            (object) array('ListID' => 20, 'IsUnsub' => false),
            (object) array('ListID' => 12, 'IsUnsub' => false),
        ));

        $this->assertTrue($this->sync->unsubscribeFromSubscribedLists('john@example.com'));
        $this->assertSame(array(10, 12), $this->listIdsCalled());
    }
}
