<?php

use PHPUnit\Framework\TestCase;

require_once dirname(__DIR__, 2) . '/libraries/Mailjet.Overlay.class.php';
require_once dirname(__DIR__, 2) . '/classes/hooks/synchronization/SynchronizationAbstract.php';
require_once dirname(__DIR__, 2) . '/classes/hooks/synchronization/SingleUser.php';
require_once dirname(__DIR__) . '/Support/FakeUnsubscribeApiOverlay.php';


/**
 * DE-11517 (audit #02): routine updates must not resubscribe a contact who
 * unsubscribed in Mailjet. addforce is only used for an explicit opt-in.
 */
class SubscribeActionTest extends TestCase
{
    /** @var FakeUnsubscribeApiOverlay */
    private $overlay;

    /** @var HooksSynchronizationSingleUser */
    private $sync;

    protected function setUp(): void
    {
        $this->overlay = new FakeUnsubscribeApiOverlay();
        $this->sync = new HooksSynchronizationSingleUser($this->overlay);
    }

    public function testRoutineSubscribeByEmailUsesAddnoforce()
    {
        $this->assertTrue($this->sync->subscribe('john@example.com', 5));
        $this->assertSame(array(array('addnoforce', 'john@example.com', 5)), $this->overlay->calls);
    }

    public function testExplicitOptInUsesAddforce()
    {
        $this->assertTrue($this->sync->subscribe('john@example.com', 5, true));
        $this->assertSame(array(array('addforce', 'john@example.com', 5)), $this->overlay->calls);
    }

    public function testAddToListNeverForces()
    {
        $customer = (object) array('id' => 0, 'email' => 'john@example.com', 'firstname' => 'J', 'lastname' => 'D', 'newsletter' => 1);
        $this->assertTrue($this->sync->addToList($customer, 7));
        $this->assertSame(array(array('addnoforce', 'john@example.com', 7)), $this->overlay->calls);
    }

    public function testNewsletterChangingFromZeroToOneIsExplicit()
    {
        $this->assertTrue(HooksSynchronizationSingleUser::isExplicitOptIn(0, (object) array('newsletter' => 1)));
        $this->assertTrue(HooksSynchronizationSingleUser::isExplicitOptIn('0', (object) array('newsletter' => '1')));
    }

    public function testUnchangedOrUnknownNewsletterIsRoutine()
    {
        $this->assertFalse(HooksSynchronizationSingleUser::isExplicitOptIn(1, (object) array('newsletter' => 1)));
        $this->assertFalse(HooksSynchronizationSingleUser::isExplicitOptIn(null, (object) array('newsletter' => 1)));
        $this->assertFalse(HooksSynchronizationSingleUser::isExplicitOptIn(1, (object) array('newsletter' => 0)));
    }
}
