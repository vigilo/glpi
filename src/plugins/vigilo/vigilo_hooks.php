<?php

class VigiloHooks
{
    // Callbacks pour différents événements
    // concernant les équipements supportés.
    public function preItemUpdate($item)
    {
        global $DB;

        $id = $item->getID();
        $query = <<<SQL
SELECT `template`
FROM glpi_plugin_vigilo_template
WHERE `id` = $id;
SQL;

        $item->fields['vigilo_template'] = 0;
        $result = $DB->query($query);
        if ($result) {
            $tpl        = $DB->result($result, 0, "template");
            $templates  = PluginVigiloTemplate::getTemplates();
            $index      = array_search($tpl, $templates, true);
            if (false !== $index) {
                $item->fields['vigilo_template'] = $index;
            }
        }
    }

    public function itemAddOrUpdate($item)
    {
        global $DB;

        $templates  = PluginVigiloTemplate::getTemplates();
        $tplId      = (int) $item->input['vigilo_template'];

        if ($tplId > 0 && $tplId < count($templates)) {
            $id         = $item->getID();
            $template   = $DB->escape($templates[$tplId]);
            $query      = <<<SQL
INSERT INTO `glpi_plugin_vigilo_template`(`id`, `template`)
VALUES ($id, '$template')
ON DUPLICATE KEY UPDATE `template` = '$template';
SQL;
            $DB->query($query);
            $item->fields['vigilo_template'] = $templates[$tplId];
        } else {
            $item->fields['vigilo_template'] = null;
        }

        $this->update($item);
    }

    public function itemPurge($item)
    {
        global $DB;

        $id         = $item->getID();
        $query      = "DELETE FROM `glpi_plugin_vigilo_template` WHERE `id` = $id;";
        $DB->query($query);
        $this->unmonitor($item->getField('name'));
    }

    // Méthodes outils / annexes
    public function writeVigiloConfig($obj, $objtype)
    {
        $dirs       = array(VIGILO_CONFDIR, $objtype, "managed");
        $confdir    = implode(DIRECTORY_SEPARATOR, $dirs);
        $file       = $confdir . DIRECTORY_SEPARATOR . $obj->getName() . ".xml";

        if (!file_exists($confdir)) {
            mkdir($confdir, 0770, true);
        }

        $res = file_put_contents($file, $obj, LOCK_EX);
        if (false !== $res) {
            chgrp($file, "vigiconf");
            chmod($file, 0660);
        }
    }

    // Méthodes d'ajout / mise à jour / suppression de la supervision
    // pour un objet équipement supporté.
    public function delete($computer)
    {
        global $DB;

        $this->unmonitor($computer->fields["name"]);

        $query = "UPDATE `glpi_plugin_vigilo_config` SET `value` = 1 WHERE `key` = 'needs_deploy';";
        $DB->query($query);
    }

    public function update($item)
    {
        global $DB;

        if (isset($item->oldvalues["name"])) {
            $this->unmonitor($item->oldvalues["name"]);
        }

        $query = "UPDATE `glpi_plugin_vigilo_config` SET `value` = 1 WHERE `key` = 'needs_deploy';";
        $DB->query($query);

        if ($item->getField("is_template")) {
            return;
        }

        $cls = "PluginVigiloMonitored" . $item->getType();
        if (class_exists($cls, true)) {
            $obj = new $cls($item);
            $this->writeVigiloConfig($obj, "hosts");
        }
    }

    public function unmonitor($host)
    {
        $dirs = array(VIGILO_CONFDIR, "hosts", "managed", $host . ".xml");
        $filename = implode(DIRECTORY_SEPARATOR, $dirs);
        if (file_exists($filename)) {
            unlink($filename);
        }
    }

    // Méthodes de mise à jour d'un équipement
    // lorsque l'un de ses composants change.
    public function refreshSoftwareVersion($version)
    {
        $computer = new Computer();
        $computer->getFromDB($version->getField("computers_id"));
        $this->update($computer);
    }

    public function refreshSoftware($software)
    {
        $softwareVer    = new SoftwareVersion();
        $versions       = $softwareVer->find('softwares_id=' . $software->getID());
        foreach ($versions as $version) {
            if (!$version['id']) {
                continue;
            }

            $installations  = new Computer_SoftwareVersion();
            $filter         = 'softwareversions_id=' . $version['id'];
            $installations  = $installations->find($filter);
            foreach ($installations as $installation) {
                if (-1 === $installation['computers_id']) {
                    continue;
                }

                $computer = new Computer();
                $computer->getFromDB($installation['computers_id']);
                $this->update($computer);
            }
        }
    }

    public function refreshDisk($disk)
    {
        $id = $disk->getField('computers_id');
        $computer = new Computer();
        $computer->getFromDB($id);
        $this->update($computer);
    }

    public function refreshAddress($address)
    {
        $id         = $address->getField('mainitems_id');
        $itemtype   = $address->getField('mainitemtype');
        $item       = new $itemtype();
        $item->getFromDB($id);
        $this->update($item);
    }

    public function refreshDevice($device)
    {
        $id         = $device->getField('items_id');
        $itemtype   = $device->getField('itemtype');
        $item       = new $itemtype();
        $item->getFromDB($id);
        $this->update($item);
    }

    // Méthode de mise à jour en cas d'évolution de l'emplacement,
    // de l'entité ou du fabricant d'un équipement.
    public function updateGroups($obj)
    {
        $groups = new PluginVigiloGroups();
        $this->writeVigiloConfig($groups, "groups");
    }
}