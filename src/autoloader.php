<?php

function vigilo_autoloader($class_name)
{
    if (!strncmp($class_name, 'Vigilo', 6)) {
        require_once(__DIR__ . DIRECTORY_SEPARATOR . 'Vigilo' . DIRECTORY_SEPARATOR . $class_name . '.php');
    }
}
