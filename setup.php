<?php

require __DIR__ . "/hook.php";

function plugin_init_vigilo()
{
    global $PLUGIN_HOOKS;
    global $DB;
    $hooks      =& $PLUGIN_HOOKS;
    $p          =  "vigilo";
    $hookObj    =  new VigiloHooks();
    $hooks['csrf_compliant'][$p]        = true;
    $hooks['item_add'][$p]              = array("Computer" => array($hookObj, "addComputer"),
                                                "NetworkEquipment" => array($hookObj, "addNetworkEquipment"),
                                                "Printer" => array($hookObj, "addPrinter"),
                                                "ComputerDisk" => array($hookObj,"manageDisks"),
                                                "NetworkPort" => array($hookObj,"manageNetworks"),
                                                "IPAddress" => array($hookObj,"manageAddresses"),
                                                "DeviceProcessor" => array($hookObj,"manageNetworks"),
                                                "DeviceMemory" => array($hookObj,"manageNetworks"),
                                                "DeviceHardDrive" => array($hookObj,"manageNetworks"),
                                                "DeviceControl" => array($hookObj,"manageNetworks"),
                                                "DeviceSoundCard" => array($hookObj,"manageNetworks"),
                                                "Software" => array($hookObj,"manageSoftwares"),
                                                "Location" => array($hookObj,"updateGroups"),
                                                "Entity" => array($hookObj,"updateGroups"),
                                                "Computer_SoftwareVersion" => array($hookObj, "manageComputerSoftwareVersion"),
                                                "Manufacturer" => array($hookObj,"updateGroups"));
    $hooks['item_update'][$p]           = array("Computer" => array($hookObj, "updateComputer"),
                                                "NetworkEquipment" => array($hookObj, "updateNetworkEquipment"),
                                                "Printer" => array($hookObj, "updatePrinter"),
                                                "ComputerDisk" => array($hookObj,"manageDisks"),
                                                "NetworkPort" => array($hookObj,"manageNetworks"),
                                                "IPAddress" => array($hookObj,"manageAddresses"),
                                                "DeviceProcessor" => array($hookObj,"manageNetworks"),
                                                "DeviceMemory" => array($hookObj,"manageNetworks"),
                                                "DeviceHardDrive" => array($hookObj,"manageNetworks"),
                                                "DeviceControl" => array($hookObj,"manageNetworks"),
                                                "DeviceSoundCard" => array($hookObj,"manageNetworks"),
                                                "Software" => array($hookObj,"manageSoftwares"),
                                                "Location" => array($hookObj,"updateGroups"),
                                                "Computer_SoftwareVersion" => array($hookObj, "manageComputerSoftwareVersion"),
                                                "Entity" => array($hookObj,"updateGroups"),
                                                "Manufacturer" => array($hookObj,"updateGroups"));
    $hooks['item_purge'][$p]            = array("Computer" => array($hookObj, "delete"),
                                                "NetworkEquipment" => array($hookObj, "delete"),
                                                "Printer" => array($hookObj, "delete"),
                                                "ComputerDisk" => array($hookObj,"manageDisks"),
                                                "NetworkPort" => array($hookObj,"manageNetworks"),
                                                "IPAddress" => array($hookObj,"manageAddresses"),
                                                "DeviceProcessor" => array($hookObj,"manageNetworks"),
                                                "DeviceMemory" => array($hookObj,"manageNetworks"),
                                                "DeviceHardDrive" => array($hookObj,"manageNetworks"),
                                                "DeviceControl" => array($hookObj,"manageNetworks"),
                                                "DeviceSoundCard" => array($hookObj,"manageNetworks"),
                                                "Software" => array($hookObj,"manageSoftwares"),
                                                "Location" => array($hookObj,"updateGroups"),
                                                "Computer_SoftwareVersion" => array($hookObj, "manageComputerSoftwareVersion"),
                                                "Entity" => array($hookObj,"updateGroups"),
                                                "Manufacturer" => array($hookObj,"updateGroups"));
    $hooks['item_delete'][$p]           = array("Computer" => array($hookObj, "delete"),
                                                "NetworkEquipment" => array($hookObj, "delete"),
                                                "Printer" => array($hookObj, "delete"),
                                                "ComputerDisk" => array($hookObj,"manageDisks"),
                                                "NetworkPort" => array($hookObj,"manageNetworks"),
                                                "IPAddress" => array($hookObj,"manageAddresses"),
                                                "DeviceProcessor" => array($hookObj,"manageNetworks"),
                                                "DeviceMemory" => array($hookObj,"manageNetworks"),
                                                "DeviceHardDrive" => array($hookObj,"manageNetworks"),
                                                "DeviceControl" => array($hookObj,"manageNetworks"),
                                                "DeviceSoundCard" => array($hookObj,"manageNetworks"),
                                                "Software" => array($hookObj,"manageSoftwares"),
                                                "Location" => array($hookObj,"updateGroups"),
                                                "Computer_SoftwareVersion" => array($hookObj, "manageComputerSoftwareVersion"),
                                                "Entity" => array($hookObj,"updateGroups"),
                                                "Manufacturer" => array($hookObj,"updateGroups"));
    $hooks['item_restore'][$p]          = array("Computer" => array($hookObj, "addComputer"),
                                                "NetworkEquipment" => array($hookObj, "addNetworkEquipment"),
                                                "Printer" => array($hookObj, "addPrinter"),
                                                "ComputerDisk" => array($hookObj,"manageDisks"),
                                                "NetworkPort" => array($hookObj,"manageNetworks"),
                                                "IPAddress" => array($hookObj,"manageAddresses"),
                                                "DeviceProcessor" => array($hookObj,"manageNetworks"),
                                                "DeviceMemory" => array($hookObj,"manageNetworks"),
                                                "DeviceHardDrive" => array($hookObj,"manageNetworks"),
                                                "DeviceControl" => array($hookObj,"manageNetworks"),
                                                "DeviceSoundCard" => array($hookObj,"manageNetworks"),
                                                "Software" => array($hookObj,"manageSoftwares"),
                                                "Location" => array($hookObj,"updateGroups"),
                                                "Computer_SoftwareVersion" => array($hookObj, "manageComputerSoftwareVersion"),
                                                "Entity" => array($hookObj,"updateGroups"),
                                                "Manufacturer" => array($hookObj,"updateGroups"));
    $hooks["menu_toadd"][$p]['plugins'] = 'PluginVigiloMenu';
    $hooks['config_page'][$p]           = 'front/menu.php?itemtype=vigilo';
    $hooks['autoinventory_information'][$p] = array(
            'Computer' =>  array('PluginVigiloComputer',
                                 'showComputerInfo'));

    if (!FieldExists('glpi_computers', 'vigilo_template'))
    {
       $query = "ALTER TABLE glpi_computers ADD vigilo_template VARCHAR(30)";
       $DB->queryOrDie($query, "Ajout d'une colonne vigilo_template dans la table glpi_computers");
    }
}

function getSearchOptions() {
    $computer = new Computer();
    $options  = $computer->getSearchOptions();

    $options['vigilo']             = 'Vigilo Template';

    $options['7007']['name']       = 'vigilo_template';
    $options['7007']['table']      = 'glpi_computers';
    $options['7007']['field']      = 'vigilo_template';
    $options['7007']['searchtype'] = 'equals';
    $options['7007']['datatype']   = 'dropdown';

    return $options;
}

function plugin_version_vigilo()
{
    return array('name'           => 'Vigilo monitoring',
                'version'        => '0.1',
                'author'         => 'CSSI',
                'license'        => 'GPLv2+',
                'homepage'       => 'http://vigilo-nms.org',
                'minGlpiVersion' => '9.1');
}

function plugin_vigilo_check_config($verbose = false)
{
    if (version_compare(GLPI_VERSION, '9.1', 'lt')) {
        echo "This plugin requires GLPI >= 9.1";
        return false;
    }
    return true;
}

function plugin_vigilo_check_prerequisites()
{
    return true;
}

function plugin_vigilo_install()
{
    return true;
}

function plugin_vigilo_uninstall()
{
    return true;
}
