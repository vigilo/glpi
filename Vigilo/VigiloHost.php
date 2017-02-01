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
            if ($agent->getAgentWithComputerid($this->computer->getID()) !== false) {
                $this->agent = $agent;
            }
        }

        $this->selectTemplates();
        $this->selectGroups();
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
	$template_name = $this->computer->getField("template_name");

        if ($template_name && $template_name !== "N/A") {
            $this->children[] = new VigiloHostTemplate($this->computer->getField("template_name"));
        }
        $refs = array(
            "glpi_operatingsystems" => "operatingsystems_id",
            "glpi_operatingsystemversions" => "operatingsystemversions_id",
            "glpi_operatingsystemservicepacks" => "operatingsystemservicepacks_id",
        );

        $model = array();
        foreach ($refs as $table => $field) {
            $id = $this->computer->fields[$field];
            $value = Dropdown::getDropdownName($table, $id);
            if ($value !== "" && $value !== null && $value !== "&nbsp;"
                && $value !== false && $value !== "-----" && $value !== 'N/A'
            ) {
                $model[] = $value;
            }
        }

        if (!count($model)) {
            $model = "default";
        } else {
            $model = implode(" - ", $model);
        }

        $this->children[] = new VigiloHostTemplate($model);
    }

    protected function selectGroups()
    {
        $location = new Location();
        $location->getFromDB($this->computer->fields["locations_id"]);
        if (!($location->getName()=='N/A')) {
            $locationCompleteName=explode(" > ", $location->getField("completename"));
            $locationRealName=implode("/", $locationCompleteName);
            $this->children[] = new VigiloGroup($locationRealName);
        }

        $entity = new Entity();
        $entity->getFromDB($this->computer->fields["entities_id"]);
        if (!($entity->getName()=='N/A')) {
            $entityCompleteName=explode(" > ", $entity->getField("completename"));
            $entityRealName=implode("/", $entityCompleteName);
            $this->children[] = new VigiloGroup($entityRealName);
        }

        $manufacturer = new Manufacturer();
        $manufacturer->getFromDB($this->computer->fields["manufacturers_id"]);
        if (!($manufacturer->getName()=='N/A')) {
            $this->children[] = new VigiloGroup($manufacturer->getName());
        }
    }

    protected function selectAddress()
    {
        static $address = null;

        if ($address === null && $this->agent) {
            $addresses = $this->agent->getIPs();
            if (count($addresses)) {
                $address = current($addresses);
            }
        }

        if ($address === null) {
            $address = $this->computer->getName();
            foreach ($this->addresses as $addr) {
                if (!$addr->is_ipv4()) {
                    continue;
                }

                $textual = $addr->getTextual();
                if (is_string($textual)) {
                    $address = $textual;
                    break;
                }
            }
        }

        return $address;
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

        if ($total > 0) {
            $this->children[] = new VigiloTest('RAM');
        }
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
            $ethport = new NetworkPortEthernet();
            $port->getFromDB($np['id']);
            if ($port->getName() == 'lo') {
                continue;
            }

            $args   = array();
            $label  = isset($port->fields['comment']) ? $port->fields['comment'] : $port->getName();
            $ethport = $ethport->find('networkports_id=' . $np['id']);
            foreach ($ethport as $rowEthPort) {
                if ($rowEthPort['speed']) {
                    $args[] = new VigiloArg('max', $rowEthPort['speed']);
                    break;
                }
            }
            $args[] = new VigiloArg('label', $label);
            $args[] = new VigiloArg('ifname', $port->getName());
            $this->children[] = new VigiloTest('Interface', $args);

            // Retrieve all IP addresses associated with this interface.
            // This will be used later in selectAddress() to select
            // the most appropriate IP address to query this computer.
            foreach ($DB->query($query2) as $nn) {
                $query3 = IPAddress::getSQLRequestToSearchForItem("NetworkName", $nn['id']);
                foreach ($DB->query($query3) as $ip) {
                    $addr = new IPAddress();
                    if ($addr->getFromDB($ip['id'])) {
                        $this->addresses[] = $addr;
                    }
                }
            }
        }
    }

    protected function monitorSoftwares()
    {
        global $DB;
        $listOfTest=new VigiloTestSoftware($this->computer);
        $computerSoftwareVersion=new Computer_SoftwareVersion();
        $ids=$computerSoftwareVersion->find('computers_id=' . $this->computer->getID());
        foreach ($ids as $id) {
            if ($id['softwareversions_id']) {
                $softwareVersion=new SoftwareVersion();
                $ids2=$softwareVersion->find('id=' . $id['softwareversions_id']);
                foreach ($ids2 as $id2) {
                    if ($id2['softwares_id']) {
                        $software=new Software();
                        $software->getFromDB($id2['softwares_id']);
                        $listOfTest->addRelevantTestWith($software->getName());
                    }
                }
            }
        }
        foreach ($listOfTest->getTable() as $test) {
             $this->children[]=$test;
        }
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
            if (!empty($total)) {
                $args[] = new VigiloArg('max', $total * 1024 * 1024);
            }
            $this->children[] = new VigiloTest('Partition', $args);
        }
    }

    public function __toString()
    {
        $outXML=new DOMdocument();
        $outXML->preserveWhiteSpace=false;
        $outXML->formatOutput=true;
        $outXML->loadXML(
            self::sprintf(
                '<?xml version="1.0"?>' .
                '<host name="%s" address="%s" ventilation="%s">%s</host>',
                $this->computer->getName(),
                $this->selectAddress(),
                "Servers",
                $this->children
            )
        );
        return $outXML->saveXML();
    }
}
