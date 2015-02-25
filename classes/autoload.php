<?php

spl_autoload_register('autoload');

function autoload($className){
    $classesDir = _PS_MODULE_DIR_.'mailjet/classes/';
    foreach(array_keys(iterator_to_array(new RecursiveIteratorIterator(new RecursiveDirectoryIterator($classesDir)),true)) as $filePath)
    {
        $fileChunks = explode(DIRECTORY_SEPARATOR, $filePath);
        $fileArr = pathinfo(array_pop($fileChunks));
        if ($className === $fileArr['filename']){
            include $filePath;
        }
    }
}
