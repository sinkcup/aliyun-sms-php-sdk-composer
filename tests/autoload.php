<?php
function autoload($className)
{
    if (strpos($className, '\\') !== false) {
        if (strpos($className, 'Aliyun') === 0) {
            require_once(__DIR__ . '/../src/' . str_replace('\\', '/', $className) . '.php');
        }
        return true;
    }
}
spl_autoload_register('autoload');
