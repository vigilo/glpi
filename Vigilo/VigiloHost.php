<?php

class VigiloHost extends VigiloXml
{
    protected $computer;
    protected $addresses;
    protected $ventilation;
    protected $children;
    protected $agent;

    public function __construct($computer)
    {
        $this->agent        = null;
        $this->ventilation  = "Servers";
        $this->computer     = $computer;
        $this->addresses    = array();
        $this->children     = array();

        if (class_exists('PluginFusioninventoryAgent')) {
            $agent = new PluginFusioninventoryAgent();
            if ($agent->getAgentWithComputerid($this->computer->getID()) !== false)
                $this->agent = $agent;
        }

        $this->selectTemplates();
        $this->selectGroups();
        $this->monitorProcessor();
        $this->monitorMemory();
        $this->monitorNetworkInterfaces();
        $this->monitorSoftwares();
        $this->monitorPartitions();
    }

    public function getName()
    {
        return $this->computer->getName();
    }

    protected function selectTemplates()
    {
        $refs = array(
            "glpi_operatingsystems" => "operatingsystems_id",
            "glpi_operatingsystemversions" => "operatingsystemversions_id",
            "glpi_operatingsystemservicepacks" => "operatingsystemservicepacks_id",
        );

        $model = array();
        foreach ($refs as $table => $field) {
            $id = $this->computer->fields[$field];
            $value = Dropdown::getDropdownName($table, $id);
            if ($value !== "" && $value !== null && $value !== "&nbsp;" &&
                $value !== false && $value !== "-----") {
                $model[] = $value;
            }
        }

        if (!count($model))
            $model = "default";
        else
            $model = implode(" - ", $model);

        $this->children[] = new VigiloHostTemplate($model);
    }

    protected function selectGroups()
    {
        $location = new Location();
        $location->getFromDB($this->computer->fields["locations_id"]);
        $location = $location->getName();

        if (!$location)
            $location = "Servers";

        $this->children[] = new VigiloGroup($location);
    }

    protected function selectAddress()
    {
        static $address = null;

        if ($address === null && $this->agent) {
            $addresses = $this->agent->getIPs();
            if (count($addresses))
                $address = current($addresses);
        }

        if ($address === null) {
            $address = $this->computer->getName();
            foreach ($this->addresses as $addr) {
                if (!$addr->is_ipv4())
                    continue;

                $textual = $addr->getTextual();
                if (is_string($textual)) {
                    $address = $textual;
                    break;
                }
            }
        }

        return $address;
    }

    protected function monitorProcessor()
    {
        $this->children[] = new VigiloTest('CPU');
    }

    protected function monitorMemory()
    {
        global $DB;

        $total = 0;
        $query = Item_DeviceMemory::getSQLRequestToSearchForItem(
            $this->computer->getType(),
            $this->computer->getID()
        );

        foreach ($DB->query($query) as $mem) {
            $memory = new Item_DeviceMemory();
            $memory->getFromDB($mem['id']);
            $total += $memory->fields['size'] * 1024 * 1024;
        }

        if ($total > 0)
            $this->children[] = new VigiloTest('RAM');
    }

    protected function monitorNetworkInterfaces()
    {
        global $DB;

        $query = NetworkPort::getSQLRequestToSearchForItem(
            $this->computer->getType(),
            $this->computer->getID()
        );

        foreach ($DB->query($query) as $np) {
            $query2 = NetworkName::getSQLRequestToSearchForItem("NetworkPort", $np['id']);

            $port = new NetworkPort();
            $port->getFromDB($np['id']);
            if ($port->getName() == 'lo')
                continue;

            $args   = array();
            $label  = isset($port->fields['comment']) ? $port->fields['comment'] : $port->getName();
            $args[] = new VigiloArg('label', $label);
            $args[] = new VigiloArg('name', $port->getName());
            // TODO: retrieve interface speed (from glpi_networkportethernets)
            $this->children[] = new VigiloTest('Interface', $args);

            // Retrieve all IP addresses associated with this interface.
            // This will be used later in selectAddress() to select
            // the most appropriate IP address to query this computer.
            foreach ($DB->query($query2) as $nn) {
                $query3 = IPAddress::getSQLRequestToSearchForItem("NetworkName", $nn['id']);
                foreach ($DB->query($query3) as $ip) {
                    $addr = new IPAddress();
                    if ($addr->getFromDB($ip['id']))
                        $this->addresses[] = $addr;
                }
            }
        }
    }

    protected function monitorSoftwares()
    {
        
    }

    protected function monitorPartitions()
    {
        global $DB;

        $query = ComputerDisk::getSQLRequestToSearchForItem(
            $this->computer->getType(),
            $this->computer->getID()
        );

        foreach ($DB->query($query) as $cd) {
            $disk = new ComputerDisk();
            $disk->getFromDB($cd['id']);

            $args = array();
            $args[] = new VigiloArg('label', $disk->getName());
            $args[] = new VigiloArg('partname', $disk->fields['mountpoint']);
            $total = $disk->fields['totalsize'];
            if (!empty($total))
                $args[] = new VigiloArg('max', $total * 1024 * 1024);
            $this->children[] = new VigiloTest('Partition', $args);
        }
    }

    public function __toString()
    {
        $outXML=new DOMdocument();
      	$outXML->preserveWhiteSpace=false;
     	$outXML->formatOutput=true;
     	$outXML->loadXML(self::sprintf(
            '<?xml version="1.0"?>' .
            '<host name="%s" address="%s" ventilation="%s">%s</host>',
            $this->computer->getName(),
            $this->selectAddress(),
            "Servers",
            $this->children
	    ));
	return $outXML->saveXML();	
	
    }
}