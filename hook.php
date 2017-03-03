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

    public function addComputer($computer)
    {
        if ($computer->getField("is_template")==0) {
            global $DB;
            $template_id = PluginVigiloVigiloTemplate::getVigiloTemplateNameByID($computer->getField("vigilo_template"));

            if(!empty($template_id)) {
                $query = "UPDATE glpi_computers
                          SET vigilo_template = '" . PluginVigiloVigiloTemplate::getVigiloTemplateNameByID($computer->getField("vigilo_template")) .
                         "' WHERE id = " . $computer->getField("id") . ";";
                $DB->queryOrDie($query, "update vigilo_template field");
            }

            $query = "UPDATE glpi_computers
                      SET is_dynamic = ' 1
                      ' WHERE id = " . $computer->getField("id") . ";";
            $DB->queryOrDie($query, "update vigilo_template field");
            $host       = new VigiloHost($computer);
            $dirs       = array($this->confdir, "hosts", "managed");
            $confdir    = implode(DIRECTORY_SEPARATOR, $dirs);
            $file     = $confdir . DIRECTORY_SEPARATOR . $host->getName() . ".xml";

            if (!file_exists($confdir)) {
                mkdir($confdir, 0770, true);
            }

            $res = file_put_contents($file, $host, LOCK_EX);
            if ($res !== false) {
                chgrp($file, "vigiconf");
                chmod($file, 0660);
            }
        }
    }

    public function addNetworkEquipment($networkequipment)
    {
        if ($networkequipment->getField("is_template")==0) {
            global $DB;

            $host       = new VigiloNetworkEquipment($networkequipment);
            $dirs       = array($this->confdir, "hosts", "managed");
            $confdir    = implode(DIRECTORY_SEPARATOR, $dirs);
            $file       = $confdir . DIRECTORY_SEPARATOR . $host->getName() . ".xml";

            if (!file_exists($confdir)) {
                mkdir($confdir, 0770, true);
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
	global $PLUGIN_HOOKS, $DB;
        if (isset($computer->oldvalues["name"])) {
            $this->unmonitor($computer->oldvalues["name"]);
        }
    }

    public function updateComputer($computer)
    {
        $this->update($computer);
        $this->addComputer($computer);
    }

    public function updateNetworkEquipment($networkEquipment)
    {
        $this->update($networkEquipment);
        $this->addNetworkEquipment($networkEquipment);
    }

    public function unmonitor($host)
    {
        $dirs = array($this->confdir, "hosts", "managed", $host . ".xml");
        unlink(implode(DIRECTORY_SEPARATOR, $dirs));
    }

    public function manageComputerSoftwareVersion($computer_software_version)
    {
        global $DB;
        $computer=new Computer();
        $computer->getFromDB($computer_software_version->getField("computers_id"));
        $this->updateComputer($computer);
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
                        $this->updateComputer($computer);
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
        $this->updateComputer($computer);
    }

    public function manageAddresses($address)
    {
        global $DB;
        $id=$address->getField('mainitems_id');
        $comp=new Computer();
        $comp->getFromDB($id);
        $this->updateComputer($comp);
    }

    public function manageNetworks($network)
    {
        global $DB;
        $id=$network->getField('items_id');
        $itemtype = $network->getField('itemtype');
        if ($itemtype === 'Computer') {
            $comp=new Computer();
            $comp->getFromDB($id);
            $this->updateComputer($comp);
        }
        else if ($itemtype === 'NetworkEquipment') {
            $ne=new NetworkEquipment();
            $ne->getFromDB($id);
            $this->updateNetworkEquipment($ne);
        }
    }

    public function plugin_vigilo_getAddSearchOptions($itemtype)
    {
        $options = array();
        if ($itemtype == 'Computer' or $itemtype == 'PluginVigiloComputer')
        {
            $options['7007']['table']          = 'glpi_computers';
            $options['7007']['field']          = 'vigilo_template';
            $options['7007']['name']           = 'vigilo_template';
            $options['7007']['massiveaction']  = 'TRUE';
            $options['7007']['datatype']       = 'dropdown';
            return $options;
        }
    }
}
