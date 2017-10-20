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
        return Session::haveRight("config", UPDATE);
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

    protected static function escape($s)
    {
        return htmlspecialchars($s, ENT_XML1 | ENT_QUOTES, "utf-8");
    }

    public static function displayMenu($res, $pipes)
    {
        global $DB;

        $disabled = '';
        if (!is_resource($res)) {
            $disabled = 'disabled';
        }

        echo <<<HTML
<h1>Vigilo NMS</h1><form method="post" action="?itemtype=vigilo">
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
            echo __('Click on "Deploy the configuration" to apply changes.', "vigilo");
        } else {
            echo __("The configuration is already up-to-date.", "vigilo");
        }

        $force = empty($_POST['force']) ? '' : 'checked';
        $debug = empty($_POST['debug']) ? '' : 'checked';
        $debug_title = htmlspecialchars(
            __("Display debug and progress information for Vigilo", "vigilo"),
            ENT_XML1 | ENT_QUOTES,
            "utf-8"
        );
        $debug_label = self::escape(__("Display debug information", "vigilo"));
        $force_title = self::escape(__("Force a full deployment rather than an incremental one", "vigilo"));
        $force_label = self::escape(__("Regenerate all files", "vigilo"));
        $deploy_title = self::escape(__("Deploy the configuration", "vigilo"));
        echo <<<HTML
</textarea>

<button type="submit" name="deploy" value="1">$deploy_title</button>

<label for="debug"><input name="debug" id="debug" value="1"
  type="checkbox" $debug title="$debug_title"/> $debug_label</label>

<label for="force"><input name="force" id="force" value="1"
  type="checkbox" $force title="$force_title"/> $force_label</label>
HTML;
        Html::closeForm();
    }
}
