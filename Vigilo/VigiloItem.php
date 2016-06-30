<?php

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