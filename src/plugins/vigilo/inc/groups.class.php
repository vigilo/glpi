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
            'Technicians'   => new VigiloGroups('Technicians'),
        );

        $this->getManufacturers();
        $this->getEntities();
        $this->getLocations();
        $this->getTechnicians();
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

    protected function getTechnicians()
    {
        global $DB;

        // Ce serait plus propre d'utiliser la surcouche de GLPI
        // pour récupérer l'information, mais cela serait aussi
        // beaucoup plus coûteux (plus d'appels à la BDD, etc.).

        $query = <<<SQL
SELECT DISTINCT u.name
FROM glpi_users u
JOIN (
    SELECT users_id_tech
    FROM glpi_computers
    UNION ALL
    SELECT users_id_tech
    FROM glpi_networkequipments
    UNION ALL
    SELECT users_id_tech
    FROM glpi_printers
) as a1 on a1.users_id_tech = u.id;
SQL;
        foreach ($DB->request($query) as $row) {
            $this->groups['Technicians'][] = $row['name'];
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
