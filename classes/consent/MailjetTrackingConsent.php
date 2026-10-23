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

require_once dirname(__FILE__) . '/MailjetTrackingConsentStorageInterface.php';
require_once dirname(__FILE__) . '/MailjetTrackingConsentDbStorage.php';
require_once dirname(__FILE__) . '/MailjetTrackingConsentApiClient.php';

/**
 * GDPR open/click tracking consent, independent of the newsletter subscription.
 *
 * - No stored value means no consent, so tracking is disabled by default.
 * - CMS changes are pushed to a Mailjet contact property; changes made in Mailjet are pulled by the cron.
 * - Emails to recipients without consent get the X-Mailjet-TrackOpen / X-Mailjet-TrackClick headers set to 0.
 */
class MailjetTrackingConsent
{
    const CONFIG_ENABLED = 'MJ_TRACKING_CONSENT_ENABLED';
    const CONFIG_LABEL = 'MJ_TRACKING_CONSENT_LABEL';
    const CONFIG_PROPERTY = 'MJ_TRACKING_CONSENT_PROPERTY';
    const CONFIG_PROPERTIES_CREATED = 'MJ_TRACKING_CONSENT_PROPS';

    const DEFAULT_PROPERTY = 'ps_tracking_consent';
    const FIELD_NAME = 'mj_tracking_consent';

    const SOURCE_FRONT = 'front';
    const SOURCE_ADMIN = 'admin';
    const SOURCE_MAILJET = 'mailjet';

    const HEADER_TRACK_OPEN = 'X-Mailjet-TrackOpen';
    const HEADER_TRACK_CLICK = 'X-Mailjet-TrackClick';

    const PUSH_BATCH_SIZE = 200;

    /**
     * @var MailjetTrackingConsentStorageInterface
     */
    private $storage;

    /**
     * @var MailjetTrackingConsentApiClient|null
     */
    private $client;

    /**
     * @var bool
     */
    private $enabled;

    /**
     * @var string
     */
    private $property;

    /**
     * @var callable
     */
    private $clock;

    /**
     * @param MailjetTrackingConsentStorageInterface $storage
     * @param MailjetTrackingConsentApiClient|null $client null when no Mailjet account is connected
     * @param bool $enabled
     * @param string $property Mailjet contact property holding the consent
     * @param callable|null $clock returns the current date as Y-m-d H:i:s
     */
    public function __construct(
        MailjetTrackingConsentStorageInterface $storage,
        $client,
        $enabled,
        $property = self::DEFAULT_PROPERTY,
        $clock = null
    ) {
        $this->storage = $storage;
        $this->client = $client;
        $this->enabled = (bool) $enabled;
        $this->property = $property ?: self::DEFAULT_PROPERTY;
        $this->clock = $clock ?: function () {
            return date('Y-m-d H:i:s');
        };
    }

    /**
     * Build the service from the module configuration
     *
     * @return MailjetTrackingConsent
     */
    public static function create()
    {
        $account = json_decode((string) Configuration::get('MAILJET'), true);
        $client = null;
        if (!empty($account['API_KEY']) && !empty($account['SECRET_KEY'])) {
            $client = new MailjetTrackingConsentApiClient($account['API_KEY'], $account['SECRET_KEY']);
        }

        return new self(
            new MailjetTrackingConsentDbStorage(),
            $client,
            (bool) Configuration::get(self::CONFIG_ENABLED),
            (string) Configuration::get(self::CONFIG_PROPERTY)
        );
    }

    /**
     * @return bool
     */
    public function isEnabled()
    {
        return $this->enabled;
    }

    /**
     * @return string
     */
    public function getProperty()
    {
        return $this->property;
    }

    /**
     * @param int $idCustomer
     * @return array|null
     */
    public function getStatus($idCustomer)
    {
        return $this->storage->find($idCustomer);
    }

    /**
     * @param int $idCustomer
     * @param int $limit
     * @return array[]
     */
    public function getHistory($idCustomer, $limit = 20)
    {
        return $this->storage->getHistory($idCustomer, $limit);
    }

    /**
     * @param int $idCustomer
     * @return bool
     */
    public function hasConsent($idCustomer)
    {
        $row = $this->storage->find($idCustomer);

        return $row !== null && $row['consent'];
    }

