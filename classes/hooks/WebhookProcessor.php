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

/**
 * Processes a Mailjet event webhook call and decides the HTTP answer.
 *
 * Mailjet retries a call that is not answered with 2xx, so a 2xx is only
 * returned once every event of the call was applied. If saving an event or
 * applying an unsubscription fails, the call is answered with 500 and
 * Mailjet sends it again later. Every handler is idempotent, so the events
 * of the call which were already applied can safely be processed again.
 */
class HooksWebhookProcessor
{
    /** Events stored in the mj_events table */
    const STORED_EVENTS = ['bounce', 'spam', 'blocked', 'typofix'];

    /** Events acknowledged without any processing */
    const IGNORED_EVENTS = ['sent', 'open', 'click'];

    /** @var callable function (array $event): bool */
    private $storeEvent;

    /** @var callable function (array $event): bool */
    private $unsubscribe;

    /** @var callable function (int $listId): bool */
    private $isManagedList;

    public function __construct(callable $storeEvent, callable $unsubscribe, callable $isManagedList)
    {
        $this->storeEvent = $storeEvent;
        $this->unsubscribe = $unsubscribe;
        $this->isManagedList = $isManagedList;
    }

    /**
     * @return self
     */
    public static function create()
    {
        $listScope = null;

        return new self(
            function (array $event) {
                $mjEvents = new MailJetEvents($event['event'], $event);

                return (bool) $mjEvents->add();
            },
            function (array $event) {
                $hooksEvents = new HooksEvents();

                return $hooksEvents->unsubscribe($event);
            },
            function ($listId) use (&$listScope) {
                if ($listScope === null) {
                    $listScope = new HooksSynchronizationSingleUser(MailjetTemplate::getApi());
                }

                return $listScope->isManagedList($listId);
            }
        );
    }

    /**
     * @param  array $events decoded webhook body (one event, or a list of events)
     * @return array{0: int, 1: string} HTTP status code and reason phrase
     */
    public function process(array $events)
    {
        // Version 1 sends a single event, version 2 a list of events
        if (array_key_exists('event', $events)) {
            $events = [$events];
        }

        $response = [200, 'Ok'];
        foreach ($events as $event) {
            if (!is_array($event) || !isset($event['event'])) {
                return [422, 'Not ok'];
            }

            try {
                $result = $this->processEvent($event);
            } catch (Exception $e) {
                $this->log('event ' . $event['event'] . ' failed: ' . $e->getMessage());

                return [500, 'Error'];
            }

            if ($result === false) {
                return [500, 'Error'];
            }
            if ($result === null) {
                $response = [423, 'No handler'];
            }
        }

        return $response;
    }

    /**
     * @param  array $event
     * @return bool|null true when applied or nothing to do, false when it failed,
     *                   null when no handler exists for this event type
     */
    private function processEvent(array $event)
    {
        $type = $event['event'];

        if (in_array($type, self::IGNORED_EVENTS, true)) {
            return true;
        }

        if (in_array($type, self::STORED_EVENTS, true)) {
            if (!call_user_func($this->storeEvent, $event)) {
                $this->log('event ' . $type . ' for ' . $this->email($event) . ' could not be saved');

                return false;
            }

            return true;
        }

        if ($type === 'unsub') {
            // Only unsubscriptions from a list managed by this shop are applied (docs/scope-rules.md)
            if (!empty($event['mj_list_id']) && !call_user_func($this->isManagedList, $event['mj_list_id'])) {
                return true;
            }
            if (!call_user_func($this->unsubscribe, $event)) {
                $this->log('unsub for ' . $this->email($event) . ' could not be applied');

                return false;
            }

            return true;
        }

        return null;
    }

    /**
     * @param  array $event
     * @return string
     */
    private function email(array $event)
    {
        return isset($event['email']) && is_string($event['email']) ? $event['email'] : '(no email)';
    }

    /**
     * @param string $message
     */
    private function log($message)
    {
        if (class_exists('MailJetLog') && MailJetLog::$file) {
            MailJetLog::write(MailJetLog::$file, 'Webhook: ' . $message);
        }
    }
}
