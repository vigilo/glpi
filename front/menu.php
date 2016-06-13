<?php

include ("../../../inc/includes.php");

if (PluginVigiloMenu::canView()) {
    Html::header(__('Vigilo', 'vigilo'), $_SERVER["PHP_SELF"], "plugins",
                    "PluginVigiloMenu", "menu");

    $res = null;
    if (!empty($_POST["deploy"])) {
        $fds = array(
            1 => array("pipe", "w"),
            2 => array("pipe", "w"),
        );
        $cmd = "/usr/bin/sudo -n /usr/bin/vigiconf deploy -f --debug 2>&1";
        $res = proc_open($cmd, $fds, $pipes);
        if (!is_resource($res))
            $res = false;
    }

    PluginVigiloMenu::displayMenu($res, $pipes);
} else {
    Html::displayRightError();
}

Html::footer();

