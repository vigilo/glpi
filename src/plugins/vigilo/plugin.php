<?php

/* GLPI 9.1.2+ est nécessaire pour disposer du hook "post_show_item". */
define('VIGILO_MIN_GLPI_VERSION', '9.1.2');

function plugin_init_vigilo()
{
    global $PLUGIN_HOOKS;
    global $DB;

    $hooks      =& $PLUGIN_HOOKS;
    $p          = "vigilo";
    $hookObj    = new VigiloHooks();

    $hooks['csrf_compliant'][$p]    = true;

    foreach (array("Computer", "Printer", "NetworkEquipment") as $itemtype) {
        $hooks['pre_item_update'][$p][$itemtype]    = array($hookObj, "preItemUpdate");
        $hooks['item_add'][$p][$itemtype]           = array($hookObj, "itemAddOrUpdate");
        $hooks['item_update'][$p][$itemtype]        = array($hookObj, "itemAddOrUpdate");
        $hooks['item_restore'][$p][$itemtype]       = array($hookObj, "itemAddOrUpdate");
        $hooks['item_delete'][$p][$itemtype]        = array($hookObj, "itemPurge");
        $hooks['item_purge'][$p][$itemtype]         = array($hookObj, "itemPurge");
    }

    $events = array('item_add', 'item_update', 'item_purge', 'item_delete', 'item_restore');
    foreach ($events as $event) {
        $hooks[$event][$p] += array(
            "IPAddress"                 => array($hookObj, "refreshAddress"),
            "ComputerDisk"              => array($hookObj, "refreshDisk"),
            "NetworkPort"               => array($hookObj, "refreshDevice"),
            "DeviceProcessor"           => array($hookObj, "refreshDevice"),
            "DeviceMemory"              => array($hookObj, "refreshDevice"),
            "DeviceHardDrive"           => array($hookObj, "refreshDevice"),
            "DeviceControl"             => array($hookObj, "refreshDevice"),
            "DeviceSoundCard"           => array($hookObj, "refreshDevice"),
            "Software"                  => array($hookObj, "refreshSoftware"),
            "Computer_SoftwareVersion"  => array($hookObj, "refreshSoftwareVersion"),
            "Location"                  => array($hookObj, "updateGroups"),
            "Entity"                    => array($hookObj, "updateGroups"),
            "Manufacturer"              => array($hookObj, "updateGroups"),
        );
    }

    $hooks["menu_toadd"][$p]['plugins'] = 'PluginVigiloMenu';
    $hooks['config_page'][$p]           = 'front/menu.php';
    $hooks['post_item_form'][$p]        = array('PluginVigiloTemplate', 'showForm');
}

function plugin_version_vigilo()
{
    return array('name'           => 'Vigilo monitoring',
                 'version'        => trim(file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . 'VERSION.txt')),
                 'author'         => 'CSSI',
                 'license'        => 'GPLv2+',
                 'homepage'       => 'http://vigilo-nms.org',
                 'minGlpiVersion' => VIGILO_MIN_GLPI_VERSION);
}

function plugin_vigilo_check_config($verbose = false)
{
    if (version_compare(GLPI_VERSION, VIGILO_MIN_GLPI_VERSION, 'lt')) {
        echo "This plugin requires GLPI >= " . VIGILO_MIN_GLPI_VERSION;
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
    global $DB;

    if (!TableExists('glpi_plugin_vigilo_template')) {
        $query = <<<SQL
CREATE TABLE `glpi_plugin_vigilo_template` (
    `id` int(11) NOT NULL default '0',
    `template` varchar(255) collate utf8_unicode_ci default NULL,
    PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
SQL;
        $DB->query($query) or die($DB->error());
    }

    if (!TableExists('glpi_plugin_vigilo_config')) {
        $query = <<<SQL
CREATE TABLE `glpi_plugin_vigilo_config` (
    `key` varchar(255) collate utf8_unicode_ci NOT NULL,
    `value` varchar(255) collate utf8_unicode_ci NULL,
    PRIMARY KEY (`key`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
SQL;
        $DB->query($query) or die($DB->error());

        $query = "INSERT INTO `glpi_plugin_vigilo_config` VALUES('needs_deploy', 0);";
        $DB->query($query) or die($DB->error());
    }

    return true;
}

function plugin_vigilo_uninstall()
{
    global $DB;

    foreach (array('template', 'deployment') as $table) {
        $DB->query("DROP TABLE IF EXISTS `glpi_plugin_vigilo_$table`;");
    }

    return true;
}

// @codingStandardsIgnoreStart
function plugin_vigilo_getAddSearchOptions($itemtype)
{
    // Le nom de la méthode est imposé par GLPI.
    // @codingStandardsIgnoreEnd
    $options = array();

    if (!in_array($itemtype, array('Computer', 'NetworkEquipment', 'Printer'))) {
        return $options;
    }

    $options[7007]['table']           = 'glpi_plugin_vigilo_template';
    $options[7007]['field']           = 'template';
    $options[7007]['linkfield']       = 'id';
    $options[7007]['name']            = 'Template Vigilo';
    $options[7007]['massiveaction']   = true;
    $options[7007]['datatype']        = 'dropdown';

    return $options;
}
