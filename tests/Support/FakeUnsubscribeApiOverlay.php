<?php

/**
 * Records list calls instead of calling the Mailjet API
 */
class FakeUnsubscribeApiOverlay extends Mailjet_ApiOverlay
{
    public $customerLists = false;
    public $accountLists = array();
    public $calls = array();

    public function __construct()
    {
    }

    public function getApi()
    {
        return new class {
            public function resetRequest()
            {
            }
        };
    }

    public function getCustomerLists($email)
    {
        return $this->customerLists;
    }

    public function getContactsLists($start = null, $limit = null, $orderby = null, $cache = null)
    {
        return $this->accountLists;
    }

    public function addDetailedContactToList($contact, $listID)
    {
        $this->calls[] = array($contact['Action'], $contact['Email'], $listID);

        return (object) array('Count' => 1);
    }
}
