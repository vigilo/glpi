<?php

class PluginVigiloVigiloTemplate extends CommonDBTM
{
    public static function getAllTemplates()
    {
        $hosttdir = VIGILO_CONFDIR . DIRECTORY_SEPARATOR . 'hosttemplates');
        $hosttemplates_files = scandir($hosttdir);
        $templates = array();
        $pattern = "<template name=\"(\w*)\">";

        foreach ($hosttemplates_files as $file) {
            $filepath = $hosttdir . DIRECTORY_SEPARATOR . $file;
            $test_filepath = preg_match("/(.*).xml$/", $filepath);

            if (is_file($filepath) && !empty($test_filepath)) {
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

    public static function getAjaxArrayTemplates()
    {
        $templates = PluginVigiloVigiloTemplate::getAllTemplates();
        $id = 0;
        $ret = array();

        foreach ($templates as $t) {
            $ret[] = array("id" => $id, "text" => $t);
            $id++;
        }

        return $ret;
    }

    public static function getVigiloTemplateNameByID($id)
    {
        if (is_numeric($id)) {
            if ($id === '0') {
                return "NULL";
            }
            $templates = PluginVigiloVigiloTemplate::getAllTemplates();
            return $templates[$id];
        }
    }
}
