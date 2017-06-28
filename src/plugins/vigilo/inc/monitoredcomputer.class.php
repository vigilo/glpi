<?php

class PluginVigiloMonitoredComputer extends PluginVigiloAbstractMonitoredItem
{
    protected static $softwares = null;

    public function __construct(CommonDBTM $item)
    {
        if (null === static::$softwares) {
            // Chargement et validation de la liste des logiciels
            // supervisés automatiquement.
            $mapping    = plugin_vigilo_getSoftwareMapping();
            $softwares  = array();
            foreach ($mapping as $name => $test) {
                // Cas d'un test sans paramètres explicites.
                if (1 === count($test)) {
                    $test[] = array();
                }

                if (2 !== count($test) || !is_array($test[1])) {
                    Toolbox::logDebug("Invalid test definition for '$name'");
                } else {
                    $softwares[$name] = $test;
                }
            }
            static::$softwares = $softwares;
        }

        parent::__construct($item);
        $this->monitorMemory();
        $this->monitorPartitions();
        $this->monitorSoftwares();
    }

    protected function monitorMemory()
    {
        global $DB;

        $total = 0;
        $query = Item_DeviceMemory::getSQLRequestToSearchForItem(
            $this->item->getType(),
            $this->item->getID()
        );

        foreach ($DB->query($query) as $mem) {
            $memory = new Item_DeviceMemory();
            $memory->getFromDB($mem['id']);
            $total += $memory->fields['size'] * 1024 * 1024;
        }

        if ($total > 0) {
            $this->children[] = new VigiloTest('RAM');
        }
    }

    protected function monitorPartitions()
    {
        global $DB;

        $query = ComputerDisk::getSQLRequestToSearchForItem(
            $this->item->getType(),
            $this->item->getID()
        );

        foreach ($DB->query($query) as $cd) {
            $disk = new ComputerDisk();
            $disk->getFromDB($cd['id']);
            $total = $disk->fields['totalsize'];

            $this->children[] =
                        $test = new VigiloTest('Partition');
            $test['label']      = $disk->getName();
            $test['partname']   = self::escapeRegex($disk->fields['mountpoint']);
            if (!empty($total)) {
                $test[] = new VigiloArg('max', $total * 1024 * 1024);
            }
        }
    }

    protected function monitorSoftwares()
    {
        $installations = new Computer_SoftwareVersion();
        $installations = $installations->find('computers_id=' . $this->item->getID());
        foreach ($installations as $installation) {
            if (!$installation['softwareversions_id']) {
                continue;
            }

            $versions = new SoftwareVersion();
            $versions = $versions->find('id=' . $installation['softwareversions_id']);
            foreach ($versions as $version) {
                if (!$version['softwares_id']) {
                    continue;
                }

                $software = new Software();
                $software->getFromDB($version['softwares_id']);
                $lcname = strtolower($software->getName());
                if (isset(static::$softwares[$lcname])) {
                    // Gestion des logiciels supervisés automatiquement.
                    list($testName, $testArgs) = static::$softwares[$lcname];
                    $this->children[] = new VigiloTest($testName, $testArgs);
                } elseif (!strncmp($lcname, 'vigilo-test-', 12)) {
                    // Gestion des "faux logiciels".
                    $parts  = explode('-', $software->getName(), 4);
                    if (count($parts) < 3) {
                        continue;
                    }

                    $type   = ucfirst(strtolower($parts[2]));
                    $args   = isset($parts[3]) ? $parts[3] : null;
                    $method = 'monitorCustom' . $type;
                    if (method_exists($this, $method)) {
                        $this->$method($software, $args);
                    }
                }
            }
        }
    }

    protected function monitorCustomService($software, $service)
    {
        if (!$service) {
            return;
        }

        $this->children[] = new VigiloTest('Service', array('svcname' => $service));
    }

    protected function monitorCustomTcp($software, $port)
    {
        if (!$port) {
            return;
        }

        $port = (int) $port;
        if ($port > 0 && $port <= 65535) {
            $this->children[] = new Vigilotest('TCP', array('port' => $port));
        }
    }

    protected function monitorCustomProcess($software, $process)
    {
        if (!$process) {
            return;
        }

        $this->children[] = new VigiloTest('Process', array('processname' => $process));
    }

    protected function monitorCustomSwap($software, $dummy)
    {
        $this->children[] = new VigiloTest('Swap');
    }

    protected function monitorCustomPing($software, $dummy)
    {
        $this->children[] = new VigiloTest('Ping');
    }
}
