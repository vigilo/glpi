<?php

if (!defined('GLPI_ROOT')) {
    die("Sorry. You can't access directly to this file");
}


class PluginVigiloMenu extends CommonGLPI
{
    const TIMEOUT = 30;

    /**
     * Name of the type
     *
     * @param $nb  integer  number of item in the type (default 0)
    **/
    static function getTypeName($nb=0) {
        return 'Vigilo';
    }

    static function canView() {
        return true;
    }

    static function canCreate() {
        return false;
    }

    static function getMenuName() {
        return self::getTypeName();
    }

    static function getAdditionalMenuOptions() {
        return array();
    }

    static function getAdditionalMenuContent() {
        return array();
    }

    static function displayMenu($res, $pipes) {
        echo '<h1>Vigilo</h1><form method="post" action="?itemtype=vigilo">';

        if (is_resource($res)) {
            ini_set("max_execution_time", 0);
            ignore_user_abort(true);
            set_time_limit(0);

            echo '<textarea readonly="readonly" id="vigilo_deploy" style="display: block; width: 99%; height: 280px">';
            do {
                $read = $exc = $pipes;
                $write = array();

                $nb = stream_select($read, $write, $exc, static::TIMEOUT, 0);

                // Error
                if ($nb === FALSE) {
                    echo "UNKNOWN ERROR\n";
                    break;
                }

                // Timeout
                if ($nb === 0) {
                    echo "ERROR: command timed out!\n";
                    break;
                }

                if (count($exc)) {
                    echo "UNKNOWN ERROR\n";
                    break;
                }

                foreach ($read as $stream)
                    echo htmlspecialchars(fread($stream, 1024), ENT_HTML5 | ENT_QUOTES, "utf-8");
                ob_flush();
                flush();

                if (feof($pipes[1]))
                    break;
            } while (1);

            $info = proc_get_status($res);
            if ($info === false) {
                echo "ERROR: could not determine process status\n";
            } else {
                if ($info["signaled"])
                    echo "Command terminated by signal ${info['termsig']}\n";
                if ($info["stopped"])
                    echo "Command stopped by signal ${info['stopsig']}\n";
                echo "Command exited with return code ${info['exitcode']}\n";
            }

            proc_close($res);
            echo '</textarea>';
        }

        echo '<button type="submit" name="deploy" value="1">Deploy</button>';
        Html::closeForm();
    }
}
