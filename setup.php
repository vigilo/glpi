<?php

require __DIR__ . "/hook.php";

function plugin_init_vigilo()
{
    global $PLUGIN_HOOKS;
    $hooks      =& $PLUGIN_HOOKS;
    $p          =  "vigilo";
    $hookObj    =  new VigiloHooks();
    $hooks['csrf_compliant'][$p]        = true;
    $hooks['item_add'][$p]              = array("Computer" => array($hookObj, "add"),
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
    $hooks['item_update'][$p]           = array("Computer" => array($hookObj, "update"),
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
    $hooks['item_restore'][$p]          = array("Computer" => array($hookObj, "add"),
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
