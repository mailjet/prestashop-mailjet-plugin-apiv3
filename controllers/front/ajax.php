<?php

require_once _PS_MODULE_DIR_ . 'mailjet/classes/endpoints/run.php';

class MailjetAjaxModuleFrontController extends ModuleFrontController
{
    public $ssl = true;
    public $display_header = false;
    public $display_footer = false;

    public function postProcess()
    {
        MailjetEndpointRunner::run('backoffice_ajax');
    }
}
