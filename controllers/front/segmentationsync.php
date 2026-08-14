<?php

require_once _PS_MODULE_DIR_ . 'mailjet/classes/endpoints/run.php';

class MailjetSegmentationsyncModuleFrontController extends ModuleFrontController
{
    public $ssl = true;
    public $display_header = false;
    public $display_footer = false;

    public function postProcess()
    {
        MailjetEndpointRunner::run('segmentation_sync');
    }
}