    /**
     * @param string $email
     * @return bool
     */
    public function hasConsentForEmail($email)
    {
        $email = trim((string) $email);
        if ($email === '') {
            return false;
        }
        $row = $this->storage->findByEmail($email);

        return $row !== null && $row['consent'];
    }

    /**
     * Record a consent or a withdrawal. Nothing is written when the value does not change,
     * except for the first value of a customer, which is always stored for audit purposes.
     *
     * @param int $idCustomer
     * @param string $email
     * @param bool $consent
     * @param string $source one of the SOURCE_* constants
     * @param bool $push push the change to Mailjet right away
     * @return bool true if a new value was recorded
     */
    public function setConsent($idCustomer, $email, $consent, $source, $push = true)
    {
        if (!(int) $idCustomer || trim((string) $email) === '') {
            return false;
        }

        $consent = (bool) $consent;
        $current = $this->storage->find($idCustomer);
        if ($current !== null && $current['consent'] === $consent && $current['email'] === $email) {
            return false;
        }

        $row = [
            'id_customer' => (int) $idCustomer,
            'email' => $email,
            'consent' => $consent,
            'source' => $source,
            'synced' => false,
            'date_upd' => call_user_func($this->clock),
        ];
        if (!$this->storage->save($row)) {
            return false;
        }

        if ($push) {
            $this->push($row);
        }

        return true;
    }

    /**
     * Store the value posted with the front office registration / identity form.
     * An unchecked checkbox is not posted, which is a refusal or a withdrawal.
     *
     * @param int $idCustomer
     * @param string $email
     * @param mixed $postedValue
     * @return bool
     */
    public function setConsentFromForm($idCustomer, $email, $postedValue)
    {
        if (!$this->enabled) {
            return false;
        }

        return $this->setConsent($idCustomer, $email, !empty($postedValue), self::SOURCE_FRONT);
    }

    /**
     * Customer email changed: move the consent to the new address on both sides
     *
     * @param int $idCustomer
     * @param string $email
     * @return bool
     */
    public function updateEmail($idCustomer, $email)
    {
        $current = $this->storage->find($idCustomer);
        if ($current === null || $current['email'] === $email) {
            return false;
        }

        return $this->setConsent($idCustomer, $email, $current['consent'], $current['source']);
    }

