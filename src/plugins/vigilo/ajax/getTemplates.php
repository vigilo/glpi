<?php

include(dirname(dirname(dirname(__DIR__))) .
        DIRECTORY_SEPARATOR . "inc" .
        DIRECTORY_SEPARATOR . "includes.php");

header("Content-Type: application/json; charset=UTF-8");
Html::header_nocache();

$templates = PluginVigiloTemplate::getAjaxTemplates();
echo json_encode(
    array(
        'count'     => count($templates),
        'results'   => $templates,
    )
);
