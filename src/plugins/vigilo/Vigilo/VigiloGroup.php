<?php

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