    /**
     * @param string[] $emails
     * @return bool
     */
    public function shouldDisableTracking(array $emails)
    {
        if (!$this->enabled) {
            return false;
        }

        foreach ($emails as $email) {
            if (!$this->hasConsentForEmail($email)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Mailjet SMTP headers which turn off open and click tracking for one message
     *
     * @return array<string, string>
     */
    public static function getDisableTrackingHeaders()
    {
        return [
            self::HEADER_TRACK_OPEN => '0',
            self::HEADER_TRACK_CLICK => '0',
        ];
    }

    /**
     * Add the "no tracking" headers to an outgoing message when one of its recipients did not consent.
     * Supports Swift_Message (PrestaShop 1.6.1.5 - 8) and Symfony\Component\Mime\Email (PrestaShop 9).
     *
     * @param object $message
     * @return bool true if tracking was disabled
     */
    public function applyToMessage($message)
    {
        if (!is_object($message) || !method_exists($message, 'getHeaders')) {
            return false;
        }

        if (!$this->shouldDisableTracking(self::extractRecipients($message))) {
            return false;
        }

        $headers = $message->getHeaders();
        foreach (self::getDisableTrackingHeaders() as $name => $value) {
            if (method_exists($headers, 'has') && $headers->has($name)) {
                $headers->remove($name);
            }
            $headers->addTextHeader($name, $value);
        }

        return true;
    }

    /**
     * @param object $message
     * @return string[]
     */
    public static function extractRecipients($message)
    {
        $emails = [];
        foreach (['getTo', 'getCc', 'getBcc'] as $getter) {
            if (!method_exists($message, $getter)) {
                continue;
            }
            $recipients = $message->$getter();
            if (!is_array($recipients)) {
                continue;
            }
            foreach ($recipients as $key => $recipient) {
                if (is_object($recipient) && method_exists($recipient, 'getAddress')) {
                    // Symfony Mime Address
                    $emails[] = $recipient->getAddress();
                } elseif (is_string($key)) {
                    // Swift: [email => name]
                    $emails[] = $key;
                } elseif (is_string($recipient)) {
                    $emails[] = $recipient;
                }
            }
        }

        return array_values(array_unique($emails));
    }

    /**
     * Retry the rows which could not be pushed to Mailjet
     *
     * @return int number of rows pushed
     */
    public function pushPending()
    {
        if ($this->client === null) {
            return 0;
        }

        $pushed = 0;
        foreach ($this->storage->findUnsynced(self::PUSH_BATCH_SIZE) as $row) {
            if ($this->push($row)) {
                $pushed++;
            }
        }

        return $pushed;
    }

    /**
     * Mailjet -> CMS: apply consent changes made on the Mailjet side.
     * Local changes not pushed yet win, they are pushed first.
     *
     * @return array{pushed: int, updated: int}
     */
    public function synchronizeFromMailjet()
    {
        $result = ['pushed' => 0, 'updated' => 0];
        if (!$this->enabled || $this->client === null) {
            return $result;
        }

        $this->ensureProperties();
        $result['pushed'] = $this->pushPending();

        foreach ($this->client->fetchConsents($this->property) as $email => $remoteConsent) {
            $result['updated'] += $this->applyRemoteConsent($email, $remoteConsent);
        }

        return $result;
    }

    /**
     * @param string $email
     * @param bool $remoteConsent
     * @return int number of customers updated
     */
    public function applyRemoteConsent($email, $remoteConsent)
    {
        $updated = 0;
        foreach ($this->storage->findCustomerIdsByEmail($email) as $idCustomer) {
            $local = $this->storage->find($idCustomer);
            if ($local !== null && !$local['synced']) {
                continue;
            }
            $localConsent = $local !== null && $local['consent'];
            if ($local !== null && $localConsent === (bool) $remoteConsent) {
                continue;
            }
            if ($local === null && !$remoteConsent) {
                // Nothing stored and no consent in Mailjet: already the default
                continue;
            }

            $customerEmail = $local !== null ? $local['email'] : $email;
            if ($this->setConsent($idCustomer, $customerEmail, $remoteConsent, self::SOURCE_MAILJET, false)) {
                $this->storage->markSynced($idCustomer, true);
                $updated++;
            }
        }

        return $updated;
    }

    /**
     * Properties to add to a contact in the bulk synchronizations. Customers without a stored
     * value are left out so that a value set in Mailjet is not overwritten before it is pulled.
     *
     * @param int $idCustomer
     * @return array
     */
    public function getContactProperties($idCustomer)
    {
        if (!$this->enabled) {
            return [];
        }
        $row = $this->storage->find($idCustomer);
        if ($row === null || !$this->ensureProperties()) {
            return [];
        }

        return [
            $this->property => $row['consent'],
            MailjetTrackingConsentApiClient::getDateProperty($this->property)
                => gmdate('Y-m-d\TH:i:s\Z', strtotime($row['date_upd'])),
        ];
    }

    /**
     * Create the Mailjet contact properties once per property name
     *
     * @return bool
     */
    public function ensureProperties()
    {
        if ($this->client === null) {
            return false;
        }
        if (Configuration::get(self::CONFIG_PROPERTIES_CREATED) === $this->property) {
            return true;
        }
        if (!$this->client->ensureProperties($this->property)) {
            return false;
        }
        Configuration::updateValue(self::CONFIG_PROPERTIES_CREATED, $this->property);

        return true;
    }

    /**
     * @param array $row
     * @return bool
     */
    private function push(array $row)
    {
        if ($this->client === null || !$this->ensureProperties()) {
            return false;
        }

        try {
            $pushed = $this->client->updateContactConsent(
                $this->property,
                $row['email'],
                $row['consent'],
                $row['date_upd']
            );
        } catch (Exception $e) {
            $pushed = false;
        }

        if ($pushed) {
            $this->storage->markSynced($row['id_customer'], true);
        }

        return $pushed;
    }
}
