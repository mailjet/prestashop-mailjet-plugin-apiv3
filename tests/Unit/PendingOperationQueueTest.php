<?php

use PHPUnit\Framework\TestCase;

require_once dirname(__DIR__, 2) . '/libraries/Mailjet.Overlay.class.php';
require_once dirname(__DIR__, 2) . '/classes/hooks/synchronization/SynchronizationAbstract.php';
require_once dirname(__DIR__, 2) . '/classes/hooks/synchronization/SingleUser.php';
require_once dirname(__DIR__, 2) . '/classes/queue/MailjetPendingOperationQueue.php';
require_once dirname(__DIR__) . '/Support/FakeUnsubscribeApiOverlay.php';
require_once dirname(__DIR__) . '/Support/InMemoryPendingOperationStorage.php';

/**
 * DE-11715 (audit #12): failed Mailjet calls are checked, kept and retried,
 * and one failure does not stop the other lists.
 */
class PendingOperationQueueTest extends TestCase
{
    /** @var FakeUnsubscribeApiOverlay */
    private $overlay;

    /** @var InMemoryPendingOperationStorage */
    private $storage;

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
        );
        $this->storage = new InMemoryPendingOperationStorage();
        $this->sync = new HooksSynchronizationSingleUser($this->overlay);
        $this->sync->setPendingQueue(new MailjetPendingOperationQueue($this->storage, 3));
    }

    protected function tearDown(): void
    {
        Configuration::$values = array();
    }

    public function testFailedCallIsReportedAndQueued()
    {
        $this->overlay->failingLists = array(10);

        $this->assertFalse($this->sync->unsubscribe('john@example.com', 10));
        $this->assertCount(1, $this->storage->rows);
        $row = $this->storage->rows['john@example.com|10'];
        $this->assertSame('unsub', $row['contact']['Action']);
    }

    public function testExceptionIsCaughtAndQueued()
    {
        $this->overlay->failingLists = array(10);
        $this->overlay->throw = true;

        $this->assertFalse($this->sync->subscribe('john@example.com', 10));
        $this->assertSame('API unavailable', $this->storage->rows['john@example.com|10']['last_error']);
    }

    public function testOneFailureDoesNotStopTheOtherLists()
    {
        $this->overlay->failingLists = array(10);

        $this->assertFalse($this->sync->removeFromAllLists('john@example.com'));
        $this->assertCount(3, $this->overlay->calls, 'lists 11 and 12 still processed');
        $this->assertSame(array('john@example.com|10'), array_keys($this->storage->rows));
    }

    public function testNewerOperationReplacesTheQueuedOne()
    {
        $this->overlay->failingLists = array(10);
        $this->sync->subscribe('john@example.com', 10);
        $this->sync->unsubscribe('john@example.com', 10);

        $this->assertCount(1, $this->storage->rows);
        $this->assertSame('unsub', $this->storage->rows['john@example.com|10']['contact']['Action']);
    }

    public function testSuccessDropsAnOlderPendingOperation()
    {
        $this->overlay->failingLists = array(10);
        $this->sync->subscribe('john@example.com', 10);

        $this->overlay->failingLists = array();
        $this->assertTrue($this->sync->unsubscribe('john@example.com', 10));
        $this->assertSame(array(), $this->storage->rows, 'the old subscribe must not be retried over the unsub');
    }

    public function testRetrySendsAgainAndClearsTheQueue()
    {
        $this->overlay->failingLists = array(10);
        $this->sync->unsubscribe('john@example.com', 10);

        $this->overlay->failingLists = array();
        $this->overlay->calls = array();
        $result = $this->sync->retryPendingOperations();

        $this->assertSame(array('retried' => 1, 'succeeded' => 1, 'failed' => 0), $result);
        $this->assertSame(array(array('unsub', 'john@example.com', 10)), $this->overlay->calls);
        $this->assertSame(array(), $this->storage->rows);
    }

    public function testOperationIsGivenUpAfterMaxAttempts()
    {
        $this->overlay->failingLists = array(10);
        $this->sync->unsubscribe('john@example.com', 10);

        $queue = new MailjetPendingOperationQueue($this->storage, 3);
        $this->sync->retryPendingOperations();
        $this->sync->retryPendingOperations();
        $this->assertSame(1, $queue->countPending());

        $result = $this->sync->retryPendingOperations();
        $this->assertSame(1, $result['failed']);
        $this->assertSame(0, $queue->countPending());
        $this->assertSame(1, $queue->countFailed());

        $this->assertSame(0, $this->sync->retryPendingOperations()['retried'], 'failed operations are not retried');
    }

    public function testWithoutQueueFailuresAreStillReported()
    {
        $this->sync->setPendingQueue(null);
        $this->overlay->failingLists = array(11);

        $this->assertFalse($this->sync->unsubscribeListsExceptMaster('john@example.com'));
        $this->assertCount(2, $this->overlay->calls);
    }
}
