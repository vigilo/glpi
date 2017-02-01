<?php

require __DIR__ . DIRECTORY_SEPARATOR .'autoloader.php';
class VigiloHooks
{
    private $confdir;

    public function __construct($confdir = "/etc/vigilo/vigiconf/conf.d")
    {
        spl_autoload_register('vigilo_autoloader');
        $this->confdir = $confdir;
    }

    public function updateGroups()
    {
        $host       = new VigiloLocation();
        $dirs       = array($this->confdir, "groups", "managed");
        $confdir    = implode(DIRECTORY_SEPARATOR, $dirs);
        $file       = $confdir . DIRECTORY_SEPARATOR . "groups.xml";

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

    public function add($computer)
    {
        if ($computer->getField("is_template")==0) {
            $host       = new VigiloHost($computer);
            $dirs       = array($this->confdir, "hosts", "managed");
            $confdir    = implode(DIRECTORY_SEPARATOR, $dirs);
            $file     = $confdir . DIRECTORY_SEPARATOR . $host->getName() . ".xml";

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
    }

    public function delete($computer)
    {
        $this->unmonitor($computer->fields["name"]);
    }

    public function update($computer)
    {
	global $PLUGIN_HOOKS;
        if (isset($computer->oldvalues["name"])) {
            $this->unmonitor($computer->oldvalues["name"]);
        }

        $this->add($computer);
    }

    public function unmonitor($host)
    {
        $dirs = array($this->confdir, "hosts", "managed", $host . ".xml");
        unlink(implode(DIRECTORY_SEPARATOR, $dirs));
    }

    public function manageSoftwares($software)
    {
        global $DB;
        $softwareVer=new SoftwareVersion();
        $idSoftwareVersion=$softwareVer->find('softwares_id=' . $software->getID());
        foreach ($idSoftwareVersion as $idVersion) {
            if ($idVersion['id']) {
                $computerVer=new Computer_SoftwareVersion();
                $goodField='softwareversions_id=' . $idVersion['id'];
                $updateComp=$computerVer->find($goodField);
                foreach ($updateComp as $idComputer) {
                    if ($idComputer['computers_id'] != -1) {
                        $computer=new Computer();
                        $computer->getFromDB($idComputer['computers_id']);
                        $this->update($computer);
                    }
                }
            }
        }
    }

    public function manageDisks($disk)
    {
        global $DB;
        $id=$disk->getField('computers_id');
        $computer=new Computer();
        $computer->getFromDB($id);
        $this->update($computer);
    }

    public function manageAddresses($address)
    {
        global $DB;
        $id=$address->getField('mainitems_id');
        $comp=new Computer();
        $comp->getFromDB($id);
        $this->update($comp);
    }

    public function manageNetworks($network)
    {
        global $DB;
        ;
        $id=$network->getField('items_id');
        $comp=new Computer();
        $comp->getFromDB($id);
        $this->update($comp);
    }
}
