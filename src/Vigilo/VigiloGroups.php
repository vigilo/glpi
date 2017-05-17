<?php

class VigiloGroups extends VigiloXml
{
    protected $name;
    protected $groups;

    public function __construct($name)
    {
        $this->name     = $name;
        $this->groups   = array();
    }
    
    public function getName()
    {
        return $this->name;
    }

    public function addSubGroup(VigiloGroups $subGroup)
    {
        $this->groups[$subGroup->getName()] = $subGroup;
    }

    public function __toString()
    {
        return self::sprintf(
            '<group name="%s">%s</group>',
            $this->name,
            $this->groups
        );
    }
}
