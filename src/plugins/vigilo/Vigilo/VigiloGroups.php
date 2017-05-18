<?php

class VigiloGroups extends VigiloXml implements ArrayAccess
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

    public function offsetExists($offset)
    {
        $name = is_a($offset, 'VigiloGroups') ? $offset->getName() : $offset;
        return isset($this->groups[$name]);
    }

    public function offsetGet($offset)
    {
        $name = is_a($offset, 'VigiloGroups') ? $offset->getName() : $offset;
        return isset($this->groups[$name]) ? $this->groups[$name] : null;
    }

    public function offsetSet($offset, $value)
    {
        if (is_null($offset) && is_string($value)) {
            $value = new VigiloGroups($value);
        }

        if (!is_a($value, 'VigiloGroups')) {
            throw new \RuntimeException();
        }

        return $this->groups[$value->getName()] = $value;
    }

    public function offsetUnset($offset)
    {
        $name = is_a($offset, 'VigiloGroups') ? $offset->getName() : $offset;
        unset($this->groups[$name]);
    }

    public function __toString()
    {
        if (!count($this->groups)) {
            return self::sprintf(
                '<group name="%s"/>',
                $this->name
            );
        }

        return self::sprintf(
            '<group name="%s">%s</group>',
            $this->name,
            $this->groups
        );
    }
}
