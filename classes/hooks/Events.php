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

class HooksEvents
{
    /**
     * Apply an unsubscription made in Mailjet to the shop.
     *
     * Customer accounts and guest newsletter subscribers (emailsubscription,
     * no customer account) are handled independently, both in the shop of
     * the current context.
     *
     * @param  array $event Mailjet event, needs an 'email' key
     * @return bool true only if every change was saved
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    public function unsubscribe(array $event)
    {
        if (empty($event['email']) || !is_string($event['email'])) {
            return false;
        }

        $email = $event['email'];
        $success = true;

        foreach ($this->findCustomerIds($email) as $idCustomer) {
            $customer = $this->loadCustomer($idCustomer);
            if (!$customer || !$customer->id || (int) $customer->newsletter === 0) {
                continue;
            }

            $customer->newsletter = 0;
            if (!$customer->update()) {
                $success = false;
            }
        }

        if (!$this->deleteNewsletterSubscription($email)) {
            $success = false;
        }

        return $success;
    }

    /**
     * Ids of the not deleted customers using this email in the current shop (group)
     *
     * @param  string $email
     * @return int[]
     */
    protected function findCustomerIds($email)
    {
        $rows = Db::getInstance()->executeS(
            'SELECT `id_customer` FROM `' . _DB_PREFIX_ . 'customer`
            WHERE `email` = \'' . pSQL($email) . '\' AND `deleted` = 0' .
            $this->getShopRestriction(true)
        );

        return is_array($rows) ? array_map('intval', array_column($rows, 'id_customer')) : array();
    }

    /**
     * @param  int $idCustomer
     * @return Customer
     */
    protected function loadCustomer($idCustomer)
    {
        return new Customer((int) $idCustomer);
    }

    /**
     * Remove the guest newsletter registration of this email, if any
     *
     * @param  string $email
     * @return bool
     */
    protected function deleteNewsletterSubscription($email)
    {
        $table = version_compare(_PS_VERSION_, '1.7', '<') ? 'newsletter' : 'emailsubscription';

        return (bool) Db::getInstance()->execute(
            'DELETE FROM `' . _DB_PREFIX_ . $table . '`
            WHERE `email` = \'' . pSQL($email) . '\'' . $this->getShopRestriction(false)
        );
    }

    /**
     * @param  bool $customerSharing restrict on the shops sharing customers (customer table)
     * @return string SQL condition starting with " AND", or an empty string
     */
    protected function getShopRestriction($customerSharing)
    {
        if (!class_exists('Shop') || !method_exists('Shop', 'addSqlRestriction')) {
            return '';
        }

        return $customerSharing ? Shop::addSqlRestriction(Shop::SHARE_CUSTOMER) : Shop::addSqlRestriction(false);
    }
}
