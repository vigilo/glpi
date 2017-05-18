<?php

$confdir = getenv('VIGILO_CONFDIR', true);
if (!$confdir || !file_exists($confdir)) {
    $confdir = implode(DIRECTORY_SEPARATOR, array('', 'etc', 'vigilo', 'vigiconf', 'conf.d'));
}

require(__DIR__ . DIRECTORY_SEPARATOR . 'autoloader.php');
require(__DIR__ . DIRECTORY_SEPARATOR . 'inc' . DIRECTORY_SEPARATOR . 'vigilo.function.php');
require(__DIR__ . DIRECTORY_SEPARATOR . 'vigilo_hooks.php');

spl_autoload_register('vigilo_autoloader');
