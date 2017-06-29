<?php

abstract class PluginVigiloAbstractMonitoredItem extends VigiloXml
{
    protected $item;
    protected $addresses;
    protected $ventilation;
    protected $children;
    protected $agent;

    public function __construct(CommonDBTM $item)
    {
        $this->agent        = null;
        $this->item         = $item;
        $this->ventilation  = "Servers";
        $this->addresses    = array();
        $this->children     = array();

        if (class_exists('PluginFusioninventoryAgent')) {
            $agent = new PluginFusioninventoryAgent();
            if ($agent->getAgentWithComputerid($item->getID()) !== false) {
                $this->agent = $agent;
            }
        }

        $this->selectTemplates();
        $this->selectGroups();
        $this->monitorNetworkInterfaces();
    }

    public function getName()
    {
        return $this->item->getName();
    }

    public function filterTests($value)
    {
        return is_object($value) && ($value instanceof VigiloTest);
    }

    public function getTests()
    {
        return new CallbackFilterIterator(
            new ArrayIterator($this->children),
            array($this, 'filterTests')
        );
    }

    protected static function escapeRegex($regex)
    {
        $res = preg_quote($regex);
        $res = preg_replace("/[\x80-\xFF]+/", '.+', $res);
        return $res;
    }

    protected function selectTemplates()
    {
        $template = PluginVigiloTemplate::getTemplateNameForItem($this->item);
        if (null !== $template) {
            $this->children[] = new VigiloTemplate($template);
        }
    }

    protected function selectGroups()
    {
        $location = new Location();
        $location->getFromDB($this->item->fields["locations_id"]);

        $entity = new Entity();
        $entity->getFromDB($this->item->fields["entities_id"]);

        // Association de l'objet à son emplacement et à son entité.
        $candidates = array(
            'Locations' => $location,
            'Entities'  => $entity,
        );
        foreach ($candidates as $type => $candidate) {
            if (NOT_AVAILABLE === $candidate->getName()) {
                continue;
            }

            $completeName       = explode(" > ", $candidate->getField("completename"));
            // Ajout de "/" et de l'origine pour avoir le chemin complet.
            array_unshift($completeName, $type);
            array_unshift($completeName, "");
            $groupName          = implode("/", $completeName);
            $this->children[]   = new VigiloGroup($groupName);
        }

        // Association de l'objet à son équipementier.
        $manufacturer = new Manufacturer();
        $manufacturer->getFromDB($this->item->fields["manufacturers_id"]);
        if (NOT_AVAILABLE !== $manufacturer->getName()) {
            $this->children[] = new VigiloGroup("/Manufacturers/" . $manufacturer->getName());
        }

        // Association de l'objet à son technicien.
        $technician = new User();
        $technician->getFromDB($this->item->fields["users_id_tech"]);
        if (NOT_AVAILABLE !== $technician->getName()) {
            $this->children[] = new VigiloGroup("/Technicians/" . $technician->getName());
        }
    }

    protected function selectAddress()
    {
        if ($this->agent) {
            $addresses = $this->agent->getIPs();
            if (count($addresses)) {
                return current($addresses);
            }
        }

        if (count($this->addresses)) {
            return current($this->addresses);
        }

        return $this->item->getName();
    }

    protected function monitorNetworkInterfaces()
    {
        global $DB;

        $query = NetworkPort::getSQLRequestToSearchForItem(
            $this->item->getType(),
            $this->item->getID()
        );

        foreach ($DB->query($query) as $np) {
            $query2     = NetworkName::getSQLRequestToSearchForItem("NetworkPort", $np['id']);
            $port       = new NetworkPort();
            $ethport    = new NetworkPortEthernet();

            $port->getFromDB($np['id']);
            if ($port->getName() == 'lo') {
                continue;
            }

            $label = !empty($port->fields['comment']) ? $port->fields['comment'] : $port->getName();

            $this->children[] =
                        $test = new VigiloTest('Interface');
            $test['label']  = $label;
            $test['ifname'] = self::escapeRegex($port->getName());

            $ethport    = $ethport->find('networkports_id=' . $np['id']);
            foreach ($ethport as $rowEthPort) {
                if ($rowEthPort['speed']) {
                    // La bande passante de l'interface est exprimée
                    // en Mbit/s dans GLPI et on la veut en bit/s dans Vigilo.
                    $test['max'] = $rowEthPort['speed'] << 20;
                    break;
                }
            }

            // Récupère la liste de toutes les adresses IPv4 pour l'interface.
            // Elles serviront plus tard dans selectAddress() pour choisir
            // l'adresse IP la plus appropriée pour interroger ce réseau.
            foreach ($DB->query($query2) as $nn) {
                $query3 = IPAddress::getSQLRequestToSearchForItem("NetworkName", $nn['id']);
                foreach ($DB->query($query3) as $ip) {
                    $addr = new IPAddress();
                    if ($addr->getFromDB($ip['id']) && $addr->is_ipv4()) {
                        $textual = $addr->getTextual();
                        if (is_string($textual)) {
                            $this->addresses[] = $textual;
                        }
                    }
                }
            }
        }
    }

    public function __toString()
    {
        return self::sprintf(
            '<' . '?xml version="1.0"?' . '>' .
            '<host name="%s" address="%s" ventilation="%s">%s</host>',
            $this->item->getName(),
            $this->selectAddress(),
            "Servers",
            $this->children
        );
    }
}
