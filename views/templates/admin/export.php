<?php

require_once dirname(__DIR__, 3) . '/classes/endpoints/run.php';
MailjetEndpointRunner::run('segmentation_export');
