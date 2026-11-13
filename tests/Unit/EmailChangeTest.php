<?php

use PHPUnit\Framework\TestCase;

require_once dirname(__DIR__, 2) . '/libraries/Mailjet.Overlay.class.php';
require_once dirname(__DIR__, 2) . '/classes/hooks/synchronization/SynchronizationAbstract.php';
require_once dirname(__DIR__, 2) . '/classes/hooks/synchronization/SingleUser.php';

/**
 * DE-11567 (audit #06): when a customer changes his email from A to B, A is
 * detected so it can be removed from the shop's lists.
 */
class EmailChangeTest extends TestCase
{
    public function testChangedEmailReturnsThePreviousAddress()
    {
        $customer = (object) array('email' => 'b@example.com');

        $this->assertSame('a@example.com', HooksSynchronizationSingleUser::getReplacedEmail('a@example.com', $customer));
    }

    public function testSameEmailIgnoringCaseIsNotAChange()
    {
        $customer = (object) array('email' => 'John@Example.com');

        $this->assertNull(HooksSynchronizationSingleUser::getReplacedEmail('john@example.com', $customer));
    }

    public function testUnknownPreviousEmailIsNotAChange()
    {
        $customer = (object) array('email' => 'b@example.com');

        $this->assertNull(HooksSynchronizationSingleUser::getReplacedEmail(null, $customer));
        $this->assertNull(HooksSynchronizationSingleUser::getReplacedEmail('', $customer));
    }
}
