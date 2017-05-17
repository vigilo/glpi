<?php

if (strpos(filter_input(INPUT_SERVER, "PHP_SELF"), "getVTValue.php")) {
  include(dirname(dirname(__DIR__)) .
          DIRECTORY_SEPARATOR . "inc" .
          DIRECTORY_SEPARATOR . "includes.php");

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

$ret['count'] = count($ret['results']);

echo json_encode($ret);

?>
