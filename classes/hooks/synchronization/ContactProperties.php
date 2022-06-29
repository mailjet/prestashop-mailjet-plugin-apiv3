<?php

/**
 * Mailjet Public API / The real-time Cloud Emailing platform
 * Connect your Apps and Make our product yours with our powerful API
 * http://www.mailjet.com/ Mailjet SAS Website
 *
 * @author  Mailjet Dev team
 * @author  Oleksandr Mykhailenko (inspired by Sofia)
 */
class ContactProperties extends HooksSynchronizationSynchronizationAbstract
{
    public const PROPERTY_TYPE_INT = 'int';
    public const PROPERTY_TYPE_STR = 'str';
    public const PROPERTY_TYPE_FLOAT = 'float';
    public const PROPERTY_TYPE_BOOL = 'bool';
    public const PROPERTY_TYPE_DATETIME = 'datetime';

    public const NAMESPACE_STATIC = 'static';
    public const NAMESPACE_HISTORIC = 'historic';

    private const PREDEFINED_CONTACT_PROPERTIES = [
        [
            'type' => self::PROPERTY_TYPE_DATETIME,
            'name' => 'ps_account_creation_date',
            'namespace' => self::NAMESPACE_STATIC,
        ],
        [
            'type' => self::PROPERTY_TYPE_INT,
            'name' => 'ps_total_orders_count',
            'namespace' => self::NAMESPACE_STATIC,
        ],
        [
            'type' => self::PROPERTY_TYPE_DATETIME,
            'name' => 'ps_last_order_date',
            'namespace' => self::NAMESPACE_STATIC,
        ],
    ];

    /**
     * @return bool
     */
    public function createPredefinedProperties(): bool
    {
        //Preparing data
        foreach (self::PREDEFINED_CONTACT_PROPERTIES as $property) {
            $requestData = [
                'method' => 'POST',
                'datatype' => $property['type'],
                'Name' => $property['name'],
                'NameSpace' => $property['namespace'],
            ];

            try {
                $this->getApiOverlay()->createContactMetaData($requestData);
            } catch (Exception $exception) {
                continue;
            }
        }

        return true;
    }
}
