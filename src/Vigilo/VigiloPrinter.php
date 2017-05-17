<?php

class VigiloPrinter extends VigiloXml
{
    protected $printer;
    protected $addresses;
    protected $ventilation;
    protected $children;
    protected $agent;

    public function __construct($printer)
    {
        $this->agent        = null;
        $this->ventilation  = "Servers";
        $this->network      = $printer;
        $this->addresses    = array();
        $this->children     = array();

        if (class_exists('PluginFusioninventoryAgent')) {
            $agent = new PluginFusioninventoryAgent();
            if ($agent->getAgentWithComputerid($this->network->getID()) !== false) {
                $this->agent = $agent;
            }
        }

        $this->selectTemplates();
        $this->selectGroups();
        $this->monitorNetworkInterfaces();
    }

    public function getName()
    {
        return $this->network->getName();
    }

    protected function selectTemplates()
    {
        $template_name = $this->network->getField("template_name");

        if ($template_name !== "N/A") {
            $this->children[] = new VigiloHostTemplate($this->network->getField("template_name"));
        }
    }

    protected function selectGroups()
    {
        $location = new Location();
        $location->getFromDB($this->network->fields["locations_id"]);
        if ('N/A' !== $location->getName()) {
            $locationCompleteName   = explode(" > ", $location->getField("completename"));
            $locationRealName       = implode("/", $locationCompleteName);
            $this->children[]       = new VigiloGroup($locationRealName);
        }

        $entity = new Entity();
        $entity->getFromDB($this->network->fields["entities_id"]);
        if ('N/A' !== $entity->getName()) {
            $entityCompleteName = explode(" > ", $entity->getField("completename"));
            $entityRealName     = implode("/", $entityCompleteName);
            $this->children[]   = new VigiloGroup($entityRealName);
        }

        $manufacturer = new Manufacturer();
        $manufacturer->getFromDB($this->network->fields["manufacturers_id"]);
        if ('N/A' !== $manufacturer->getName()) {
            $this->children[] = new VigiloGroup($manufacturer->getName());
        }
    }

    protected function selectAddress()
    {
        static $address = null;

        if (null === $address && $this->agent) {
            $addresses = $this->agent->getIPs();
            if (count($addresses)) {
                $address = current($addresses);
            }
        }

        if (null === $address) {
            $address = $this->network->getName();
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

    protected function monitorNetworkInterfaces()
    {
        global $DB;
        $query = NetworkPort::getSQLRequestToSearchForItem(
            $this->network->getType(),
            $this->network->getID()
        );

        foreach ($DB->query($query) as $np) {
            $query2 = NetworkName::getSQLRequestToSearchForItem("NetworkPort", $np['id']);
            $port = new NetworkPort();
            $ethport = new NetworkPortEthernet();
            $port->getFromDB($np['id']);
            if ($port->getName() == 'lo') {
                continue;
            }

            $args       = array();
            $label      = isset($port->fields['comment']) ? $port->fields['comment'] : $port->getName();
            $ethport    = $ethport->find('networkports_id=' . $np['id']);
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
            // the most appropriate IP address to query this network.
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

    public function __toString()
    {
        $outXML = new DOMDocument();
        $outXML->preserveWhiteSpace = false;
        $outXML->formatOutput       = true;
        $outXML->loadXML(
            self::sprintf(
                '<?xml version="1.0"?>' .
                '<host name="%s" address="%s" ventilation="%s">%s</host>',
                $this->network->getName(),
                $this->selectAddress(),
                "Servers",
                $this->children
            )
        );
        return $outXML->saveXML();
    }
}
