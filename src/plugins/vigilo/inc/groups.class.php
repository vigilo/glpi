<?php

class PluginVigiloGroups
{
    protected $groups;

    public function __construct()
    {
        $this->groups = array(
            'Entities'      => new VigiloGroups('Entities'),
            'Manufacturers' => new VigiloGroups('Manufacturers'),
            'Locations'     => new VigiloGroups('Locations'),
        );

        $this->getManufacturers();
        $this->getEntities();
        $this->getLocations();
    }

    public function getName()
    {
        return "glpi";
    }

    protected function getManufacturers()
    {
        $manufacturers = new Manufacturer();
        $manufacturers = $manufacturers->find();
        foreach ($manufacturers as $manufacturer) {
            $this->groups['Manufacturers'][] = $manufacturer["name"];
        }
    }

    protected function getEntities()
    {
        $items   = new Entity();
        $items   = $items->find("", "completename");
        foreach ($items as $item) {
            $parts  = explode(' > ', $item['completename']);
            $name   = array_pop($parts);

            $pos =& $this->groups['Entities'];
            foreach ($parts as $part) {
                $pos =& $pos[$part];
            }
            $pos[] = $name;
        }
    }

    protected function getLocations()
    {
        $items   = new Location();
        $items   = $items->find("", "completename");
        foreach ($items as $item) {
            $parts  = explode(' > ', $item['completename']);
            $name   = array_pop($parts);

            $pos =& $this->groups['Locations'];
            foreach ($parts as $part) {
                $pos =& $pos[$part];
            }
            $pos[] = $name;
        }
    }

    public function __toString()
    {
        $out  = "<groups>\n";
        foreach ($this->groups as $group) {
            $out .= $group;
        }
        $out .= "</groups>\n";

        $outXML = new DOMDocument();
        $outXML->preserveWhiteSpace = false;
        $outXML->formatOutput       = true;
        $outXML->loadXML($out);
        return $outXML->saveXML();
    }
}
