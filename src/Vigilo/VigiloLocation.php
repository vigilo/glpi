<?php

class VigiloLocation extends VigiloXml
{
    protected $childrenLocation;
    protected $childrenEntity;
    protected $childrenManufacturer;

    public function __construct()
    {
        $this->childrenLocation     = array();
        $this->childrenEntity       = array();
        $this->childrenManufacturer = array();
        $this->selectLocations();
        $this->selectEntities();
        $this->selectManufacturers();
    }

    protected function selectManufacturers()
    {
        $manufacturers = new Manufacturer();
        $manufacturers = $manufacturers->find();
        foreach ($manufacturers as $manufacturer) {
            $this->childrenManufacturer[] = new VigiloGroups($manufacturer["name"]);
        }
    }

    protected function selectEntities()
    {
        $entities   = new Entity();
        $entities   = $entities->find("", "completename");
        $ancestors  = array();
        foreach ($entities as $entity) {
            $currentLevel = $entity["level"];
            if ($currentLevel == 1 && isset($ancestors[1])) {
                $this->childrenEntity[] = $ancestors[1];
            }
            $tempEntity = new VigiloGroups($entity["name"]);
            $ancestors[$currentLevel] = $tempEntity;
            if ($currentLevel != 1) {
                $ancestors[$currentLevel - 1]->addSubGroup($tempEntity);
            }
        }
        $this->childrenEntity[] = $ancestors[1];
    }

    protected function selectLocations()
    {
        $locations = new Location();
        $locations = $locations->find("", "completename");
        $ancestors = array();
        foreach ($locations as $location) {
            $currentLevel = $location["level"];
            if ($currentLevel == 1 && isset($ancestors[1])) {
                $this->childrenLocation[] = $ancestors[1];
            }
            $tempLocation = new VigiloGroups($location["name"]);
            $ancestors[$currentLevel] = $tempLocation;
            if ($currentLevel != 1) {
                $ancestors[$currentLevel - 1]->addSubGroup($tempLocation);
            }
        }
        $this->childrenLocation[] = $ancestors[1];
    }
  
    public function __toString()
    {
        $outXML = new DOMDocument();
        $outXML->preserveWhiteSpace = false;
        $outXML->formatOutput       = true;
        $outXML->loadXML(
            self::sprintf(
                '<groups>
     <group name="Locations"> %s </group>
     <group name="Entities"> %s </group>
     <group name="Manufacturers"> %s </group>
     </groups>',
                $this->childrenLocation,
                $this->childrenEntity,
                $this->childrenManufacturer
            )
        );
        return $outXML->saveXML();
    }
}
