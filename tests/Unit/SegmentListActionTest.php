<?php

use PHPUnit\Framework\TestCase;

require_once dirname(__DIR__, 2) . '/libraries/Mailjet.Overlay.class.php';
require_once dirname(__DIR__, 2) . '/classes/hooks/synchronization/SynchronizationAbstract.php';
require_once dirname(__DIR__, 2) . '/classes/hooks/synchronization/SingleUser.php';

/**
 * DE-11516 (audit #01): a matching auto-assigned segment must not subscribe a
 * customer who has not given newsletter consent.
 */
class SegmentListActionTest extends TestCase
{
    private static function customer($active, $newsletter)
    {
        return (object) array('active' => $active, 'newsletter' => $newsletter);
    }

    public function testActiveSubscriberMatchingSegmentIsSubscribed()
    {
        $this->assertSame(
            HooksSynchronizationSingleUser::SEGMENT_SUBSCRIBE,
            HooksSynchronizationSingleUser::getSegmentListAction(self::customer(1, 1), true)
        );
    }

    public function testCustomerWithoutNewsletterConsentIsNeverSubscribed()
    {
        $this->assertSame(
            HooksSynchronizationSingleUser::SEGMENT_UNSUBSCRIBE,
            HooksSynchronizationSingleUser::getSegmentListAction(self::customer(1, 0), true)
        );
        $this->assertSame(
            HooksSynchronizationSingleUser::SEGMENT_UNSUBSCRIBE,
            HooksSynchronizationSingleUser::getSegmentListAction(self::customer('1', '0'), true)
        );
    }

    public function testInactiveCustomerIsNotSubscribed()
    {
        $this->assertSame(
            HooksSynchronizationSingleUser::SEGMENT_UNSUBSCRIBE,
            HooksSynchronizationSingleUser::getSegmentListAction(self::customer(0, 1), true)
        );
    }

    public function testCustomerNotMatchingSegmentIsRemoved()
    {
        $this->assertSame(
            HooksSynchronizationSingleUser::SEGMENT_REMOVE,
            HooksSynchronizationSingleUser::getSegmentListAction(self::customer(1, 1), false)
        );
    }
}
