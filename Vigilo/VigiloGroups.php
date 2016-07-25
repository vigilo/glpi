<?php

class VigiloGroups extends VigiloXml
{
    protected $name;
    protected $grps;

    public function __construct($name)
    {
        $this->name = $name;
        $this->grps=array();
    }
    
    public function getName()
    {
        return $this->name;
    }

    public function addSubGroup(VigiloGroups $subGroup)
    {
        $this->grps[$subGroup->getName()] = $subGroup;
    }

    public function __toString()
    {
        return self::sprintf(
            '<group name="%s">%s</group>',
            $this->name,
            $this->grps
        );
    }
}
