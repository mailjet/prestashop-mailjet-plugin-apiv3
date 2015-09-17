<?php

if (!defined('_PS_VERSION_')){
    exit;
}

function upgrade_module_3_2_8($object) {
    // Process Module upgrade to 3.2.8
    return Db::getInstance()->execute(
        'INSERT INTO `'._DB_PREFIX_.'mj_sourcecondition` VALUES (4, 1, 107, \'LEFT JOIN `%1shop` s ON s.`id_shop` = c.`id_shop`\')');
}

?>