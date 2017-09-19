<?php

class PluginVigiloTemplate extends CommonDBTM
{
    public function getTabNameForItem(CommonGLPI $item, $withtemplate = 0)
    {
        return 'Vigilo';
    }

    public static function showForm($params)
    {
        global $DB, $CFG_GLPI;

        $item = $params['item'];
        $options = $params['options'];

        if (!in_array($item::getType(), array('Computer', 'Printer', 'NetworkEquipment'))) {
            return;
        }

        if (array_key_exists('formfooter', $options) && false === $options['formfooter']) {
            return;
        }

        $opts = array(
            "name" => "vigilo_template",
            "value" => 0,
            "url" => $CFG_GLPI["root_doc"] . "/plugins/vigilo/ajax/getTemplates.php"
        );

        $id = $item->getID();
        $query = <<<SQL
SELECT `template`
FROM glpi_plugin_vigilo_template
WHERE `id` = $id;
SQL;
        $result = $DB->query($query);
        if ($result) {
            $tpl        = $DB->result($result, 0, "template");
            $templates  = static::getTemplates();
            $index      = array_search($tpl, $templates, true);
            if (false !== $index) {
                $opts['value']      = $index;
                $opts['emptylabel'] = $tpl;
            }
        }

        echo "<tr><th colspan='4'>Vigilo NMS</th></tr>";
        echo '<tr>';
        echo '<td><label for="vigilo_template">Vigilo Template</td>';
        echo '<td>';
        Dropdown::show(__CLASS__, $opts);
        echo '</td></tr>';
    }

    public static function filterFile($path)
    {
        $tpl_dir    = VIGILO_CONFDIR . DIRECTORY_SEPARATOR . 'hosttemplates';
        $pattern    = '#^(([^.][^/]+/)*[^.][^/]+\.xml)$#i';
        // FIXME : l'utilisation de substr()+strlen() n'est pas idéale ici.
        return preg_match($pattern, substr($path->getPathname(), strlen($tpl_dir)));
    }

    public static function getTemplates()
    {
        $tpl_dir    = VIGILO_CONFDIR . DIRECTORY_SEPARATOR . 'hosttemplates';
        $templates  = array();
        $pattern    = "/<template\\s+(?:.*?\\s+)?name=(['\"])(\w+)\\1>/";
        $dir_it     = new RecursiveDirectoryIterator($tpl_dir, RecursiveDirectoryIterator::SKIP_DOTS);
        $it_it      = new RecursiveIteratorIterator($dir_it);
        $files      = new CallbackFilterIterator($it_it, array(__CLASS__, 'filterFile'));

        foreach ($files as $file) {
            $file = $file->getPathname();

            if (!is_file($file)) {
                continue;
            }

            $ok = preg_match_all($pattern, file_get_contents($file), $matches);
            if (!$ok) {
                continue;
            }

            foreach ($matches[2] as $match) {
                $templates[] = $match;
            }
        }

        $templates = array_unique($templates);
        sort($templates, SORT_REGULAR);
        array_unshift($templates, '-----');
        return $templates;
    }

    public static function getAjaxTemplates()
    {
        $res = array();
        foreach (static::getTemplates() as $index => $tpl) {
            $res[] = array("id" => $index, "text" => $tpl);
        }
        return $res;
    }

    public static function getTemplateIndexForItem(CommonDBTM $item)
    {
        $tpl        = self::getTemplateNameForItem($item);
        if (null === $tpl) {
            return null;
        }

        $templates  = self::getTemplates();
        $index      = array_search($tpl, $templates, true);
        return (false !== $index ? $index : null);
    }

    public static function getTemplateNameForItem(CommonDBTM $item)
    {
        global $DB;

        $id = $item->getID();
        $query = <<<SQL
SELECT `template`
FROM glpi_plugin_vigilo_template
WHERE `id` = $id;
SQL;

        $result = $DB->query($query);
        if (!$result) {
            return null;
        }

        return $DB->result($result, 0, "template");
    }
}
