<?php

use PHPUnit\Framework\TestCase;

require_once dirname(__DIR__, 2) . '/libraries/Mailjet.Overlay.class.php';
require_once dirname(__DIR__, 2) . '/classes/hooks/synchronization/SynchronizationAbstract.php';
require_once dirname(__DIR__, 2) . '/classes/hooks/synchronization/SingleUser.php';
require_once dirname(__DIR__) . '/Support/FakeUnsubscribeApiOverlay.php';

/**
 * DE-11568 (audit #07): every deletion workflow removes the contact from all
 * managed lists, master list included.
 */
class CustomerDeletionTest extends TestCase
{
    protected function tearDown(): void
    {
        Configuration::$values = array();
    }

    public function testEraseRemovesFromMasterAndSegmentListsOnly()
    {
        Configuration::$values = array('contact_list' => 10);
        $overlay = new FakeUnsubscribeApiOverlay();
        $overlay->accountLists = array(
            (object) array('ID' => 10, 'Name' => 'PrestaShop Customers Master List'),
            (object) array('ID' => 11, 'Name' => '1idfloyalcustomers'),
            (object) array('ID' => 20, 'Name' => 'Blog readers'),
        );
        $sync = new HooksSynchronizationSingleUser($overlay);

        $this->assertTrue($sync->erase('john@example.com'));
        $this->assertSame(array(
            array('remove', 'john@example.com', 10),
            array('remove', 'john@example.com', 11),
        ), $overlay->calls);
    }

    public function testEraseWithoutEmailDoesNothing()
    {
        $overlay = new FakeUnsubscribeApiOverlay();
        $sync = new HooksSynchronizationSingleUser($overlay);

        $this->assertFalse($sync->erase(''));
        $this->assertSame(array(), $overlay->calls);
    }

    public function testEveryDeletionHookUsesTheSameErasure()
    {
        $source = file_get_contents(dirname(__DIR__, 2) . '/mailjet.php');
        $method = function ($name) use ($source) {
            $start = strpos($source, 'public function ' . $name . '(');
            $end = strpos($source, "\n    }\n", $start);

            return substr($source, $start, $end - $start);
        };

        $this->assertStringContainsString('eraseCustomerEmail(', $method('hookActionObjectCustomerDeleteAfter'));
        $this->assertStringNotContainsString('->remove(', $method('hookActionObjectCustomerDeleteBefore'));
        $this->assertStringNotContainsString('removeFromAllLists', $method('hookActionAdminCustomersControllerDeleteBefore'));
        $this->assertStringContainsString('eraseCustomerEmail(', $method('hookActionObjectCustomerUpdateAfter'));
    }
}
