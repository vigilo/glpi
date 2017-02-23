<?php

if (!defined('GLPI_ROOT')) {
    die("Sorry. You can't access directly to this file");
}

class PluginVigiloVigiloTemplate extends CommonDBTM {
    static function getAllTemplates() {
        $hosttdir = "/etc/vigilo/vigiconf/conf.d/hosttemplates";
        $hosttemplates_files = scandir($hosttdir);
        $templates = array();
        $pattern = "<template name=\"(\w*)\">";

        foreach($hosttemplates_files as $file) {
            $filepath = $hosttdir . DIRECTORY_SEPARATOR . $file;
            $test_filepath = preg_match("/(.*).xml$/", $filepath);
            if (is_file($filepath) AND !empty($test_filepath)) {
                preg_match_all($pattern, file_get_contents($filepath), $matches);
                foreach ($matches[1] as $match) {
                    $templates[] = $match;
                }
            }
        }

        sort($templates, SORT_STRING);
        array_unshift($templates, '-----');
        return $templates;
    }

    static function getVigiloTemplateNameByID($id) {

        if (is_numeric($id)) {
            if ($id === '0') return "NULL";
            $templates = PluginVigiloVigiloTemplate::getAllTemplates();
            return $templates[$id];
        }
    }
}
