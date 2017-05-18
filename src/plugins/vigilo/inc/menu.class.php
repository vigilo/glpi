<?php

class PluginVigiloMenu extends CommonGLPI
{
    const TIMEOUT = 30;

    /**
     * Name of the type
     *
     * @param $nb  integer  number of item in the type (default 0)
    **/
    public static function getTypeName($nb = 0)
    {
        return 'Vigilo';
    }

    public static function canView()
    {
        return true;
    }

    public static function canCreate()
    {
        return false;
    }

    public static function getMenuName()
    {
        return static::getTypeName();
    }

    public static function getAdditionalMenuOptions()
    {
        return array();
    }

    public static function getAdditionalMenuContent()
    {
        return array();
    }

    public static function displayMenu($res, $pipes)
    {
        global $DB;

        $disabled = '';
        if (!is_resource($res)) {
            $disabled = 'disabled';
        }

        echo <<<HTML
<h1>Vigilo</h1><form method="post" action="?itemtype=vigilo">
<textarea readonly='readonly' $disabled id='vigilo_deploy' style='display: block; width: 99%; height: 380px'>
HTML;

        $needs_deploy = false;
        $query = <<<SQL
SELECT `value`
FROM `glpi_plugin_vigilo_config`
WHERE `key` = 'needs_deploy';
SQL;

        $result = $DB->query($query);
        if ($result) {
            $needs_deploy = (int) $DB->result($result, 0, "value");
        }

        if (is_resource($res)) {
            ini_set("max_execution_time", 0);
            ignore_user_abort(true);
            set_time_limit(0);

            do {
                $read = $exc = $pipes;
                $write = array();

                $nb = stream_select($read, $write, $exc, static::TIMEOUT, 0);

                // Error
                if ($nb === false) {
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

                foreach ($read as $stream) {
                    echo htmlspecialchars(fread($stream, 1024), ENT_HTML5 | ENT_QUOTES, "utf-8");
                };

                flush();
                if (feof($pipes[1])) {
                    break;
                }
            } while (1);

            $info = proc_get_status($res);
            if ($info === false) {
                echo "ERROR: could not determine process status\n";
                $info = array('exitcode' => null);
            } else {
                if ($info["signaled"]) {
                    echo "Command terminated by signal ${info['termsig']}\n";
                }
                if ($info["stopped"]) {
                    echo "Command stopped by signal ${info['stopsig']}\n";
                }
                echo "Command exited with return code ${info['exitcode']}\n";
            }
            proc_close($res);

            if (isset($info['exitcode']) && 0 === $info['exitcode']) {
                $query = "UPDATE `glpi_plugin_vigilo_config` SET `value` = 0 WHERE `key` = 'needs_deploy';";
                $DB->query($query);
            }
        } elseif ($needs_deploy) {
            echo "Cliquez sur « Déployer la configuration » pour appliquer les modifications en attente.";
        } else {
            echo "La configuration est à jour.";
        }

        echo '</textarea>';
        echo '<button type="submit" name="deploy" value="1">Déployer la configuration</button> ';
        echo '<input name="debug" id="debug" value="1" type="checkbox"/> ';
        echo '<label for="debug">Afficher les informations de débogage</label>';
        Html::closeForm();
    }
}
