<?php

function vigilo_autoloader($class_name){
  if(strstr($class_name,'Vigilo')){
    require_once __DIR__ . '/Vigilo/' . $class_name . '.php';
  }
}
