<?php

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