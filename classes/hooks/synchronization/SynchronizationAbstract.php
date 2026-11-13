<?php

/**
 * 2007-2017 PrestaShop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright 2007-2017 PrestaShop SA
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 * International Registered Trademark & Property of PrestaShop SA
 */

require_once dirname(__FILE__) . '/Exception.php';

/**
 *
 * @author atanas
 */
abstract class HooksSynchronizationSynchronizationAbstract
{
    /**
     *
     * @var string
     */
    const LIST_NAME = 'PrestaShop Customers Master List';

    /**
     *
     * @var int
     */
    protected $masterListId;

    /**
     *
     * @var ApiOverlay
     */
    protected $apiOverlay;

    /**
     *
     * @param Mailjet_ApiOverlay $apiOverlay
     */
    public function __construct(Mailjet_ApiOverlay $apiOverlay)
    {
        $this->apiOverlay = $apiOverlay;
    }

    /**
     *
     * @return Mailjet_ApiOverlay
     */
    protected function getApiOverlay()
    {
        return $this->apiOverlay;
    }

    /**
     *
     * @return Api
     */
    protected function getApi()
    {
        return $this->getApiOverlay()->getApi();
    }

    /**
     *
     * @throws Exception
     * @return Db
     */
    public function getDbInstance()
    {
        if (!Db::getInstance()) {
            throw new Exception('Db instance is not provided.');
        }

        return Db::getInstance();
    }

    /**
     *
     * @return number|boolean
     */
    public function getAlreadyCreatedMasterListId()
    {
        if (Configuration::get('contact_list')) {
            $this->masterListId = Configuration::get('contact_list');
        }
        if (!$this->masterListId) {
            $lists = $this->getApiOverlay()->getContactsLists();

            if ($lists !== false) {
                foreach ($lists as $list) {
                    if ($list->Name === self::LIST_NAME) {
                        $this->masterListId = (int) $list->ID;
                    }
                }
            }
        }
        return $this->masterListId;
    }

    /**
     * Ids of the Mailjet lists this shop manages: the master list and the
     * segment lists ("<id_filter>idf<name>"). Lists created outside the module
     * on the same API key are never included. See docs/scope-rules.md.
     *
     * @param  bool $includeMaster
     * @return int[]
     */
    public function getManagedListIds($includeMaster = true)
    {
        $masterListId = (int) $this->getAlreadyCreatedMasterListId();
        $managed = array();
        if ($includeMaster && $masterListId) {
            $managed[] = $masterListId;
        }

        $lists = $this->getApiOverlay()->getContactsLists();
        if (is_array($lists)) {
            foreach ($lists as $list) {
                if (!isset($list->ID, $list->Name) || (int) $list->ID === $masterListId) {
                    continue;
                }
                if (self::isSegmentListName($list->Name)) {
                    $managed[] = (int) $list->ID;
                }
            }
        }

        return array_values(array_unique($managed));
    }

    /**
     * @param  int|string $listId
     * @return bool
     */
    public function isManagedList($listId)
    {
        return !empty($listId) && in_array((int) $listId, $this->getManagedListIds(), true);
    }

    /**
     * Segment lists are created as "<id_filter>idf<name>" by HooksSynchronizationSegment
     *
     * @param  string $name
     * @return bool
     */
    public static function isSegmentListName($name)
    {
        return (bool) preg_match('/^[0-9]+idf/', (string) $name);
    }

    public function getMailjetSegmentationLists($listNames)
    {
        if (!is_array($listNames)) {
            return false;
        }

        $mailjetLists = $this->getApiOverlay()->getContactsLists();

        $mailjetSegmentListIds = array();

        foreach ($mailjetLists as $mailjetList) {
            if (in_array($mailjetList->Name, $listNames)) {
                $mailjetSegmentListIds[] = (int) $mailjetList->ID;
            }
        }
        return $mailjetSegmentListIds;
    }
}
