<?php

class InMemoryTrackingConsentStorage implements MailjetTrackingConsentStorageInterface
{
    /** @var array<int, array> */
    public $rows = [];

    /** @var array[] */
    public $history = [];

    /** @var array<int, string> id_customer => email of the ps_customer table */
    public $customers = [];

    public function find($idCustomer)
    {
        return isset($this->rows[(int) $idCustomer]) ? $this->rows[(int) $idCustomer] : null;
    }

    public function findByEmail($email)
    {
        $found = null;
        foreach ($this->rows as $row) {
            if (strcasecmp($row['email'], $email) === 0
                && ($found === null || $row['date_upd'] >= $found['date_upd'])
            ) {
                $found = $row;
            }
        }

        return $found;
    }

    public function findCustomerIdsByEmail($email)
    {
        $ids = [];
        foreach ($this->customers as $id => $customerEmail) {
            if (strcasecmp($customerEmail, $email) === 0) {
                $ids[] = (int) $id;
            }
        }

        return $ids;
    }

    public function save(array $row)
    {
        $this->rows[(int) $row['id_customer']] = $row;
        $this->history[] = [
            'id_customer' => (int) $row['id_customer'],
            'email' => $row['email'],
            'consent' => (bool) $row['consent'],
            'source' => $row['source'],
            'date_add' => $row['date_upd'],
        ];

        return true;
    }

    public function markSynced($idCustomer, $synced)
    {
        if (!isset($this->rows[(int) $idCustomer])) {
            return false;
        }
        $this->rows[(int) $idCustomer]['synced'] = (bool) $synced;

        return true;
    }

    public function findUnsynced($limit)
    {
        return array_slice(array_values(array_filter($this->rows, function ($row) {
            return !$row['synced'];
        })), 0, $limit);
    }

    public function getHistory($idCustomer, $limit)
    {
        $entries = array_values(array_filter($this->history, function ($entry) use ($idCustomer) {
            return $entry['id_customer'] === (int) $idCustomer;
        }));

        return array_slice(array_reverse($entries), 0, $limit);
    }
}
