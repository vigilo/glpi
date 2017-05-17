<?php

require(__DIR__ . DIRECTORY_SEPARATOR . 'autoloader.php');
require(__DIR__ . DIRECTORY_SEPARATOR . 'Vigilo' . DIRECTORY_SEPARATOR . 'VigiloSoftwareList.php');
require(__DIR__ . DIRECTORY_SEPARATOR . 'vigilo_hooks.php');

$confdir = getenv('VIGILO_CONFDIR', true);
if (!$confdir || !file_exists($confdir)) {
    $confdir = implode(DIRECTORY_SEPARATOR, array('etc', 'vigilo', 'vigiconf', 'conf.d'));
}
define('VIGILO_CONFDIR', $confdir);
spl_autoload_register('vigilo_autoloader');
