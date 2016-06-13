<?php

//require_once(__DIR__ . DIRECTORY_SEPARATOR . '');

abstract class VigiloXml
{
    abstract public function __toString();

    public static function sprintf($s)
    {
        $args = func_get_args();
        array_shift($args); // pop $s

        $new_args = array();
        foreach ($args as $arg) {
            if (is_string($arg)) {
                $new_args[] = htmlspecialchars($arg, ENT_XML1 | ENT_QUOTES, "utf-8");
            } else if (is_array($arg)) {
                $acc = '';
                foreach ($arg as $sub) {
                    if (is_object($sub))
                        $acc .= (string) $sub;
                }
                $new_args[] = $acc;
            } else {
                $new_args[] = (string) $arg;
            }
        }

        return vsprintf($s, $new_args);
    }
}

class VigiloHostTemplate extends VigiloXml
{
    protected $name;

    public function __construct($tpl)
    {
        $this->name = $tpl;
    }

    public function __toString()
    {
        return self::sprintf('<template>%s</template>', $this->name);
    }
}

class VigiloGroup extends VigiloXml
{
    protected $name;

    public function __construct($group)
    {
        $this->name = $group;
    }

    public function __toString()
    {
        return self::sprintf('<group>%s</group>', $this->name);
    }
}

class VigiloAttribute extends VigiloXml
{
    protected $name;
    protected $value;

    public function __construct($name, $value)
    {
        $this->name = $name;
        $this->value = $value;
    }

    public function __toString()
    {
        return self::sprintf(
            '<attribute name="%s">%s</attribute>',
            $this->name,
            $this->value
        );
    }
}

class VigiloItem extends VigiloXml
{
    protected $value;

    public function __construct($value)
    {
        $this->value = $value;
    }

    public function getValue()
    {
        return $this->value;
    }

    public function __toString()
    {
        return self::sprintf('<item>%s</item>', $this->value);
    }
}

class VigiloArg extends VigiloXml
{
    protected $name;
    protected $values;

    public function __construct($name, $values)
    {
        if (is_array($values)) {
            $new_values = array();
            foreach ($values as $value) {
                if (is_string($value))
                    $new_values[] = new VigiloItem($value);
                else if (!is_a($value, 'VigiloItem'))
                    throw new \RuntimeException();
                else
                    $new_values[] = $value;
            }
            $values = $new_values;
        } else if (!is_string($values) && !is_int($values) &&
                   !is_bool($values) && !is_float($values))
            throw new \RuntimeException();
        else {
            $values = (string) $values;
        }

        $this->name = $name;
        $this->values = $values;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getValue()
    {
        if (is_string($this->values))
            return $this->values;
        return array_map('getValue', $this->values);
    }

    public function __toString()
    {
        return self::sprintf(
            '<arg name="%s">%s</arg>',
            $this->name,
            $this->values
        );
    }
}

class VigiloTest extends VigiloXml
{
    protected $name;
    protected $args;

    public function __construct($name, array $args = array())
    {
        $new_args = array();
        foreach ($args as $arg) {
            if (!is_a($arg, 'VigiloArg'))
                    throw new \RuntimeException();
            $new_args[$arg->getName()] = $arg;
        }

        $this->name = $name;
        $this->args = $new_args;
    }

    public function __toString()
    {
        return self::sprintf(
            '<test name="%s">%s</test>',
            $this->name,
            $this->args
        );
    }
}

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
            $address = $this->$computer->getName();
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
        return self::sprintf(
            '<?xml version="1.0"?>' .
            '<host name="%s" address="%s" ventilation="%s">%s</host>',
            $this->computer->getName(),
            $this->selectAddress(),
            "Servers",
            $this->children
        );
    }
}

class VigiloHooks
{
    private $confdir;

    public function __construct($confdir="/etc/vigilo/vigiconf/conf.d")
    {
        $this->confdir = $confdir;
    }

    public function add($computer)
    {
        $host       = new VigiloHost($computer);
        $dirs       = array($this->confdir, "hosts", "managed");
        $confdir    = implode(DIRECTORY_SEPARATOR, $dirs);
        $file       = $confdir . DIRECTORY_SEPARATOR . $host->getName() . ".xml";

        mkdir($confdir, 0770, true);
        $acc = "";
        foreach ($dirs as $dir) {
            $acc .= DIRECTORY_SEPARATOR . $dir;
            chgrp($acc, "vigiconf");
        }

        $res = file_put_contents($file, $host, LOCK_EX);
        if ($res !== false) {
            chgrp($file, "vigiconf");
            chmod($file, 0660);
        }
    }

    public function delete($computer)
    {
        $this->_unmonitor($computer->fields["name"]);
    }

    public function update($computer)
    {
        if (isset($computer->oldvalues["name"])) {
            $this->_unmonitor($computer->oldvalues["name"]);
        }

        $this->add($computer);
    }

    public function _unmonitor($host)
    {
        $dirs = array($this->confdir, "hosts", "managed", $host . ".xml");
        unlink(implode(DIRECTORY_SEPARATOR, $dirs));
    }
}

