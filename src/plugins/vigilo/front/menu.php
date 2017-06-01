<?php

include(
    dirname(dirname(dirname(__DIR__))) .
    DIRECTORY_SEPARATOR . "inc" .
    DIRECTORY_SEPARATOR . "includes.php"
);

Session::checkRight("config", UPDATE);

Html::header(
    __('Vigilo', 'vigilo'),
    $_SERVER["PHP_SELF"],
    "plugins",
    "PluginVigiloMenu",
    "menu"
);

$res = null;
$pipes = array();

if (!empty($_POST["deploy"])) {
    $fds = array(
        1 => array("pipe", "w"),
        2 => array("pipe", "w"),
    );

    $debug  = empty($_POST['debug']) ? '' : '--debug';
    $force  = empty($_POST['force']) ? '' : '--force';
    $cmd    = "/usr/bin/sudo -n /usr/bin/vigiconf deploy $force $debug";
    $env    = array(
        "LC_ALL" => isset($_SESSION["glpilanguage"]) ? $_SESSION["glpilanguage"] : "en",
        "PATH" => getenv("PATH")
    );
    $res    = proc_open($cmd, $fds, $pipes, null, $env);

    if (!is_resource($res)) {
        $res = false;
    }
}
PluginVigiloMenu::displayMenu($res, $pipes);

Html::footer();
