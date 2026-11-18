<?php

require_once dirname(__DIR__, 2) . '/classes/queue/MailjetPendingOperationStorageInterface.php';

class InMemoryPendingOperationStorage implements MailjetPendingOperationStorageInterface
{
    /** @var array[] keyed by email|list_id */
    public $rows = array();
    private $nextId = 1;

    public function save($email, $listId, array $contact, $error)
    {
        $key = $email . '|' . (int) $listId;
        $id = isset($this->rows[$key]) ? $this->rows[$key]['id'] : $this->nextId++;
        $this->rows[$key] = array(
            'id' => $id,
            'email' => $email,
            'list_id' => (int) $listId,
            'contact' => $contact,
            'attempts' => 0,
            'last_error' => $error,
            'status' => MailjetPendingOperationQueue::STATUS_PENDING,
        );

        return true;
    }

    public function delete($email, $listId)
    {
        unset($this->rows[$email . '|' . (int) $listId]);

        return true;
    }

    public function findPending($limit)
    {
        $pending = array_values(array_filter($this->rows, function ($row) {
            return $row['status'] === MailjetPendingOperationQueue::STATUS_PENDING;
        }));

        return array_slice($pending, 0, $limit);
    }

    public function updateAttempt($id, $attempts, $error, $status)
    {
        foreach ($this->rows as $key => $row) {
            if ($row['id'] === $id) {
                $this->rows[$key]['attempts'] = $attempts;
                $this->rows[$key]['last_error'] = $error;
                $this->rows[$key]['status'] = $status;
            }
        }

        return true;
    }

    public function count($status)
    {
        return count(array_filter($this->rows, function ($row) use ($status) {
            return $row['status'] === $status;
        }));
    }
}
