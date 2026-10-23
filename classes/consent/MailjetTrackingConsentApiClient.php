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
 * Minimal Mailjet v3 REST client for the tracking consent contact properties.
 *
 * The legacy API wrappers in /libraries cache GET requests and cannot send a JSON PUT
 * without leaking the ID into the body, so the consent sync uses its own small client.
 */
class MailjetTrackingConsentApiClient
{
    const API_URL = 'https://api.mailjet.com/v3/REST/';
    const PAGE_SIZE = 1000;
    const DATE_PROPERTY_SUFFIX = '_date';

    /**
     * @var string
     */
    private $apiKey;

    /**
     * @var string
     */
    private $secretKey;

    /**
     * function (string $method, string $url, array|null $body): array [int $httpCode, mixed $decodedBody]
     *
     * @var callable
     */
    private $transport;

    /**
     * @param string $apiKey
     * @param string $secretKey
     * @param callable|null $transport Replaces the cURL transport (used by tests)
     */
    public function __construct($apiKey, $secretKey, $transport = null)
    {
        $this->apiKey = $apiKey;
        $this->secretKey = $secretKey;
        $this->transport = $transport ?: [$this, 'curlRequest'];
    }

    /**
     * @param string $property
     * @return string
     */
    public static function getDateProperty($property)
    {
        return $property . self::DATE_PROPERTY_SUFFIX;
    }

    /**
     * Create the bool consent property and its datetime companion. Mailjet answers 400 when
     * a property already exists, which is fine.
     *
     * @param string $property
     * @return bool
     */
    public function ensureProperties($property)
    {
        $definitions = [
            ['Datatype' => 'bool', 'Name' => $property, 'NameSpace' => 'static'],
            ['Datatype' => 'datetime', 'Name' => self::getDateProperty($property), 'NameSpace' => 'static'],
        ];

        $result = true;
        foreach ($definitions as $definition) {
            list($code) = $this->request('POST', 'contactmetadata', $definition);
            if ($code !== 201 && $code !== 400) {
                $result = false;
            }
        }

        return $result;
    }

    /**
     * Write consent + consent date on the Mailjet contact, creating the contact if needed.
     * The contact is not added to any list, so newsletter subscription is left untouched.
     *
     * @param string $property
     * @param string $email
     * @param bool $consent
     * @param string $date Y-m-d H:i:s, shop time
     * @return bool
     */
    public function updateContactConsent($property, $email, $consent, $date)
    {
        $body = [
            'Data' => [
                ['Name' => $property, 'Value' => (bool) $consent],
                ['Name' => self::getDateProperty($property), 'Value' => gmdate('Y-m-d\TH:i:s\Z', strtotime($date))],
            ],
        ];
        $resource = 'contactdata/' . rawurlencode($email);

        list($code) = $this->request('PUT', $resource, $body);
        if ($code === 404) {
            list($createCode) = $this->request('POST', 'contact', ['Email' => $email]);
            if ($createCode !== 201) {
                return false;
            }
            list($code) = $this->request('PUT', $resource, $body);
        }

        return $code === 200;
    }

    /**
     * Read the consent property of every Mailjet contact which has it set
     *
     * @param string $property
     * @param int $maxPages Safety cap per run (PAGE_SIZE contacts per page)
     * @return array<string, bool> lowercased email => consent
     */
    public function fetchConsents($property, $maxPages = 50)
    {
        $consentByContactId = [];
        for ($page = 0; $page < $maxPages; $page++) {
            $data = $this->getPage('contactdata', $page);
            if ($data === null) {
                break;
            }
            foreach ($data as $contactData) {
                if (!isset($contactData->ContactID, $contactData->Data) || !is_array($contactData->Data)) {
                    continue;
                }
                foreach ($contactData->Data as $item) {
                    if (isset($item->Name) && $item->Name === $property && isset($item->Value)) {
                        $consentByContactId[(string) $contactData->ContactID] = self::toBool($item->Value);
                    }
                }
            }
            if (count($data) < self::PAGE_SIZE) {
                break;
            }
        }

        $consents = [];
        if (empty($consentByContactId)) {
            return $consents;
        }

        for ($page = 0; $page < $maxPages; $page++) {
            $contacts = $this->getPage('contact', $page);
            if ($contacts === null) {
                break;
            }
            foreach ($contacts as $contact) {
                $id = isset($contact->ID) ? (string) $contact->ID : null;
                if ($id !== null && isset($consentByContactId[$id]) && !empty($contact->Email)) {
                    $consents[Tools::strtolower($contact->Email)] = $consentByContactId[$id];
                }
            }
            if (count($consents) >= count($consentByContactId) || count($contacts) < self::PAGE_SIZE) {
                break;
            }
        }

        return $consents;
    }

    /**
     * Mailjet returns bool properties as JSON booleans or as "true"/"false" strings
     *
     * @param mixed $value
     * @return bool
     */
    public static function toBool($value)
    {
        if (is_bool($value)) {
            return $value;
        }

        return in_array(Tools::strtolower(trim((string) $value)), ['1', 'true', 'yes', 'on'], true);
    }

    /**
     * @param string $resource
     * @param int $page
     * @return array|null
     */
    private function getPage($resource, $page)
    {
        list($code, $response) = $this->request(
            'GET',
            $resource . '?Limit=' . self::PAGE_SIZE . '&Offset=' . ($page * self::PAGE_SIZE)
        );

        if ($code !== 200 || !isset($response->Data) || !is_array($response->Data) || empty($response->Data)) {
            return null;
        }

        return $response->Data;
    }

    /**
     * @param string $method
     * @param string $resource
     * @param array|null $body
     * @return array [int $httpCode, mixed $decodedBody]
     */
    private function request($method, $resource, $body = null)
    {
        return call_user_func($this->transport, $method, self::API_URL . $resource, $body);
    }

    /**
     * @param string $method
     * @param string $url
     * @param array|null $body
     * @return array
     */
    public function curlRequest($method, $url, $body = null)
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($curl, CURLOPT_TIMEOUT, 10);
        curl_setopt($curl, CURLOPT_USERPWD, $this->apiKey . ':' . $this->secretKey);
        curl_setopt($curl, CURLOPT_USERAGENT, 'prestashop-3.0');
        if ($body !== null) {
            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($body));
            curl_setopt($curl, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        }

        $buffer = curl_exec($curl);
        $code = (int) curl_getinfo($curl, CURLINFO_HTTP_CODE);
        if (PHP_VERSION_ID < 80000) {
            curl_close($curl);
        }

        return [$code, is_string($buffer) ? json_decode($buffer) : null];
    }
}
