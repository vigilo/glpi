<?php

//require_once(__DIR__ . DIRECTORY_SEPARATOR . '');
require __DIR__ . '/autoloader.php';
class VigiloHooks
{
    private $confdir;

    public function __construct($confdir="/etc/vigilo/vigiconf/conf.d")
    {
        spl_autoload_register('vigilo_autoloader');
        $this->confdir = $confdir;
    }

    public function add($computer)
    {
        $host       = new VigiloHost($computer);
        $dirs       = array($this->confdir, "hosts", "managed");
        $confdir    = implode(DIRECTORY_SEPARATOR, $dirs);
        $file       = $confdir . DIRECTORY_SEPARATOR . $host->getName() . ".xml";

        mkdir($confdir, 0770, true);
        $acc = "";
        foreach ($dirs as $dir) {
            $acc .= DIRECTORY_SEPARATOR . $dir;
            chgrp($acc, "vigiconf");
        }

        $res = file_put_contents($file, $host, LOCK_EX);
        if ($res !== false) {
            chgrp($file, "vigiconf");
            chmod($file, 0660);
        }
    }

    public function delete($computer)
    {
        $this->_unmonitor($computer->fields["name"]);
    }

    public function update($computer)
    {
        if (isset($computer->oldvalues["name"])) {
            $this->_unmonitor($computer->oldvalues["name"]);
        }

        $this->add($computer);
    }

    public function _unmonitor($host)
    {
        $dirs = array($this->confdir, "hosts", "managed", $host . ".xml");
        unlink(implode(DIRECTORY_SEPARATOR, $dirs));
    }

    public function manageDisks($disk){
      
      global $DB;
      
      $query="SELECT computers_id FROM glpi_" . strtolower($disks->getType()) . "s WHERE id=" . $disks->getID() . ";";
      foreach($DB->query($query) as $id){
	$computer= new Computer();
	$computer->getFromDB($id['id']);
	$this->update($computer);
      }
    }

    public function manageNetworks($network){
      
      global $DB;

      $query="SELECT computers_id FROM glpi_computers WHERE networks_id=" . $network->getID() . ";";
      foreach($DB->query($query) as $id){
	$computer=new Computer();
	$computer->getFromDB($id['id']);
	$this->update($computer);
      }
    }
}

