<?php

require_once _PS_MODULE_DIR_ . 'mailjet/mailjet.php';

$internalToken = Tools::getValue('internaltoken');
if ($internalToken !== Tools::getAdminTokenLite('AdminModules')) {
    header('HTTP/1.1 403 Forbidden');
    exit;
}

$mailjet = new Mailjet();
$mailjet->checkSubscription();

$params = [
    MailJetPages::REQUEST_PAGE_TYPE => 'HOME',
    'token' => $internalToken,
];

Tools::redirectAdmin($mailjet->getAdminModuleLink($params));
