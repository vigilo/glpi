<?php

class VigiloHooks
{
    private $confdir;

    public function __construct($confdir = "/etc/vigilo/vigiconf/conf.d")
    {
        spl_autoload_register('vigilo_autoloader');
        $this->confdir = $confdir;
    }

    public function saveHost($host, $dir_type)
    {
        $dirs       = array($this->confdir, $dir_type, "managed");
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

    public function updateGroups()
    {
        $host       = new VigiloLocation();
        $this->saveHost($host, "groups");
    }

    public function addComputer($computer)
    {
        global $DB;

        if ($computer->getField("is_template") == 0) {
            $template_id = PluginVigiloVigiloTemplate::getVigiloTemplateNameByID(
                $computer->getField("vigilo_template")
            );

            if (!empty($template_id)) {
                $query = "UPDATE glpi_computers
                          SET vigilo_template = '" . $template_id .
                         "' WHERE id = " . $computer->getField("id") . ";";
                $DB->queryOrDie($query, "update vigilo_template field");
            }

            $query = "UPDATE glpi_computers
                      SET is_dynamic = ' 1
                      ' WHERE id = " . $computer->getField("id") . ";";
            $DB->queryOrDie($query, "update vigilo_template field");

            $host = new VigiloHost($computer);
            $this->saveHost($host, "hosts");
        }
    }

    public function addNetworkEquipment($networkequipment)
    {
        if ($networkequipment->getField("is_template") == 0) {
            global $DB;

            $host = new VigiloNetworkEquipment($networkequipment);
            $this->saveHost($host, "hosts");
        }
    }

    public function addPrinter($printer)
    {
        if ($printer->getField("is_template") == 0) {
            global $DB;

            $host = new VigiloPrinter($printer);
            $this->saveHost($host, "hosts");
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

    public function updatePrinter($printer)
    {
        $this->update($printer);
        $this->addPrinter($printer);
    }

    public function unmonitor($host)
    {
        $dirs = array($this->confdir, "hosts", "managed", $host . ".xml");
        $filename = implode(DIRECTORY_SEPARATOR, $dirs);
        if (file_exists($filename)) {
            unlink($filename);
        }
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
        $id = $address->getField('mainitems_id');
        $comp = new Computer();
        $comp->getFromDB($id);
        $this->updateComputer($comp);
    }

    public function manageNetworks($network)
    {
        global $DB;
        $id = $network->getField('items_id');
        $itemtype = $network->getField('itemtype');
        if ($itemtype === 'Computer') {
            $comp = new Computer();
            $comp->getFromDB($id);
            $this->updateComputer($comp);
        } elseif ($itemtype === 'NetworkEquipment') {
            $ne = new NetworkEquipment();
            $ne->getFromDB($id);
            $this->updateNetworkEquipment($ne);
        } elseif ($itemtype === 'Printer') {
            $printer = new Printer();
            $printer->getFromDB($id);
            $this->updatePrinter($printer);
        }
    }

    // @codingStandardsIgnoreStart
    public function plugin_vigilo_getAddSearchOptions($itemtype)
    {
        // Le nom de la méthode est imposé par GLPI.
        // @codingStandardsIgnoreEnd
        $options = array();
        if ($itemtype == 'Computer' || $itemtype == 'PluginVigiloComputer') {
            $options['7007']['table']          = 'glpi_computers';
            $options['7007']['field']          = 'vigilo_template';
            $options['7007']['name']           = 'vigilo_template';
            $options['7007']['massiveaction']  = 'TRUE';
            $options['7007']['datatype']       = 'dropdown';
        }
        return $options;
    }
}
