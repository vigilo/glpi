<?php

if (strpos(filter_input(INPUT_SERVER, "PHP_SELF"), "getVTValue.php")) {
   include ("../../../inc/includes.php");
   header("Content-Type: text/html; charset=UTF-8");
   Html::header_nocache();
}
if (!defined('GLPI_ROOT')) {
   die("Can not acces directly to this file");
}

$ret = array();
$tmp = PluginVigiloVigiloTemplate::getAjaxArrayTemplates();

foreach ($tmp as $template) {
    $ret['results'][] = $template;
}

$ret['count']   = count($ret['results']);

/*$ret['results'][] = array("id" => 0, "text" => "-----");
$ret['results'][] = array("id" => 1, "text" => "linux");
$ret['results'][] = array("id" => 2, "text" => "windows");
*/
echo json_encode($ret);

?>
